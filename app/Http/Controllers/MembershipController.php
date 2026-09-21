<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\Payment;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function index(Request $request)
    {
        $memberships = Membership::query()
            ->when($request->search, function ($q, $search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Lazily catch anyone who has passed their payment due date without
        // paying in full, same pattern as the pending → expired status check.
        foreach ($memberships as $membership) {
            $membership->applyLatePenaltyIfNeeded();
            $membership->refreshStatus();
        }

        return view('memberships.index', compact('memberships'));
    }

    public function create()
    {
        return view('memberships.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'member_type' => ['required', 'in:regular,student'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        // Price always comes from the fixed price list, never from user input,
        // so the cashier never has to type (or mistype) an amount.
        $data['amount_due'] = Membership::PRICES[$data['member_type']];
        $data['plan_name'] = ucfirst($data['member_type']).' Monthly';
        $data['amount_paid'] = min($data['amount_paid'] ?? 0, $data['amount_due']);
        $data['payment_due_date'] = now()->addDay()->toDateString();

        $membership = Membership::create($data);
        $membership->refreshStatus();

        if ($membership->amount_paid > 0) {
            Payment::create([
                'membership_id' => $membership->id,
                'payment_date' => now()->toDateString(),
                'amount' => $membership->amount_paid,
                'payment_method' => 'cash',
                'status' => 'completed',
            ]);
        }

        return redirect()->route('memberships.index')->with('success', 'Member registered successfully.');
    }

    public function edit(Membership $membership)
    {
        return view('memberships.edit', compact('membership'));
    }

    public function update(Request $request, Membership $membership)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'member_type' => ['required', 'in:regular,student'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        // If the type changed, keep the price in sync with the price list.
        $data['amount_due'] = Membership::PRICES[$data['member_type']];
        $data['plan_name'] = ucfirst($data['member_type']).' Monthly';

        $membership->update($data);
        $membership->refreshStatus();

        return redirect()->route('memberships.index')->with('success', 'Membership updated.');
    }

    /**
     * Record an additional (partial) payment toward this membership's balance.
     */
    public function addPayment(Request $request, Membership $membership)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$membership->balance],
            'payment_method' => ['required', 'in:cash,gcash,other'],
        ]);

        Payment::create([
            'membership_id' => $membership->id,
            'payment_date' => now()->toDateString(),
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'status' => 'completed',
        ]);

        $membership->increment('amount_paid', $data['amount']);

        // Once fully paid for the first time, start the membership term today.
        if ($membership->fresh()->is_fully_paid && ! $membership->start_date) {
            $membership->start_date = now()->toDateString();
            $membership->end_date = now()->addMonth()->toDateString();
            $membership->save();
        }

        $membership->refresh()->refreshStatus();

        return redirect()->route('memberships.index')->with('success', 'Payment recorded.');
    }

    public function destroy(Membership $membership)
    {
        $membership->delete();

        return redirect()->route('memberships.index')->with('success', 'Membership record deleted.');
    }
}
