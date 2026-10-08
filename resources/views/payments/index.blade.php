@extends('layouts.app')

@section('title', 'Membership Payments & Revenue Summary — MAX GYM')

@section('content')
@php
    $todayTotal = $allPayments->filter(fn($p) => optional($p->paid_at)->isToday())->sum('amount')
        + $allWalkIns->filter(fn($w) => optional($w->paid_at)->isToday())->sum('amount');
    $weekTotal = $allPayments->filter(fn($p) => optional($p->paid_at)->greaterThanOrEqualTo(now()->subDays(7)->startOfDay()))->sum('amount')
        + $allWalkIns->filter(fn($w) => optional($w->paid_at)->greaterThanOrEqualTo(now()->subDays(7)->startOfDay()))->sum('amount');
    $monthTotal = $allPayments->filter(fn($p) => optional($p->paid_at)->isCurrentMonth())->sum('amount')
        + $allWalkIns->filter(fn($w) => optional($w->paid_at)->isCurrentMonth())->sum('amount');

    $datePayments = $allPayments->filter(fn($p) => optional($p->paid_at)->format('Y-m-d') === $selectedDate);
    $dateWalkIns = $allWalkIns->filter(fn($w) => optional($w->paid_at)->format('Y-m-d') === $selectedDate);
    $dateMemTotal = $datePayments->sum('amount');
    $dateWalkInTotal = $dateWalkIns->sum('amount');
    $dateGrandTotal = $dateMemTotal + $dateWalkInTotal;
    $dateCashTotal = $datePayments->where('payment_method', 'Cash')->sum('amount') + $dateWalkIns->where('payment_method', 'Cash')->sum('amount');
    $dateGcashTotal = $datePayments->where('payment_method', 'GCash')->sum('amount') + $dateWalkIns->where('payment_method', 'GCash')->sum('amount');
@endphp
<div class="space-y-6">
    <div class="bg-[#111111] text-white rounded-xl border-l-4 border-[#E31B23] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-white">Membership Payments & Revenue Summary</h1>
            <p class="text-xs text-[#D1D5DB] mt-0.5">Total revenue summary for Today, This Week, This Month, and Specific Date lookup.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="px-4 py-2 text-xs font-semibold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">Open Revenue Reports →</a>
    </div>

    <!-- Total Revenue Summary (Today, This Week, This Month) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-[#E5E7EB] border-t-4 border-t-[#E31B23] p-4 shadow-sm">
            <div class="text-xs font-bold text-[#6B7280]">Today's Revenue</div>
            <div class="text-2xl font-mono font-extrabold text-[#111111] mt-1">₱{{ number_format($todayTotal, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] border-t-4 border-t-[#374151] p-4 shadow-sm">
            <div class="text-xs font-bold text-[#6B7280]">This Week's Revenue</div>
            <div class="text-2xl font-mono font-extrabold text-[#111111] mt-1">₱{{ number_format($weekTotal, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] border-t-4 border-t-[#E31B23] p-4 shadow-sm">
            <div class="text-xs font-bold text-[#6B7280]">This Month's Revenue</div>
            <div class="text-2xl font-mono font-extrabold text-[#111111] mt-1">₱{{ number_format($monthTotal, 2) }}</div>
        </div>
    </div>

    <!-- Specific Date Lookup (No Quick Dates & No Helper Text) -->
    <div class="bg-white rounded-xl border border-[#E5E7EB] p-5 space-y-4">
        <form method="GET" action="{{ route('payments.index') }}" class="flex flex-wrap items-center justify-between gap-3">
            <input type="hidden" name="method" value="{{ $method }}">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#E31B23] text-white uppercase">Specific Date Lookup</span>
                <span class="text-xs font-bold text-[#111111]">Revenue for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-[#111111]">Select Date:</label>
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="px-3 py-1.5 text-xs font-mono font-bold rounded-lg border-2 border-[#E31B23] bg-white text-[#111111]">
            </div>
        </form>

        <div class="grid grid-cols-2 xl:grid-cols-4 gap-2.5 text-[10px]">
            <div class="p-3 rounded-lg bg-white border-l-2 border-[#E31B23] border-y border-r border-[#E5E7EB]">
                <div class="text-[#6B7280]">Total Revenue ({{ \Carbon\Carbon::parse($selectedDate)->format('M j, Y') }})</div>
                <div class="text-base font-mono font-bold text-[#E31B23] mt-1">₱{{ number_format($dateGrandTotal, 2) }}</div>
                <div class="text-[9px] text-[#6B7280] mt-1">{{ $datePayments->count() + $dateWalkIns->count() }} transactions</div>
            </div>
            <div class="p-3 rounded-lg bg-[#F9FAFB] border border-[#E5E7EB]">
                <div class="text-[#6B7280]">Membership Payment Revenue</div>
                <div class="text-base font-mono font-bold text-[#111111] mt-1">₱{{ number_format($dateMemTotal, 2) }}</div>
                <div class="text-[9px] text-[#6B7280] mt-1">{{ $datePayments->count() }} payments</div>
            </div>
            <div class="p-3 rounded-lg bg-[#F9FAFB] border border-[#E5E7EB]">
                <div class="text-[#6B7280]">Walk-In Revenue</div>
                <div class="text-base font-mono font-bold text-[#111111] mt-1">₱{{ number_format($dateWalkInTotal, 2) }}</div>
                <div class="text-[9px] text-[#6B7280] mt-1">{{ $dateWalkIns->count() }} sessions</div>
            </div>
            <div class="p-3 rounded-lg bg-[#F9FAFB] border border-[#E5E7EB]">
                <div class="text-[#6B7280]">Cash vs. GCash</div>
                <div class="text-[10px] font-mono font-bold text-[#111111] mt-1">Cash: ₱{{ number_format($dateCashTotal, 2) }}</div>
                <div class="text-[10px] font-mono font-bold text-[#111111] mt-1">GCash: ₱{{ number_format($dateGcashTotal, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Membership Payments Ledger -->
    <div class="bg-white rounded-xl border border-[#E5E7EB] p-6">
        <h2 class="text-sm font-bold text-[#111111] pb-3 mb-4 border-b border-[#E5E7EB]">Membership Payments Ledger</h2>
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#111111] text-white">
                    <th class="py-3 px-4">Member</th>
                    <th class="py-3 px-4">Category & Plan</th>
                    <th class="py-3 px-4">Payment Method</th>
                    <th class="py-3 px-4">Date & Time</th>
                    <th class="py-3 px-4 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E7EB]">
                @foreach($payments as $pay)
                    <tr>
                        <td class="py-3 px-4 font-semibold text-[#111111]">{{ $pay->payer_name }}</td>
                        <td class="py-3 px-4 text-[#6B7280]">{{ $pay->category }} — {{ $pay->plan_label }}</td>
                        <td class="py-3 px-4 text-[#6B7280]">{{ $pay->payment_method }}</td>
                        <td class="py-3 px-4 font-mono text-[#6B7280]">{{ $pay->paid_at?->copy()->setTimezone(config('app.display_timezone'))->format('M j, Y g:i A') ?? '—' }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-[#111111]">₱{{ number_format($pay->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
