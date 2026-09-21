<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Equipment;
use App\Models\Membership;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        // Catch anyone past their payment due date before computing stats.
        Membership::where('status', 'pending')->get()->each(function ($membership) {
            $membership->applyLatePenaltyIfNeeded();
            $membership->refreshStatus();
        });

        $stats = [
            'total_members' => Membership::count(),
            'active_memberships' => Membership::where('status', 'active')->count(),
            'pending_memberships' => Membership::where('status', 'pending')->count(),
            'expired_memberships' => Membership::where('status', 'expired')->count(),
            'today_attendance' => Attendance::whereDate('attendance_date', today())->count(),
            'today_sales' => Payment::whereDate('payment_date', today())->sum('amount'),
            'equipment_needing_repair' => Equipment::where('status', 'needs_repair')->count(),
        ];

        $recentPayments = Payment::with('membership')->latest('payment_date')->take(5)->get();
        $expiringSoon = Membership::where('status', 'active')
            ->whereBetween('end_date', [today(), today()->addDays(7)])
            ->get();

        return view('dashboard', compact('stats', 'recentPayments', 'expiringSoon'));
    }
}
