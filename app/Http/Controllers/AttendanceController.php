<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Membership;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $attendances = Attendance::with('membership.member')
            ->when($request->date, fn ($q, $date) => $q->whereDate('attendance_date', $date))
            ->latest('attendance_date')
            ->latest('check_in')
            ->paginate(15)
            ->withQueryString();

        $memberships = Membership::with('member')->get()
            ->sortBy(fn (Membership $membership) => $membership->member?->first_name)
            ->values();

        return view('attendance.index', compact('attendances', 'memberships'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'membership_id' => ['required', 'exists:memberships,id'],
        ]);

        Attendance::create([
            'membership_id' => $data['membership_id'],
            'attendance_date' => now()->toDateString(),
            'check_in' => now()->toTimeString(),
        ]);

        return back()->with('success', 'Check-in recorded.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return back()->with('success', 'Attendance record removed.');
    }
}
