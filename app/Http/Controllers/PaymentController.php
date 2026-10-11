<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\WalkIn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'method' => ['nullable', 'in:All,Cash,GCash'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $method = $validated['method'] ?? 'All';
        $selectedDate = $validated['date']
            ?? Carbon::now(config('app.display_timezone', 'Asia/Manila'))->format('Y-m-d');

        $query = Payment::with('membership')->latest('paid_at');
        if (in_array($method, ['Cash', 'GCash'], true)) {
            $query->where('payment_method', $method);
        }

        $payments = $query->get();
        $allPayments = Payment::latest('paid_at')->get();
        $allWalkIns = WalkIn::latest('paid_at')->get();

        return view('payments.index', compact(
            'payments',
            'allPayments',
            'allWalkIns',
            'method',
            'selectedDate'
        ));
    }

    public function searchRevenue(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'max:100'],
            'period' => ['nullable', 'in:date,month,year'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
            'year' => ['nullable', 'regex:/^\d{4}$/', 'integer', 'between:1900,2100'],
        ]);
        $displayTimezone = config('app.display_timezone', 'Asia/Manila');
        $period = $validated['period'] ?? 'date';
        $now = Carbon::now($displayTimezone);

        if ($period === 'year') {
            $start = Carbon::createFromFormat('!Y', $validated['year'] ?? $now->format('Y'), $displayTimezone)->startOfYear();
            $end = $start->copy()->endOfYear();
        } elseif ($period === 'month') {
            $start = Carbon::createFromFormat('!Y-m', $validated['month'] ?? $now->format('Y-m'), $displayTimezone)->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } else {
            $start = Carbon::parse($validated['date'] ?? $now->format('Y-m-d'), $displayTimezone)->startOfDay();
            $end = $start->copy()->endOfDay();
        }

        $rangeStart = $start->copy()->setTimezone('UTC');
        $rangeEnd = $end->copy()->setTimezone('UTC');
        $payments = Payment::with('membership')
            ->whereBetween('paid_at', [$rangeStart, $rangeEnd])
            ->get();
        $walkIns = WalkIn::whereBetween('paid_at', [$rangeStart, $rangeEnd])->get();
        $rows = $payments->map(fn (Payment $payment) => [
            'searchable' => implode(' ', array_filter([
                $payment->membership?->full_name,
                $payment->payer_name,
                $payment->membership?->member_id,
            ])),
            'search_value' => $payment->membership?->member_id ?: $payment->payer_name,
            'identifier' => $payment->membership?->member_id ?? '—',
            'name' => $payment->membership?->full_name ?: ($payment->payer_name ?: '—'),
            'source' => 'Membership',
            'details' => $payment->plan_label ?: 'Membership payment',
        ])->concat($walkIns->map(fn (WalkIn $walkIn) => [
            'searchable' => implode(' ', array_filter([
                $walkIn->first_name,
                $walkIn->last_name,
                $walkIn->receipt_no,
                $walkIn->id,
            ])),
            'search_value' => $walkIn->receipt_no,
            'identifier' => $walkIn->receipt_no,
            'name' => trim(($walkIn->first_name ?? '') . ' ' . ($walkIn->last_name ?? '')) ?: 'Walk-In',
            'source' => 'Walk-In',
            'details' => $walkIn->session_type,
        ]));

        $normalize = static fn (string $value): string => trim((string) preg_replace(
            '/[^\pL\pN]+/u',
            ' ',
            mb_strtolower($value)
        ));
        $terms = preg_split('/\s+/', $normalize($validated['q']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $matches = $rows->filter(function (array $row) use ($normalize, $terms) {
            $searchable = $normalize($row['searchable']);

            foreach ($terms as $term) {
                if (!str_contains($searchable, $term)) {
                    return false;
                }
            }

            return $terms !== [];
        })->take(10)->values();

        return response()->json(['matches' => $matches]);
    }

    public function reports(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:date,month,year'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
            'year' => ['nullable', 'regex:/^\d{4}$/', 'integer', 'between:1900,2100'],
            'search' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'in:All,Cash,GCash'],
            'source' => ['nullable', 'in:All,Membership,Walk-In'],
            'sort' => ['nullable', 'in:date_desc,date_asc,amount_desc,amount_asc'],
            'rows' => ['nullable', 'integer', 'in:10,30,50,100'],
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
        $ledgerSearch = trim((string) ($validated['search'] ?? ''));
        $ledgerMethod = $validated['payment_method'] ?? 'All';
        $ledgerSource = $validated['source'] ?? 'All';
        $ledgerSort = $validated['sort'] ?? 'date_desc';
        $ledgerRowsPerPage = (int) ($validated['rows'] ?? 10);

        $ledgerRows = $datePayments->map(fn (Payment $payment) => (object) [
            'key' => 'payment-' . $payment->id,
            'identifier' => $payment->membership?->member_id ?? '—',
            'paid_at' => $payment->paid_at,
            'source' => 'Membership Payment',
            'name' => $payment->membership?->full_name ?: ($payment->payer_name ?: '—'),
            'searchable' => implode(' ', array_filter([
                $payment->membership?->full_name,
                $payment->payer_name,
                $payment->membership?->member_id,
            ])),
            'subscription' => $payment->plan_label ?: '—',
            'method' => $payment->payment_method,
            'amount' => $payment->amount,
        ])->concat($dateWalkIns->map(fn (WalkIn $walkIn) => (object) [
            'key' => 'walk-in-' . $walkIn->id,
            'identifier' => $walkIn->id,
            'paid_at' => $walkIn->paid_at,
            'source' => 'Walk-In',
            'name' => trim(($walkIn->first_name ?? '') . ' ' . ($walkIn->last_name ?? '')) ?: 'Walk-In',
            'searchable' => implode(' ', array_filter([
                $walkIn->first_name,
                $walkIn->last_name,
                $walkIn->receipt_no,
                $walkIn->id,
            ])),
            'subscription' => $walkIn->session_type,
            'method' => $walkIn->payment_method,
            'amount' => $walkIn->amount,
        ]));

        if ($ledgerMethod !== 'All') {
            $ledgerRows = $ledgerRows->where('method', $ledgerMethod);
        }
        if ($ledgerSource !== 'All') {
            $sourceLabel = $ledgerSource === 'Membership' ? 'Membership Payment' : 'Walk-In';
            $ledgerRows = $ledgerRows->where('source', $sourceLabel);
        }
        if ($ledgerSearch !== '') {
            $normalize = static fn (string $value): string => trim((string) preg_replace(
                '/[^\pL\pN]+/u',
                ' ',
                mb_strtolower($value)
            ));
            $searchTerms = array_filter(explode(' ', $normalize($ledgerSearch)));
            $ledgerRows = $ledgerRows->filter(function ($row) use ($normalize, $searchTerms) {
                $searchable = $normalize((string) $row->searchable);

                foreach ($searchTerms as $term) {
                    if (!str_contains($searchable, $term)) {
                        return false;
                    }
                }

                return true;
            });
        }

        $ledgerRows = (match ($ledgerSort) {
            'date_asc' => $ledgerRows->sortBy(fn ($row) => $row->paid_at?->timestamp ?? 0),
            'amount_asc' => $ledgerRows->sortBy('amount'),
            'amount_desc' => $ledgerRows->sortByDesc('amount'),
            default => $ledgerRows->sortByDesc(fn ($row) => $row->paid_at?->timestamp ?? 0),
        })->values();
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $ledgerPaginator = new LengthAwarePaginator(
            $ledgerRows->forPage($currentPage, $ledgerRowsPerPage)->values(),
            $ledgerRows->count(),
            $ledgerRowsPerPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

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
            'ledgerPaginator',
            'ledgerSearch',
            'ledgerMethod',
            'ledgerSource',
            'ledgerSort',
            'ledgerRowsPerPage',
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
