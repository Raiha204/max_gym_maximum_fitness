<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Equipment;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\WalkIn;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ])['date'] ?? today()->toDateString();

        $memberships = Membership::latest()->get();
        $activeCount = $memberships->where('membership_status', 'Active')->count();
        $expiringCount = $memberships->where('display_status', 'expiring')->count();
        $expiredCount = $memberships->where('membership_status', 'Expired')->count();

        $todayAttendances = Attendance::with('membership')
            ->whereDate('checked_in_at', today())
            ->latest('checked_in_at')
            ->get();

        $membershipRevenue = Payment::sum('amount');
        $walkInRevenue = WalkIn::sum('amount');
        $totalRevenue = $membershipRevenue + $walkInRevenue;

        $todayRevenue = Payment::whereDate('paid_at', today())->sum('amount')
            + WalkIn::whereDate('paid_at', today())->sum('amount');

        $weekRevenue = Payment::where('paid_at', '>=', now()->subDays(7)->startOfDay())->sum('amount')
            + WalkIn::where('paid_at', '>=', now()->subDays(7)->startOfDay())->sum('amount');

        $monthRevenue = Payment::whereYear('paid_at', now()->year)->whereMonth('paid_at', now()->month)->sum('amount')
            + WalkIn::whereYear('paid_at', now()->year)->whereMonth('paid_at', now()->month)->sum('amount');

        $datePayments = Payment::whereDate('paid_at', $selectedDate)->get();
        $dateWalkIns = WalkIn::whereDate('paid_at', $selectedDate)->get();
        $dateMembershipRevenue = $datePayments->sum('amount');
        $dateWalkInRevenue = $dateWalkIns->sum('amount');
        $dateCashRevenue = $datePayments->where('payment_method', 'Cash')->sum('amount')
            + $dateWalkIns->where('payment_method', 'Cash')->sum('amount');
        $dateGcashRevenue = $datePayments->where('payment_method', 'GCash')->sum('amount')
            + $dateWalkIns->where('payment_method', 'GCash')->sum('amount');

        $equipmentCount = Equipment::count();
        $operationalEquipment = Equipment::where('status', 'Operational')->count();
        $walkInCount = WalkIn::count();

        return view('dashboard', compact(
            'memberships',
            'activeCount',
            'expiringCount',
            'expiredCount',
            'todayAttendances',
            'totalRevenue',
            'membershipRevenue',
            'walkInRevenue',
            'todayRevenue',
            'weekRevenue',
            'monthRevenue',
            'selectedDate',
            'datePayments',
            'dateWalkIns',
            'dateMembershipRevenue',
            'dateWalkInRevenue',
            'dateCashRevenue',
            'dateGcashRevenue',
            'equipmentCount',
            'operationalEquipment',
            'walkInCount'
        ));
    }
}
