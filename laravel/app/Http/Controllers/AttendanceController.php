<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Membership;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $memberships = Membership::orderBy('full_name')->get();
        $displayTimezone = config('app.display_timezone', 'Asia/Manila');
        $attendances = Attendance::with('membership')
            ->latest('checked_in_at')
            ->latest('id')
            ->take(250)
            ->get()
            ->unique(function (Attendance $attendance) use ($displayTimezone) {
                $date = $attendance->checked_in_at
                    ->copy()
                    ->setTimezone($displayTimezone)
                    ->toDateString();

                return ($attendance->membership_id ?? $attendance->member_id) . '|' . $date;
            })
            ->take(50)
            ->values();

        $preselectedMember = null;
        if ($request->filled('member_id')) {
            $preselectedMember = Membership::where('member_id', $request->query('member_id'))->first();
        }

        return view('attendance.index', compact('memberships', 'attendances', 'preselectedMember'));
    }

    /**
     * AJAX Verification Endpoint for QR Scanner & Manual Search
     */
    public function verify(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['found' => false, 'message' => 'Empty search query.'], 422);
        }

        // Extract 6-digit Member ID if scanned from an Offline Member QR Profile or plain ID QR
        $member = null;
        if (preg_match('/\b(\d{6})\b/', $q, $matches)) {
            $member = Membership::where('member_id', $matches[1])->first();
        }

        if (!$member) {
            $member = Membership::where('member_id', $q)
                ->orWhere('full_name', 'like', "%{$q}%")
                ->first();
        }

        if (!$member) {
            return response()->json([
                'found' => false,
                'message' => "No member found matching '{$q}'.",
            ], 404);
        }

        $displayTimezone = config('app.display_timezone', 'Asia/Manila');
        $localDayStart = Carbon::now($displayTimezone)->startOfDay();
        $dayStartUtc = $localDayStart->copy()->setTimezone('UTC');
        $nextDayStartUtc = $localDayStart->copy()->addDay()->setTimezone('UTC');
        $todayAttendance = Attendance::where(function ($query) use ($member) {
                $query->where('membership_id', $member->id)
                    ->orWhere('member_id', $member->member_id);
            })
            ->where('checked_in_at', '>=', $dayStartUtc)
            ->where('checked_in_at', '<', $nextDayStartUtc)
            ->latest('checked_in_at')
            ->first();

        return response()->json([
            'found' => true,
            'id' => $member->id,
            'member_id' => $member->member_id,
            'full_name' => $member->full_name,
            'photo_url' => $member->photo_url,
            'date_of_birth' => optional($member->date_of_birth)->format('d/m/Y') ?? '—',
            'membership_type' => $member->plan_type,
            'status' => $member->membership_status,
            'can_check_in' => in_array($member->membership_status, ['Active', 'Partial'], true),
            'available_credit' => $member->daily_credit_enabled ? $member->available_credit : null,
            'start_date' => optional($member->start_date)->format('F j, Y') ?? '—',
            'expiration_date' => optional($member->end_date)->format('F j, Y') ?? '—',
            'days_remaining' => $member->days_remaining,
            'renew_url' => route('memberships.show', $member),
            'checked_in_today' => $todayAttendance !== null,
            'checked_in_at' => $todayAttendance?->checked_in_at
                ?->copy()
                ->setTimezone($displayTimezone)
                ->format('g:i:s A'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => ['required', 'string'],
            'verification_method' => ['nullable', 'string', 'in:QR Code Scan,Manual Search'],
        ]);

        // Extract 6-digit ID if full QR payload was submitted
        $memberId = $validated['member_id'];
        if (preg_match('/\b(\d{6})\b/', $memberId, $matches)) {
            $memberId = $matches[1];
        }

        $membershipId = Membership::where('member_id', $memberId)->value('id');
        if (!$membershipId) {
            return back()->withErrors(['member_id' => "Member ID {$memberId} not found."]);
        }

        $result = DB::transaction(function () use ($membershipId, $validated) {
            $member = Membership::whereKey($membershipId)->lockForUpdate()->firstOrFail();

            if (!in_array($member->membership_status, ['Active', 'Partial'], true)) {
                return ['status' => 'expired', 'member' => $member];
            }

            $displayTimezone = config('app.display_timezone', 'Asia/Manila');
            $localDayStart = Carbon::now($displayTimezone)->startOfDay();
            $dayStartUtc = $localDayStart->copy()->setTimezone('UTC');
            $nextDayStartUtc = $localDayStart->copy()->addDay()->setTimezone('UTC');

            $alreadyCheckedIn = Attendance::where('membership_id', $member->id)
                ->where('checked_in_at', '>=', $dayStartUtc)
                ->where('checked_in_at', '<', $nextDayStartUtc)
                ->exists();

            if ($alreadyCheckedIn) {
                return ['status' => 'already_checked_in', 'member' => $member];
            }

            Attendance::create([
                'membership_id' => $member->id,
                'member_id' => $member->member_id,
                'checked_in_at' => Carbon::now('UTC'),
                'verification_method' => $validated['verification_method'] ?? 'QR Code Scan',
            ]);

            return ['status' => 'recorded', 'member' => $member];
        });

        $member = $result['member'];
        if ($result['status'] === 'expired') {
            return back()
                ->with('verified_member_id', $member->member_id)
                ->withErrors([
                    'expired' => '✗ Membership Expired — Please proceed to Membership Renewal.',
                ]);
        }

        if ($result['status'] === 'already_checked_in') {
            return redirect()
                ->route('attendance.index', ['member_id' => $member->member_id])
                ->with('popup', [
                    'title' => 'Already Checked In',
                    'message' => "{$member->full_name} (ID: {$member->member_id}) has already checked in today.",
                    'sub' => 'No additional attendance was recorded.',
                ]);
        }

        return redirect()
            ->route('attendance.index', ['member_id' => $member->member_id])
            ->with('attendance_verified', "✓ Membership Verified — ✓ Attendance Recorded Successfully for {$member->full_name} (ID: {$member->member_id}).");
    }
}
