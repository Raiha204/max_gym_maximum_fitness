@extends('layouts.app')

@section('title', 'Revenue Reports — MAX GYM')

@section('content')
<div class="space-y-6">
    <div class="bg-[#111111] text-white rounded-xl border-l-4 border-[#E31B23] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-white">Revenue Reports</h1>
            <p class="text-xs text-[#D1D5DB] mt-0.5">Review revenue by day, month, or year.</p>
        </div>
        <button type="button" onclick="exportRevenueWorkbook()" class="px-4 py-2.5 text-xs font-bold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">
            Export to Excel
        </button>
    </div>

    <div class="p-4 rounded-xl bg-white border border-[#E5E7EB] flex flex-col sm:flex-row sm:items-end gap-4">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="period" value="date">
            <div>
                <label for="report-date" class="block text-[11px] font-bold text-[#111111] uppercase tracking-wider mb-1">Daily report</label>
                <input id="report-date" type="date" name="date" value="{{ $selectedDate }}" required class="px-3.5 py-2 text-xs font-mono font-bold rounded-lg border-2 border-[#E31B23] bg-white text-[#111111]">
            </div>
            <button class="px-4 py-2 text-xs font-bold text-white bg-[#111111] hover:bg-[#333333] rounded-lg">View date</button>
        </form>

        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="period" value="month">
            <div>
                <label for="report-month" class="block text-[11px] font-bold text-[#111111] uppercase tracking-wider mb-1">Monthly report</label>
                <input id="report-month" type="month" name="month" value="{{ $selectedMonth }}" required class="px-3.5 py-2 text-xs font-mono font-bold rounded-lg border-2 border-[#E31B23] bg-white text-[#111111]">
            </div>
            <button class="px-4 py-2 text-xs font-bold text-white bg-[#111111] hover:bg-[#333333] rounded-lg">View month</button>
        </form>

        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="period" value="year">
            <div>
                <label for="report-year" class="block text-[11px] font-bold text-[#111111] uppercase tracking-wider mb-1">Yearly report</label>
                <input id="report-year" type="number" name="year" value="{{ $selectedYear }}" min="1900" max="2100" required class="w-28 px-3.5 py-2 text-xs font-mono font-bold rounded-lg border-2 border-[#E31B23] bg-white text-[#111111]">
            </div>
            <button class="px-4 py-2 text-xs font-bold text-white bg-[#111111] hover:bg-[#333333] rounded-lg">View year</button>
        </form>
    </div>

    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-[#111111]">{{ $period === 'year' ? 'Yearly Revenue by Month' : ($period === 'month' ? 'Monthly Revenue' : 'Daily Revenue') }}</h2>
        <span class="text-sm font-semibold text-[#6B7280]">{{ $periodLabel }}</span>
    </div>

    @if($period === 'year')
        <div class="p-4 rounded-xl bg-[#111111] text-white text-xs">
            <div class="text-[#D1D5DB]">Total Revenue for {{ $periodLabel }}</div>
            <div class="text-xl font-mono font-extrabold text-[#E31B23] mt-1">₱{{ number_format($totalRevenue, 2) }}</div>
        </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <div class="p-4 rounded-xl bg-[#111111] text-white">
            <div class="text-[#D1D5DB]">Total Revenue</div>
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
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-xs">
        @foreach([
            ['Membership Payments', $membershipCashRevenue, $membershipGcashRevenue, $memRevenue],
            ['Walk-In Revenue', $walkInCashRevenue, $walkInGcashRevenue, $walkInRevenue],
        ] as [$source, $cash, $gcash, $combined])
            <div class="p-4 rounded-xl bg-white border border-[#E5E7EB]">
                <h3 class="font-bold text-[#111111] mb-3">{{ $source }}</h3>
                <div class="grid grid-cols-3 gap-3">
                    <div><div class="text-[#6B7280]">Cash</div><div class="font-mono font-bold mt-1">₱{{ number_format($cash, 2) }}</div></div>
                    <div><div class="text-[#6B7280]">GCash</div><div class="font-mono font-bold mt-1">₱{{ number_format($gcash, 2) }}</div></div>
                    <div><div class="text-[#6B7280]">Combined</div><div class="font-mono font-bold text-[#E31B23] mt-1">₱{{ number_format($combined, 2) }}</div></div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <div class="bg-white rounded-xl border border-[#D1D5DB] overflow-x-auto">
        <div class="bg-[#111111] text-white {{ $period === 'year' ? '' : 'min-w-[1000px]' }} px-5 py-3 flex items-center justify-between text-xs font-bold border-l-2 border-[#E31B23]">
            <span>{{ $period === 'year' ? 'Monthly revenue totals — ' . $periodLabel : 'Revenue ledger — ' . $periodLabel }}</span>
            <span class="font-mono">TOTAL: ₱{{ number_format($totalRevenue, 2) }}</span>
        </div>
        @if($period === 'year')
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-[#F3F4F6] border-b border-[#D1D5DB] text-[#111111]">
                        <th class="py-3 px-4">Month</th>
                        <th class="py-3 px-4 text-right">Total Revenue (PHP)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @foreach($monthlyRevenue as $monthRevenue)
                        <tr>
                            <td class="py-3 px-4 font-semibold">Total revenue for {{ $monthRevenue['month'] }}</td>
                            <td class="py-3 px-4 text-right font-mono font-bold">₱{{ number_format($monthRevenue['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
        <table class="w-full min-w-[1000px] text-left border-collapse text-xs">
            <thead>
                <tr class="bg-[#F3F4F6] border-b border-[#D1D5DB] text-[#111111]">
                    <th class="py-2.5 px-4">Member / Walk-In ID</th>
                    <th class="py-2.5 px-4 text-center">Date</th>
                    <th class="py-2.5 px-4">Time</th>
                    <th class="py-2.5 px-4">Source</th>
                    <th class="py-2.5 px-4">Name</th>
                    <th class="py-2.5 px-4">Subscription</th>
                    <th class="py-2.5 px-4">Method</th>
                    <th class="py-2.5 px-4 text-right">Amount (PHP)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E7EB]">
                @forelse($datePayments as $p)
                    <tr>
                        <td class="py-2.5 px-4 font-mono">{{ $p->membership?->member_id ?? '—' }}</td>
                        <td class="py-2.5 px-4 text-center font-mono text-sm font-bold">{{ $p->paid_at?->copy()->setTimezone(config('app.display_timezone'))->format('Y-m-d') ?? '—' }}</td>
                        <td class="py-2.5 px-4 whitespace-nowrap font-mono">{{ $p->paid_at?->copy()->setTimezone(config('app.display_timezone'))->format('g:i A') ?? '—' }}</td>
                        <td class="py-2.5 px-4 font-semibold">Membership Payment</td>
                        <td class="py-2.5 px-4">{{ $p->payer_name }}</td>
                        <td class="py-2.5 px-4">{{ $p->plan_label ?: '—' }}</td>
                        <td class="py-2.5 px-4">{{ $p->payment_method }}</td>
                        <td class="py-2.5 px-4 text-right font-mono font-bold">₱{{ number_format($p->amount, 2) }}</td>
                    </tr>
                @empty
                    @if($dateWalkIns->isEmpty())
                        <tr><td colspan="8" class="py-8 px-4 text-center text-[#6B7280]">No revenue recorded for this period.</td></tr>
                    @endif
                @endforelse
                @foreach($dateWalkIns as $w)
                    <tr>
                        <td class="py-2.5 px-4 font-mono">{{ $w->id }}</td>
                        <td class="py-2.5 px-4 text-center font-mono text-sm font-bold">{{ $w->paid_at?->copy()->setTimezone(config('app.display_timezone'))->format('Y-m-d') ?? '—' }}</td>
                        <td class="py-2.5 px-4 whitespace-nowrap font-mono">{{ $w->paid_at?->copy()->setTimezone(config('app.display_timezone'))->format('g:i A') ?? '—' }}</td>
                        <td class="py-2.5 px-4 font-semibold">Walk-In</td>
                        <td class="py-2.5 px-4">{{ trim(($w->first_name ?? '') . ' ' . ($w->last_name ?? '')) ?: 'Walk-In' }}</td>
                        <td class="py-2.5 px-4">{{ $w->session_type }}</td>
                        <td class="py-2.5 px-4">{{ $w->payment_method }}</td>
                        <td class="py-2.5 px-4 text-right font-mono font-bold">₱{{ number_format($w->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <table id="excel-revenue-table" class="hidden" aria-hidden="true">
        @if($period === 'year')
            <colgroup>
                <col width="180">
                <col width="150">
            </colgroup>
            <thead>
                <tr><th colspan="2">MAX GYM — {{ $periodLabel }} MONTHLY REVENUE</th></tr>
                <tr><th>Month</th><th>Total Revenue (PHP)</th></tr>
            </thead>
            <tbody>
                @foreach($monthlyRevenue as $monthRevenue)
                    <tr><td>{{ $monthRevenue['month'] }}</td><td>PHP {{ number_format($monthRevenue['total'], 2, '.', '') }}</td></tr>
                @endforeach
                <tr><td>TOTAL REVENUE</td><td>PHP {{ number_format($totalRevenue, 2, '.', '') }}</td></tr>
            </tbody>
        @else
        <colgroup>
            <col width="165">
            <col width="95">
            <col width="85">
            <col width="145">
            <col width="175">
            <col width="235">
            <col width="70">
            <col width="120">
        </colgroup>
        <thead>
            <tr><th colspan="8">MAX GYM — {{ strtoupper($periodLabel) }} REVENUE</th></tr>
            <tr><td colspan="7">Membership Payments Total</td><td>PHP {{ number_format($memRevenue, 2, '.', '') }}</td></tr>
            <tr><td colspan="7">Walk-In Revenue Total</td><td>PHP {{ number_format($walkInRevenue, 2, '.', '') }}</td></tr>
            <tr><td colspan="7">TOTAL REVENUE</td><td>PHP {{ number_format($totalRevenue, 2, '.', '') }}</td></tr>
            <tr><th>Member / Walk-In ID</th><th>Date</th><th>Time</th><th>Source</th><th>Name</th><th>Subscription</th><th>Method</th><th>Amount (PHP)</th></tr>
        </thead>
        <tbody>
            @foreach($datePayments as $p)
                <tr>
                    <td>{{ $p->membership?->member_id ?? '—' }}</td>
                    <td>{{ $p->paid_at?->copy()->setTimezone(config('app.display_timezone'))->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $p->paid_at?->copy()->setTimezone(config('app.display_timezone'))->format('g:i A') ?? '—' }}</td>
                    <td>Membership Payment</td>
                    <td>{{ $p->payer_name }}</td>
                    <td>{{ $p->plan_label ?: '—' }}</td>
                    <td>{{ $p->payment_method }}</td>
                    <td>PHP {{ number_format($p->amount, 2, '.', '') }}</td>
                </tr>
            @endforeach
            @foreach($dateWalkIns as $w)
                <tr>
                    <td>{{ $w->id }}</td>
                    <td>{{ $w->paid_at?->copy()->setTimezone(config('app.display_timezone'))->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $w->paid_at?->copy()->setTimezone(config('app.display_timezone'))->format('g:i A') ?? '—' }}</td>
                    <td>Walk-In</td>
                    <td>{{ trim(($w->first_name ?? '') . ' ' . ($w->last_name ?? '')) ?: 'Walk-In' }}</td>
                    <td>{{ $w->session_type }}</td>
                    <td>{{ $w->payment_method }}</td>
                    <td>PHP {{ number_format($w->amount, 2, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
        @endif
    </table>
</div>

<script>
function exportRevenueWorkbook() {
    const tableHtml = document.getElementById('excel-revenue-table').outerHTML;
    const workbook = '\uFEFF<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif}table{border-collapse:collapse;table-layout:fixed;width:1090px}col{mso-width-source:userset}th,td{border:1px solid #C8CDD4;padding:5px 7px;vertical-align:middle}th{background:#D1D5DB;text-align:left;font-weight:bold}thead tr:first-child th{background:#D1D5DB;color:#000;text-align:center;font-size:12pt;font-weight:bold}thead tr:nth-child(2) td,thead tr:nth-child(3) td,thead tr:nth-child(4) td{background:#fff}thead tr:nth-child(2) td:last-child,thead tr:nth-child(3) td:last-child,thead tr:nth-child(4) td:last-child{text-align:right;white-space:nowrap}thead tr:last-child th:nth-child(1),thead tr:last-child th:nth-child(2),thead tr:last-child th:nth-child(3),thead tr:last-child th:nth-child(7),thead tr:last-child th:nth-child(8){text-align:center}tbody td:nth-child(1),tbody td:nth-child(2),tbody td:nth-child(3),tbody td:nth-child(4),tbody td:nth-child(5),tbody td:nth-child(6),tbody td:nth-child(7){text-align:center}tbody td:nth-child(2){font-weight:bold;font-size:12pt}tbody td:last-child{text-align:right;white-space:nowrap}</style></head><body>' + tableHtml + '</body></html>';
    const blob = new Blob([workbook], { type: 'application/vnd.ms-excel;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'MAXGYM-Revenue-{{ $exportPeriod }}.xls';
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}
</script>
@endsection
