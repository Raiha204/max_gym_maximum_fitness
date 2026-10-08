<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\WalkIn;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function reports(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:date,month,year'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
            'year' => ['nullable', 'regex:/^\d{4}$/', 'integer', 'between:1900,2100'],
        ]);

        $period = $validated['period'] ?? 'date';
        $displayTimezone = config('app.display_timezone');
        $today = Carbon::now($displayTimezone);
        $selectedDate = $validated['date'] ?? $today->format('Y-m-d');
        $selectedMonth = $validated['month'] ?? $today->format('Y-m');
        $selectedYear = $validated['year'] ?? $today->format('Y');

        if ($period === 'year') {
            $localRangeStart = Carbon::createFromFormat('!Y', $selectedYear, $displayTimezone)->startOfYear();
            $localRangeEnd = $localRangeStart->copy()->endOfYear();
            $periodLabel = $localRangeStart->format('Y');
            $exportPeriod = $periodLabel;
        } elseif ($period === 'month') {
            $localRangeStart = Carbon::createFromFormat('!Y-m', $selectedMonth, $displayTimezone)->startOfMonth();
            $localRangeEnd = $localRangeStart->copy()->endOfMonth();
            $periodLabel = $localRangeStart->format('F Y');
            $exportPeriod = $localRangeStart->format('Y-m');
        } else {
            $localRangeStart = Carbon::parse($selectedDate, $displayTimezone)->startOfDay();
            $localRangeEnd = $localRangeStart->copy()->endOfDay();
            $periodLabel = $localRangeStart->format('F j, Y');
            $exportPeriod = $localRangeStart->format('Y-m-d');
        }

        $rangeStart = $localRangeStart->copy()->setTimezone('UTC');
        $rangeEnd = $localRangeEnd->copy()->setTimezone('UTC');

        $datePayments = Payment::with('membership')
            ->whereBetween('paid_at', [$rangeStart, $rangeEnd])
            ->orderBy('paid_at')
            ->get();
        $dateWalkIns = WalkIn::whereBetween('paid_at', [$rangeStart, $rangeEnd])
            ->orderBy('paid_at')
            ->get();

        $memRevenue = $datePayments->sum('amount');
        $walkInRevenue = $dateWalkIns->sum('amount');
        $totalRevenue = $memRevenue + $walkInRevenue;
        $membershipCashRevenue = $datePayments->where('payment_method', 'Cash')->sum('amount');
        $membershipGcashRevenue = $datePayments->where('payment_method', 'GCash')->sum('amount');
        $walkInCashRevenue = $dateWalkIns->where('payment_method', 'Cash')->sum('amount');
        $walkInGcashRevenue = $dateWalkIns->where('payment_method', 'GCash')->sum('amount');
        $monthlyRevenue = collect(range(1, 12))->map(function ($month) use ($datePayments, $dateWalkIns, $displayTimezone, $localRangeStart) {
            $monthPayments = $datePayments->filter(function ($payment) use ($month, $displayTimezone, $localRangeStart) {
                $paidAt = $payment->paid_at?->copy()->setTimezone($displayTimezone);

                return $paidAt && $paidAt->year === $localRangeStart->year && $paidAt->month === $month;
            })->sum('amount');
            $monthWalkIns = $dateWalkIns->filter(function ($walkIn) use ($month, $displayTimezone, $localRangeStart) {
                $paidAt = $walkIn->paid_at?->copy()->setTimezone($displayTimezone);

                return $paidAt && $paidAt->year === $localRangeStart->year && $paidAt->month === $month;
            })->sum('amount');

            return [
                'month' => Carbon::create($localRangeStart->year, $month, 1, 0, 0, 0, $displayTimezone)->format('F'),
                'total' => $monthPayments + $monthWalkIns,
            ];
        });

        return view('reports.index', compact(
            'period',
            'selectedDate',
            'selectedMonth',
            'selectedYear',
            'periodLabel',
            'exportPeriod',
            'monthlyRevenue',
            'datePayments',
            'dateWalkIns',
            'memRevenue',
            'walkInRevenue',
            'totalRevenue',
            'membershipCashRevenue',
            'membershipGcashRevenue',
            'walkInCashRevenue',
            'walkInGcashRevenue'
        ));
    }
}
