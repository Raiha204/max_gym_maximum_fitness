<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MembershipController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $statusFilter = (string) $request->query('status', 'all');

        $query = Membership::query()->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('member_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $allMemberships = $query->get();

        $counts = [
            'all' => $allMemberships->count(),
            'active' => $allMemberships->where('membership_status', 'Active')->count(),
            'expiring' => $allMemberships->where('display_status', 'expiring')->count(),
            'expired' => $allMemberships->where('membership_status', 'Expired')->count(),
        ];

        $memberships = $allMemberships->filter(function (Membership $m) use ($statusFilter) {
            if ($statusFilter === 'active') return $m->membership_status === 'Active';
            if ($statusFilter === 'expiring') return $m->display_status === 'expiring';
            if ($statusFilter === 'expired') return $m->membership_status === 'Expired';
            return true;
        });

        return view('memberships.index', compact('memberships', 'search', 'statusFilter', 'counts'));
    }

    public function create()
    {
        return view('memberships.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'date_of_birth' => ['required', 'date'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'plan_type' => ['required', 'in:Student Membership,Regular Membership'],
            'duration_months' => ['required', 'integer', 'in:1,2,3,6,12'],
            'payment_method' => ['required', 'in:Cash,GCash'],
        ]);

        $monthlyRate = Membership::PLAN_RATES[$validated['plan_type']] ?? 750;
        $duration = (int) $validated['duration_months'];
        $totalAmount = $monthlyRate * $duration;

        $startDate = Carbon::today();
        $endDate = $startDate->copy()->addMonths($duration);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('members', 'public');
        }

        $membership = DB::transaction(function () use ($validated, $monthlyRate, $duration, $totalAmount, $startDate, $endDate, $photoPath) {
            $member = Membership::create([
                'member_id' => Membership::generateMemberId(),
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'],
                'photo_path' => $photoPath,
                'plan_type' => $validated['plan_type'],
                'duration_months' => $duration,
                'monthly_rate' => $monthlyRate,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'Active',
            ]);

            Payment::create([
                'membership_id' => $member->id,
                'payer_name' => $member->full_name,
                'category' => 'Membership Registration',
                'plan_label' => "{$member->plan_type} ({$duration} " . ($duration === 1 ? 'Month' : 'Months') . ")",
                'amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'paid_at' => now(),
            ]);

            return $member;
        });

        return redirect()
            ->route('memberships.show', [$membership, 'registered' => 1])
            ->with('success', "Member {$membership->full_name} (ID: {$membership->member_id}) registered successfully.");
    }

    public function show(Membership $membership)
    {
        $membership->load(['attendances' => fn ($q) => $q->latest('checked_in_at'), 'payments' => fn ($q) => $q->latest('paid_at')]);
        return view('memberships.show', compact('membership'));
    }

    public function edit(Membership $membership)
    {
        return view('memberships.edit', compact('membership'));
    }

    public function update(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'date_of_birth' => ['required', 'date'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('members', 'public');
        }

        $membership->update($validated);

        return redirect()->route('memberships.show', $membership)->with('success', 'Member profile updated.');
    }

    public function renew(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'plan_type' => ['required', 'in:Student Membership,Regular Membership'],
            'duration_months' => ['required', 'integer', 'in:1,2,3,6,12'],
            'payment_method' => ['required', 'in:Cash,GCash'],
        ]);

        $monthlyRate = Membership::PLAN_RATES[$validated['plan_type']] ?? 750;
        $duration = (int) $validated['duration_months'];
        $totalAmount = $monthlyRate * $duration;

        $baseDate = ($membership->end_date && $membership->end_date->isFuture())
            ? $membership->end_date->copy()
            : Carbon::today();

        $newEndDate = $baseDate->copy()->addMonths($duration);

        DB::transaction(function () use ($membership, $validated, $monthlyRate, $duration, $totalAmount, $newEndDate) {
            $membership->update([
                'plan_type' => $validated['plan_type'],
                'duration_months' => $duration,
                'monthly_rate' => $monthlyRate,
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'start_date' => $membership->membership_status === 'Active' ? $membership->start_date : Carbon::today(),
                'end_date' => $newEndDate,
                'status' => 'Active',
            ]);

            Payment::create([
                'membership_id' => $membership->id,
                'payer_name' => $membership->full_name,
                'category' => 'Membership Renewal',
                'plan_label' => "{$validated['plan_type']} ({$duration} " . ($duration === 1 ? 'Month' : 'Months') . ")",
                'amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
                'paid_at' => now(),
            ]);
        });

        return redirect()->route('memberships.show', $membership)->with('success', 'Membership renewed successfully.');
    }

    public function destroy(Membership $membership)
    {
        $membership->delete();
        return redirect()->route('memberships.index')->with('success', 'Membership deleted.');
    }
}
