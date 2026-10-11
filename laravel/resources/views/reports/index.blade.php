@extends('layouts.app')

@section('title', 'Revenue Reports — MAX GYM')

@section('content')
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
        <form id="revenue-ledger-filter-form" method="GET" action="{{ route('reports.index') }}" class="flex flex-col gap-3 border-b border-[#E5E7EB] bg-[#F9FAFB] px-4 py-3">
            <input type="hidden" name="date" value="{{ $selectedDate }}">
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
                <a href="{{ route('reports.index', ['date' => $selectedDate]) }}" class="rounded-lg border border-[#D1D5DB] bg-white px-4 py-2 text-xs font-semibold text-[#374151]">Reset</a>
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
        <table class="w-full min-w-[800px] border-separate border-spacing-0 text-left text-xs">
            <thead>
                <tr class="bg-[#F3F4F6] border-b border-[#D1D5DB] text-[#111111]">
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4 border-r border-[#E5E7EB]">Member / Walk-In ID</th>
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4 border-r border-[#E5E7EB]">Date & Time</th>
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4 border-r border-[#E5E7EB]">Source</th>
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4 border-r border-[#E5E7EB]">Name / Details</th>
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4 border-r border-[#E5E7EB]">Method</th>
                    <th class="sticky top-0 z-10 bg-[#F3F4F6] py-2.5 px-4 text-right">Amount (PHP)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E5E7EB]">
                @forelse($ledgerPaginator as $row)
                    <tr data-ledger-key="{{ $row->key }}">
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB] font-mono">{{ $row->identifier }}</td>
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB] font-mono">{{ $row->paid_at?->copy()->setTimezone(config('app.display_timezone', 'Asia/Manila'))->format('Y-m-d g:i A') ?? '—' }}</td>
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB] font-semibold">{{ $row->source }}</td>
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB]">{{ $row->name }} ({{ $row->subscription }})</td>
                        <td class="py-2.5 px-4 border-r border-[#E5E7EB]">{{ $row->method }}</td>
                        <td class="py-2.5 px-4 text-right font-mono font-bold">₱{{ number_format($row->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 px-4 text-center text-[#6B7280]">No revenue matches these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($ledgerPaginator->hasPages())
            <div class="border-t border-[#E5E7EB] px-4 py-3">
                {{ $ledgerPaginator->links() }}
            </div>
        @endif
    </div>
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
