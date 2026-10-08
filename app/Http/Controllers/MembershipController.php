<?php

namespace App\Http\Controllers;

use App\Models\Membership;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
            'partial' => $allMemberships->where('membership_status', 'Partial')->count(),
            'expiring' => $allMemberships->where('display_status', 'expiring')->count(),
            'expired' => $allMemberships->where('membership_status', 'Expired')->count(),
        ];

        $memberships = $allMemberships->filter(function (Membership $m) use ($statusFilter) {
            if ($statusFilter === 'active') return $m->membership_status === 'Active';
            if ($statusFilter === 'partial') return $m->membership_status === 'Partial';
            if ($statusFilter === 'expiring') return $m->display_status === 'expiring';
            if ($statusFilter === 'expired') return $m->membership_status === 'Expired';
            return true;
        });

        // Nearest due date / expiration first
        $memberships = $memberships
            ->sortBy(fn (Membership $m) => optional($m->end_date)->timestamp ?? 0)
            ->values();

        return view('memberships.index', compact('memberships', 'search', 'statusFilter', 'counts'));
    }

    public function create()
    {
        return view('memberships.create');
    }

    public function store(Request $request)
    {
        $this->mergeFullName($request);

        $validated = $request->validate([
            'surname' => ['required', 'string', 'max:120'],
            'first_name' => ['required', 'string', 'max:120'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'digits:11'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'date_of_birth' => ['required', 'date'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'plan_type' => ['required', 'in:Student Membership,Regular Membership'],
            'duration_months' => ['required', 'integer', 'in:1,2,3,6,12'],
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:Cash,GCash'],
        ]);

        $monthlyRate = Membership::PLAN_RATES[$validated['plan_type']] ?? 750;
        $duration = (int) $validated['duration_months'];
        $totalAmount = $monthlyRate * $duration;
        $amountPaid = round((float) $validated['amount_paid'], 2);
        if ($amountPaid > $totalAmount) {
            throw ValidationException::withMessages([
                'amount_paid' => 'The payment cannot exceed the plan total.',
            ]);
        }

        $startDate = Carbon::today();
        $endDate = $startDate->copy()->addMonths($duration);
        $paidAt = Carbon::now('UTC');
        $isPartialPayment = $amountPaid < $totalAmount;

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('members', 'public');
        }

        $membership = DB::transaction(function () use ($validated, $monthlyRate, $duration, $totalAmount, $amountPaid, $startDate, $endDate, $photoPath, $paidAt, $isPartialPayment) {
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
                'remaining_amount' => $totalAmount - $amountPaid,
                'daily_credit_enabled' => $isPartialPayment,
                'daily_credit_started_at' => $isPartialPayment ? $paidAt : null,
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
                'amount' => $amountPaid,
                'payment_method' => $validated['payment_method'],
                'paid_at' => $paidAt,
            ]);

            return $member;
        });

        return redirect()
            ->route('memberships.show', [$membership, 'registered' => 1])
            ->with('popup', ['title' => 'Member Added', 'message' => 'Added Successfully!', 'sub' => "{$membership->full_name} (ID: {$membership->member_id}) is now registered."]);
    }

    public function show(Membership $membership)
    {
        $membership->loadCount('attendances')
            ->load(['payments' => fn ($q) => $q->latest('paid_at')]);
        return view('memberships.show', compact('membership'));
    }

    public function photo(Membership $membership)
    {
        abort_unless(
            $membership->photo_path && Storage::disk('public')->exists($membership->photo_path),
            404
        );

        return Storage::disk('public')->response($membership->photo_path);
    }

    public function edit(Membership $membership)
    {
        return view('memberships.edit', compact('membership'));
    }

    public function update(Request $request, Membership $membership)
    {
        $this->mergeFullName($request);

        $validated = $request->validate([
            'surname' => ['required', 'string', 'max:120'],
            'first_name' => ['required', 'string', 'max:120'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'digits:11'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'date_of_birth' => ['required', 'date'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        unset($validated['surname'], $validated['first_name']);

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
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:Cash,GCash'],
        ]);

        $monthlyRate = Membership::PLAN_RATES[$validated['plan_type']] ?? 750;
        $duration = (int) $validated['duration_months'];
        $totalAmount = $monthlyRate * $duration;
        $amountPaid = round((float) $validated['amount_paid'], 2);
        if ($amountPaid > $totalAmount) {
            throw ValidationException::withMessages([
                'amount_paid' => 'The payment cannot exceed the plan total.',
            ]);
        }
        $paidAt = Carbon::now('UTC');
        $isPartialPayment = $amountPaid < $totalAmount;

        $baseDate = ($membership->end_date && $membership->end_date->isFuture())
            ? $membership->end_date->copy()
            : Carbon::today();

        $newEndDate = $baseDate->copy()->addMonths($duration);

        DB::transaction(function () use ($membership, $validated, $monthlyRate, $duration, $totalAmount, $amountPaid, $newEndDate, $paidAt, $isPartialPayment) {
            $enableDailyCredit = $membership->daily_credit_enabled || $isPartialPayment;
            $membership->update([
                'plan_type' => $validated['plan_type'],
                'duration_months' => $duration,
                'monthly_rate' => $monthlyRate,
                'total_amount' => $totalAmount,
                'remaining_amount' => $totalAmount - $amountPaid,
                'daily_credit_enabled' => $enableDailyCredit,
                'daily_credit_started_at' => $membership->daily_credit_started_at ?? ($isPartialPayment ? $paidAt : null),
                'payment_method' => $validated['payment_method'],
                'start_date' => $membership->end_date && $membership->end_date->isFuture() ? $membership->start_date : Carbon::today(),
                'end_date' => $newEndDate,
                'status' => 'Active',
            ]);

            Payment::create([
                'membership_id' => $membership->id,
                'payer_name' => $membership->full_name,
                'category' => 'Membership Renewal',
                'plan_label' => "{$validated['plan_type']} ({$duration} " . ($duration === 1 ? 'Month' : 'Months') . ")",
                'amount' => $amountPaid,
                'payment_method' => $validated['payment_method'],
                'paid_at' => $paidAt,
            ]);
        });

        return redirect()->route('memberships.show', $membership)->with('success', 'Membership renewed successfully.');
    }

    public function recordPayment(Request $request, Membership $membership)
    {
        $validated = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:Cash,GCash'],
        ]);

        DB::transaction(function () use ($membership, $validated) {
            $member = Membership::whereKey($membership->id)->lockForUpdate()->firstOrFail();
            if (!$member->daily_credit_enabled) {
                throw ValidationException::withMessages([
                    'amount_paid' => 'Additional payments are only available for memberships using daily credit.',
                ]);
            }
            $amountPaid = round((float) $validated['amount_paid'], 2);
            $member->update([
                'remaining_amount' => max(0, $member->remaining_amount - $amountPaid),
            ]);

            Payment::create([
                'membership_id' => $member->id,
                'payer_name' => $member->full_name,
                'category' => 'Membership Balance Payment',
                'plan_label' => "{$member->plan_type} ({$member->duration_months} " . ($member->duration_months === 1 ? 'Month' : 'Months') . ")",
                'amount' => $amountPaid,
                'payment_method' => $validated['payment_method'],
                'paid_at' => Carbon::now('UTC'),
            ]);
        });

        return redirect()->route('memberships.show', $membership)->with('success', 'Membership payment recorded.');
    }

    public function destroy(Membership $membership)
    {
        $membership->delete();
        return redirect()->route('memberships.index')->with('success', 'Membership deleted.');
    }

    /**
     * The form collects Surname and First Name separately; they are stored as
     * "Surname, First Name" in the existing full_name column.
     */
    private function mergeFullName(Request $request): void
    {
        $surname = trim((string) $request->input('surname', ''));
        $first = trim((string) $request->input('first_name', ''));

        if ($surname !== '' && $first !== '') {
            $request->merge(['full_name' => "{$surname}, {$first}"]);
        }
    }
}
