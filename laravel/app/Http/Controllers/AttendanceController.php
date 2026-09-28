<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Membership;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $memberships = Membership::orderBy('full_name')->get();
        $attendances = Attendance::with('membership')
            ->latest('checked_in_at')
            ->take(50)
            ->get();

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

        return response()->json([
            'found' => true,
            'id' => $member->id,
            'member_id' => $member->member_id,
            'full_name' => $member->full_name,
            'photo_url' => $member->photo_url,
            'date_of_birth' => optional($member->date_of_birth)->format('F j, Y') ?? '—',
            'membership_type' => $member->plan_type,
            'status' => $member->membership_status,
            'start_date' => optional($member->start_date)->format('F j, Y') ?? '—',
            'expiration_date' => optional($member->end_date)->format('F j, Y') ?? '—',
            'days_remaining' => $member->days_remaining,
            'renew_url' => route('memberships.show', $member),
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

        $member = Membership::where('member_id', $memberId)->first();

        if (!$member) {
            return back()->withErrors(['member_id' => "Member ID {$memberId} not found."]);
        }

        // Strict Attendance Validation: Block check-in if Expired
        if ($member->membership_status !== 'Active') {
            return back()
                ->with('verified_member_id', $member->member_id)
                ->withErrors([
                    'expired' => '✗ Membership Expired — Please proceed to Membership Renewal.',
                ]);
        }

        Attendance::create([
            'membership_id' => $member->id,
            'member_id' => $member->member_id,
            'checked_in_at' => now(),
            'verification_method' => $validated['verification_method'] ?? 'QR Code Scan',
        ]);

        return redirect()
            ->route('attendance.index', ['member_id' => $member->member_id])
            ->with('attendance_verified', "✓ Membership Verified — ✓ Attendance Recorded Successfully for {$member->full_name} (ID: {$member->member_id}).");
    }
}
