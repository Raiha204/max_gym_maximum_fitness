<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Equipment;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\WalkIn;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:date,month'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $selectedPeriod = $validated['period'] ?? 'date';
        $selectedDate = $validated['date'] ?? today()->toDateString();
        $selectedMonth = $validated['month'] ?? today()->format('Y-m');

        $memberships = Membership::latest()->get();
        $activeCount = $memberships->where('membership_status', 'Active')->count();
        $partialCount = $memberships->where('membership_status', 'Partial')->count();
        $expiringCount = $memberships->where('display_status', 'expiring')->count();
        $expiredCount = $memberships->where('membership_status', 'Expired')->count();

        $dateAttendances = Attendance::with('membership')
            ->whereDate('checked_in_at', $selectedDate)
            ->latest('checked_in_at')
            ->get();

        $membershipRevenue = Payment::sum('amount');
        $walkInRevenue = WalkIn::sum('amount');

        $selectedMonthStart = Carbon::createFromFormat('!Y-m', $selectedMonth)->startOfMonth();
        $selectedMonthEnd = $selectedMonthStart->copy()->endOfMonth();
        $selectedMonthMembershipMethods = Payment::whereBetween('paid_at', [$selectedMonthStart, $selectedMonthEnd])
            ->selectRaw('payment_method, SUM(amount) as revenue')
            ->groupBy('payment_method')
            ->pluck('revenue', 'payment_method');
        $selectedMonthWalkInMethods = WalkIn::whereBetween('paid_at', [$selectedMonthStart, $selectedMonthEnd])
            ->selectRaw('payment_method, SUM(amount) as revenue')
            ->groupBy('payment_method')
            ->pluck('revenue', 'payment_method');
        $selectedMonthMembershipRevenue = (float) $selectedMonthMembershipMethods->sum();
        $selectedMonthWalkInRevenue = (float) $selectedMonthWalkInMethods->sum();
        $selectedMonthCashRevenue = (float) $selectedMonthMembershipMethods->get('Cash', 0)
            + (float) $selectedMonthWalkInMethods->get('Cash', 0);
        $selectedMonthGcashRevenue = (float) $selectedMonthMembershipMethods->get('GCash', 0)
            + (float) $selectedMonthWalkInMethods->get('GCash', 0);

        $selectedDateStart = Carbon::parse($selectedDate)->startOfDay();
        $selectedDateEnd = $selectedDateStart->copy()->endOfDay();
        $datePaymentMethods = Payment::whereBetween('paid_at', [$selectedDateStart, $selectedDateEnd])
            ->selectRaw('payment_method, SUM(amount) as revenue')
            ->groupBy('payment_method')
            ->pluck('revenue', 'payment_method');
        $dateWalkInMethods = WalkIn::whereBetween('paid_at', [$selectedDateStart, $selectedDateEnd])
            ->selectRaw('payment_method, SUM(amount) as revenue')
            ->groupBy('payment_method')
            ->pluck('revenue', 'payment_method');
        $dateMembershipRevenue = (float) $datePaymentMethods->sum();
        $dateWalkInRevenue = (float) $dateWalkInMethods->sum();
        $dateCashRevenue = (float) $datePaymentMethods->get('Cash', 0) + (float) $dateWalkInMethods->get('Cash', 0);
        $dateGcashRevenue = (float) $datePaymentMethods->get('GCash', 0) + (float) $dateWalkInMethods->get('GCash', 0);

        $summaryMembershipRevenue = $selectedPeriod === 'month' ? $selectedMonthMembershipRevenue : $dateMembershipRevenue;
        $summaryWalkInRevenue = $selectedPeriod === 'month' ? $selectedMonthWalkInRevenue : $dateWalkInRevenue;
        $summaryCashRevenue = $selectedPeriod === 'month' ? $selectedMonthCashRevenue : $dateCashRevenue;
        $summaryGcashRevenue = $selectedPeriod === 'month' ? $selectedMonthGcashRevenue : $dateGcashRevenue;
        $summaryTotalRevenue = $summaryMembershipRevenue + $summaryWalkInRevenue;

        $equipmentCount = Equipment::count();
        $operationalEquipment = Equipment::where('status', 'Operational')->count();
        $walkInCount = WalkIn::count();

        return view('dashboard', compact(
            'memberships',
            'activeCount',
            'partialCount',
            'expiringCount',
            'expiredCount',
            'dateAttendances',
            'membershipRevenue',
            'walkInRevenue',
            'selectedPeriod',
            'selectedMonth',
            'selectedMonthMembershipRevenue',
            'selectedMonthWalkInRevenue',
            'selectedMonthCashRevenue',
            'selectedMonthGcashRevenue',
            'summaryMembershipRevenue',
            'summaryWalkInRevenue',
            'summaryCashRevenue',
            'summaryGcashRevenue',
            'summaryTotalRevenue',
            'selectedDate',
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
