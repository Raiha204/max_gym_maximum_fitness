@extends('layouts.app')

@section('title', 'Revenue Reports — MAX GYM')

@section('content')
@php
    $datePayments = $allPayments->filter(fn($p) => optional($p->paid_at)->format('Y-m-d') === $selectedDate);
    $dateWalkIns = $allWalkIns->filter(fn($w) => optional($w->paid_at)->format('Y-m-d') === $selectedDate);
    $memRevenue = $datePayments->sum('amount');
    $walkInRevenue = $dateWalkIns->sum('amount');
    $totalRevenue = $memRevenue + $walkInRevenue;
    $cashRevenue = $datePayments->where('payment_method', 'Cash')->sum('amount') + $dateWalkIns->where('payment_method', 'Cash')->sum('amount');
    $gcashRevenue = $datePayments->where('payment_method', 'GCash')->sum('amount') + $dateWalkIns->where('payment_method', 'GCash')->sum('amount');
@endphp
<div class="space-y-6">
    <div class="bg-[#111111] text-white rounded-xl border-l-4 border-[#E31B23] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-white">Revenue Reports</h1>
            <p class="text-xs text-[#D1D5DB] mt-0.5">Detailed Walk-In and Membership Payment revenue breakdown with Excel UI export.</p>
        </div>
        <button type="button" onclick="exportExcelWorkbook()" class="px-4 py-2.5 text-xs font-bold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">
            Export Revenue (Excel .XLS)
        </button>
    </div>

    <!-- Choose Specific Date for Revenue (No Quick Dates & No Helper Text) -->
    <div class="p-4 rounded-xl bg-white border border-[#E5E7EB] flex flex-wrap items-center gap-3">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-3">
            <label class="block text-[11px] font-bold text-[#111111] uppercase tracking-wider">
                Choose Specific Date for Revenue
            </label>
            <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="px-3.5 py-2 text-xs font-mono font-bold rounded-lg border-2 border-[#E31B23] bg-white text-[#111111]">
        </form>
    </div>

    <!-- Summary Cards for Selected Date -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
        <div class="p-4 rounded-xl bg-[#111111] text-white">
            <div class="text-[#D1D5DB]">Total Revenue ({{ \Carbon\Carbon::parse($selectedDate)->format('M j, Y') }})</div>
            <div class="text-xl font-mono font-extrabold text-[#E31B23] mt-1">₱{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="p-4 rounded-xl bg-white border border-[#E5E7EB]">
            <div class="text-[#6B7280]">Membership Payments</div>
            <div class="text-xl font-mono font-bold text-[#111111] mt-1">₱{{ number_format($memRevenue, 2) }}</div>
        </div>
        <div class="p-4 rounded-xl bg-white border border-[#E5E7EB]">
            <div class="text-[#6B7280]">Walk-In Revenue</div>
            <div class="text-xl font-mono font-bold text-[#111111] mt-1">₱{{ number_format($walkInRevenue, 2) }}</div>
        </div>
        <div class="p-4 rounded-xl bg-white border border-[#E5E7EB]">
            <div class="text-[#6B7280]">Cash / GCash</div>
            <div class="text-sm font-mono font-bold text-[#111111] mt-1">Cash: ₱{{ number_format($cashRevenue, 2) }} · GCash: ₱{{ number_format($gcashRevenue, 2) }}</div>
        </div>
    </div>

    <!-- Excel UI Table -->
    <div class="bg-white rounded-xl border border-[#D1D5DB] overflow-hidden">
        <div class="bg-[#111111] text-white px-5 py-3 flex items-center justify-between text-xs font-bold border-l-2 border-[#E31B23]">
            <span>MAX GYM — EXCEL REVENUE WORKSHEET ({{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }})</span>
            <span class="font-mono">TOTAL: ₱{{ number_format($totalRevenue, 2) }}</span>
        </div>
        <table id="excel-revenue-table" class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#F3F4F6] border-b border-[#D1D5DB] text-[#111111]">
                    <th class="py-2.5 px-4 border-r border-[#E5E7EB]">Date & Time</th>
                    <th class="py-2.5 px-4 border-r border-[#E5E7EB]">Source</th>
                    <th class="py-2.5 px-4 border-r border-[#E5E7EB]">Details</th>
                    <th class="py-2.5 px-4 border-r border-[#E5E7EB]">Method</th>
                    <th class="py-2.5 px-4 text-right">Amount (PHP)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E7EB]">
                @foreach($datePayments as $p)
                    <tr>
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB] font-mono">{{ $p->paid_at?->copy()->setTimezone(config('app.display_timezone', 'Asia/Manila'))->format('Y-m-d g:i A') ?? '—' }}</td>
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB] font-semibold">Membership Payment</td>
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB]">{{ $p->payer_name }} ({{ $p->plan_label }})</td>
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB]">{{ $p->payment_method }}</td>
                        <td class="py-2.5 px-4 text-right font-mono font-bold">₱{{ number_format($p->amount, 2) }}</td>
                    </tr>
                @endforeach
                @foreach($dateWalkIns as $w)
                    <tr>
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB] font-mono">{{ $w->paid_at?->copy()->setTimezone(config('app.display_timezone', 'Asia/Manila'))->format('Y-m-d g:i A') ?? '—' }}</td>
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB] font-semibold">Walk-In</td>
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB]">{{ $w->receipt_no }} — {{ $w->session_type }}</td>
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB]">{{ $w->payment_method }}</td>
                        <td class="py-2.5 px-4 text-right font-mono font-bold">₱{{ number_format($w->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function exportExcelWorkbook() {
    const tableHtml = document.getElementById('excel-revenue-table').outerHTML.replace(/₱/g, 'PHP ');
    const excelDoc = '\uFEFF<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8"></head><body>' + tableHtml + '</body></html>';
    const blob = new Blob([excelDoc], { type: 'application/vnd.ms-excel;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'MAXGYM-Revenue-{{ $selectedDate }}.xls';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>
@endsection
