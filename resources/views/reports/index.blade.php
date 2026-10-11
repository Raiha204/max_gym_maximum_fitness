@extends('layouts.app')

@section('title', 'Revenue Reports — MAX GYM')

@section('content')
<div class="space-y-6">
    <div class="bg-[#111111] text-white rounded-xl border-l-4 border-[#E31B23] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-white">Revenue Reports</h1>
            <p class="text-xs text-[#D1D5DB] mt-0.5">Review revenue by day, month, or year.</p>
        </div>
        <button type="button" onclick="exportRevenueWorkbook()" class="gym-primary px-4 py-2.5 text-xs">
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
            <button class="gym-primary px-4 py-2 text-xs">View date</button>
        </form>

        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="period" value="month">
            <div>
                <label for="report-month" class="block text-[11px] font-bold text-[#111111] uppercase tracking-wider mb-1">Monthly report</label>
                <input id="report-month" type="month" name="month" value="{{ $selectedMonth }}" required class="px-3.5 py-2 text-xs font-mono font-bold rounded-lg border-2 border-[#E31B23] bg-white text-[#111111]">
            </div>
            <button class="gym-primary px-4 py-2 text-xs">View month</button>
        </form>

        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="period" value="year">
            <div>
                <label for="report-year" class="block text-[11px] font-bold text-[#111111] uppercase tracking-wider mb-1">Yearly report</label>
                <input id="report-year" type="number" name="year" value="{{ $selectedYear }}" min="1900" max="2100" required class="w-28 px-3.5 py-2 text-xs font-mono font-bold rounded-lg border-2 border-[#E31B23] bg-white text-[#111111]">
            </div>
            <button class="gym-primary px-4 py-2 text-xs">View year</button>
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

    <div class="bg-white rounded-xl border border-[#D1D5DB] overflow-hidden">
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
        <form id="revenue-ledger-filter-form" method="GET" action="{{ route('reports.index') }}" class="flex flex-col gap-3 border-b border-[#E5E7EB] bg-[#F9FAFB] px-4 py-3">
            <input type="hidden" name="period" value="{{ $period }}">
            <input type="hidden" name="date" value="{{ $selectedDate }}">
            <input type="hidden" name="month" value="{{ $selectedMonth }}">
            <input type="hidden" name="year" value="{{ $selectedYear }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[180px] flex-1">
                    <label for="ledger-search" class="mb-1 block text-[10px] font-semibold text-[#6B7280]">Search member or walk-in name / ID</label>
                    <div class="relative">
                        <input id="ledger-search" name="search" type="search" value="{{ $ledgerSearch }}" autocomplete="off" aria-autocomplete="list" aria-controls="ledger-search-results" placeholder="Search..." class="w-full rounded-lg border border-[#D1D5DB] bg-white px-3 py-2 text-xs">
                        <p id="ledger-search-feedback" class="hidden mt-1 text-xs"></p>
                        <div id="ledger-search-results" role="listbox" class="hidden mt-2 overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-lg"></div>
                    </div>
                </div>
                <div>
                    <label for="ledger-method" class="mb-1 block text-[10px] font-semibold text-[#6B7280]">Payment method</label>
                    <select id="ledger-method" name="payment_method" class="rounded-lg border border-[#D1D5DB] bg-white px-3 py-2 text-xs">
                        @foreach(['All' => 'All Methods', 'Cash' => 'Cash', 'GCash' => 'GCash'] as $value => $label)
                            <option value="{{ $value }}" @selected($ledgerMethod === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="ledger-source" class="mb-1 block text-[10px] font-semibold text-[#6B7280]">Source</label>
                    <select id="ledger-source" name="source" class="rounded-lg border border-[#D1D5DB] bg-white px-3 py-2 text-xs">
                        @foreach(['All' => 'All Sources', 'Membership' => 'Membership', 'Walk-In' => 'Walk-In'] as $value => $label)
                            <option value="{{ $value }}" @selected($ledgerSource === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="ledger-sort" class="mb-1 block text-[10px] font-semibold text-[#6B7280]">Sort by</label>
                    <select id="ledger-sort" name="sort" class="rounded-lg border border-[#D1D5DB] bg-white px-3 py-2 text-xs">
                        <option value="date_desc" @selected($ledgerSort === 'date_desc')>Date: Newest first</option>
                        <option value="date_asc" @selected($ledgerSort === 'date_asc')>Date: Oldest first</option>
                        <option value="amount_desc" @selected($ledgerSort === 'amount_desc')>Amount: Highest first</option>
                        <option value="amount_asc" @selected($ledgerSort === 'amount_asc')>Amount: Lowest first</option>
                    </select>
                </div>
                <a href="{{ route('reports.index', ['period' => $period, 'date' => $selectedDate, 'month' => $selectedMonth, 'year' => $selectedYear]) }}" class="rounded-lg border border-[#D1D5DB] bg-white px-4 py-2 text-xs font-semibold text-[#374151]">Reset</a>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 text-[11px] text-[#6B7280]">
                <span>Showing {{ $ledgerPaginator->firstItem() ?? 0 }} to {{ $ledgerPaginator->lastItem() ?? 0 }} of {{ $ledgerPaginator->total() }} entries</span>
                <label class="flex items-center gap-2">
                    Show
                    <select name="rows" class="rounded border border-[#D1D5DB] bg-white px-2 py-1">
                        @foreach([10, 30, 50, 100] as $rows)
                            <option value="{{ $rows }}" @selected($ledgerRowsPerPage === $rows)>{{ $rows }}</option>
                        @endforeach
                    </select>
                    entries
                </label>
            </div>
        </form>
        <div class="max-h-[32rem] overflow-auto">
        <table class="w-full min-w-[1000px] border-separate border-spacing-0 text-left text-xs">
            <thead>
                <tr class="bg-[#F3F4F6] border-b border-[#D1D5DB] text-[#111111]">
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4">Member / Walk-In ID</th>
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4 text-center">Date</th>
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4">Time</th>
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4">Source</th>
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4">Name</th>
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4">Subscription</th>
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4">Method</th>
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4 text-right">Amount (PHP)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E7EB]">
                @forelse($ledgerPaginator as $row)
                    <tr data-ledger-key="{{ $row->key }}">
                        <td class="py-2.5 px-4 font-mono">{{ $row->identifier }}</td>
                        <td class="py-2.5 px-4 text-center font-mono">{{ $row->paid_at?->copy()->setTimezone(config('app.display_timezone'))->format('Y-m-d') ?? '—' }}</td>
                        <td class="py-2.5 px-4 whitespace-nowrap font-mono">{{ $row->paid_at?->copy()->setTimezone(config('app.display_timezone'))->format('g:i A') ?? '—' }}</td>
                        <td class="py-2.5 px-4 font-semibold">{{ $row->source }}</td>
                        <td class="py-2.5 px-4">{{ $row->name }}</td>
                        <td class="py-2.5 px-4">{{ $row->subscription }}</td>
                        <td class="py-2.5 px-4">{{ $row->method }}</td>
                        <td class="py-2.5 px-4 text-right font-mono font-bold">₱{{ number_format($row->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-8 px-4 text-center text-[#6B7280]">No revenue matches these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($ledgerPaginator->hasPages())
            <div class="border-t border-[#E5E7EB] px-4 py-3">
                {{ $ledgerPaginator->links() }}
            </div>
        @endif
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
(() => {
    const form = document.getElementById('revenue-ledger-filter-form');
    if (!form) return;

    const search = form.querySelector('[name="search"]');
    const results = document.getElementById('ledger-search-results');
    const feedback = document.getElementById('ledger-search-feedback');
    let searchTimer;
    let searchRequest = 0;
    search?.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const query = search.value.trim();
        const requestId = ++searchRequest;
        results?.replaceChildren();
        results?.classList.add('hidden');
        feedback?.classList.add('hidden');
        if (!query || !results || !feedback) return;

        searchTimer = setTimeout(async () => {
            feedback.textContent = 'Searching revenue...';
            feedback.className = 'mt-1 text-xs text-neutral-500';
            try {
                const params = new URLSearchParams(new FormData(form));
                params.set('q', query);
                const response = await fetch(`{{ route('reports.search') }}?${params.toString()}`);
                if (!response.ok) throw new Error('Revenue search failed.');
                const data = await response.json();
                if (requestId !== searchRequest) return;
                if (data.matches.length === 0) {
                    feedback.textContent = 'No matching member or walk-in payments found for this report period.';
                    feedback.className = 'mt-1 text-xs text-neutral-500';
                    return;
                }

                data.matches.forEach((match) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.searchValue = match.search_value ?? '';
                    button.setAttribute('role', 'option');
                    button.className = 'w-full flex items-center justify-between gap-3 border-b border-neutral-100 px-3 py-2.5 text-left last:border-b-0 hover:bg-neutral-50';
                    const details = document.createElement('span');
                    details.className = 'min-w-0';
                    const name = document.createElement('span');
                    name.className = 'block truncate text-xs font-semibold text-[#111111]';
                    name.textContent = match.name;
                    const subtitle = document.createElement('span');
                    subtitle.className = 'block truncate text-[10px] text-neutral-500';
                    subtitle.textContent = `${match.source} · ${match.details}`;
                    details.append(name, subtitle);
                    const identifier = document.createElement('span');
                    identifier.className = 'shrink-0 font-mono text-[10px] font-bold text-[#E31B23]';
                    identifier.textContent = match.identifier;
                    button.append(details, identifier);
                    results.append(button);
                });
                results.classList.remove('hidden');
                feedback.textContent = `${data.matches.length} matching revenue record${data.matches.length === 1 ? '' : 's'} found. Select one to view.`;
                feedback.className = 'mt-1 text-xs font-semibold text-emerald-700';
            } catch {
                if (requestId !== searchRequest) return;
                feedback.textContent = 'Could not search revenue. Please try again.';
                feedback.className = 'mt-1 text-xs font-semibold text-red-600';
            }
        }, 250);
    });

    results?.addEventListener('click', (event) => {
        const option = event.target.closest('[data-search-value]');
        if (!option || !search) return;
        search.value = option.dataset.searchValue;
        form.requestSubmit();
    });

    form.querySelectorAll('select').forEach((select) => {
        select.addEventListener('change', () => form.requestSubmit());
    });
})();

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
