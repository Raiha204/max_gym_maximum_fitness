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
        $method = $request->query('method', 'All');
        $selectedDate = $request->query('date', Carbon::today()->format('Y-m-d'));

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
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $displayTimezone = config('app.display_timezone', 'Asia/Manila');
        $selectedDate = $validated['date'] ?? Carbon::now($displayTimezone)->format('Y-m-d');
        $localDate = Carbon::parse($selectedDate, $displayTimezone);
        $rangeStart = $localDate->copy()->startOfDay()->setTimezone('UTC');
        $rangeEnd = $localDate->copy()->endOfDay()->setTimezone('UTC');
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
            'searchable' => implode(' ', array_filter([$walkIn->receipt_no, $walkIn->id])),
            'search_value' => $walkIn->receipt_no,
            'identifier' => $walkIn->receipt_no,
            'name' => $walkIn->receipt_no,
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
            'date' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'in:All,Cash,GCash'],
            'source' => ['nullable', 'in:All,Membership,Walk-In'],
            'sort' => ['nullable', 'in:date_desc,date_asc,amount_desc,amount_asc'],
            'rows' => ['nullable', 'integer', 'in:10,30,50,100'],
        ]);
        $displayTimezone = config('app.display_timezone', 'Asia/Manila');
        $selectedDate = $validated['date'] ?? Carbon::now($displayTimezone)->format('Y-m-d');
        $localDate = Carbon::parse($selectedDate, $displayTimezone);
        $rangeStart = $localDate->copy()->startOfDay()->setTimezone('UTC');
        $rangeEnd = $localDate->copy()->endOfDay()->setTimezone('UTC');
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
        $cashRevenue = $datePayments->where('payment_method', 'Cash')->sum('amount')
            + $dateWalkIns->where('payment_method', 'Cash')->sum('amount');
        $gcashRevenue = $datePayments->where('payment_method', 'GCash')->sum('amount')
            + $dateWalkIns->where('payment_method', 'GCash')->sum('amount');

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
            'identifier' => $walkIn->receipt_no,
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
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('reports.index', compact(
            'datePayments',
            'dateWalkIns',
            'totalRevenue',
            'memRevenue',
            'walkInRevenue',
            'cashRevenue',
            'gcashRevenue',
            'selectedDate',
            'ledgerPaginator',
            'ledgerSearch',
            'ledgerMethod',
            'ledgerSource',
            'ledgerSort',
            'ledgerRowsPerPage'
        ));
    }
}
