@extends('layouts.app')

@section('title', 'Members & Prepaid Memberships — MAX GYM')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white rounded-xl border border-neutral-200 p-5">
        <div>
            <h1 class="text-xl font-bold text-[#0B0B0E]">Members & Prepaid Memberships</h1>
            <p class="text-xs text-neutral-500 mt-0.5">Prepaid membership directory with active/expired status and days remaining.</p>
        </div>
        <a href="{{ route('memberships.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 text-xs font-semibold text-white bg-[#E31E24] hover:bg-[#c8191f] rounded-lg">+ Register Member</a>
    </div>

    <!-- Search & Status Filter Tabs -->
    <div class="relative z-20 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 bg-white rounded-xl border border-neutral-200 p-4">
        <form id="membership-search-form" method="GET" action="{{ route('memberships.index') }}" class="flex-1 flex items-start gap-2">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <div class="relative flex-1">
                <input id="membership-search" type="search" name="search" value="{{ $search }}" autocomplete="off" aria-autocomplete="list" aria-controls="membership-search-results" placeholder="Search by Member ID, Last Name, First Name, Email, or Phone..." class="w-full px-3.5 py-2 text-xs rounded-lg border border-neutral-300 focus:outline-none focus:border-[#E31E24]">
                <div id="membership-search-popover" class="absolute left-0 right-0 top-full z-50 mt-2 hidden">
                    <p id="membership-search-feedback" class="hidden rounded-t-lg border border-neutral-200 bg-white px-3 py-2 text-xs shadow-lg"></p>
                    <div id="membership-search-results" role="listbox" class="hidden overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-lg"></div>
                </div>
            </div>
            <button type="submit" class="shrink-0 px-4 py-2 text-xs font-semibold text-white bg-[#0B0B0E] rounded-lg">Search</button>
        </form>

        <div class="flex items-center gap-1 p-1 bg-neutral-100 rounded-lg">
            @foreach(['all' => 'All (' . $counts['all'] . ')', 'active' => 'Active (' . $counts['active'] . ')', 'partial' => 'Daily Credit (' . $counts['partial'] . ')', 'expiring' => 'Expiring (' . $counts['expiring'] . ')', 'expired' => 'Expired (' . $counts['expired'] . ')'] as $key => $label)
                <a href="{{ route('memberships.index', ['status' => $key, 'search' => $search]) }}" class="px-3 py-1.5 text-xs font-semibold rounded-md {{ $statusFilter === $key ? 'bg-[#0B0B0E] text-white' : 'text-neutral-600 hover:text-[#0B0B0E]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Members table: ID, Last Name, First Name, then due date (nearest due first) -->
    <div class="relative z-0 bg-white rounded-xl border border-neutral-200 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-[#111111] text-white text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3.5 font-bold">Member ID</th>
                    <th class="px-4 py-3.5 font-bold">Last Name</th>
                    <th class="px-4 py-3.5 font-bold">First Name</th>
                    <th class="px-4 py-3.5 font-bold">Due Date</th>
                    <th class="px-4 py-3.5 font-bold">Days Left</th>
                    <th class="px-4 py-3.5 font-bold">Status</th>
                    <th class="px-4 py-3.5 font-bold">Membership</th>
                    <th class="px-4 py-3.5 font-bold">Contact</th>
                    <th class="px-4 py-3.5 font-bold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200">
                @forelse($memberships as $member)
                    @php
                        $isActive = $member->membership_status === 'Active';
                        $isPartial = $member->membership_status === 'Partial';
                        $soon = $isActive && $member->days_remaining <= 7;
                    @endphp
                    <tr class="align-middle">
                        <td class="px-4 py-4 font-mono text-xs font-bold text-[#E31B23] whitespace-nowrap">{{ $member->member_id }}</td>
                        <td class="px-4 py-4 font-extrabold text-[#0B0B0E]">{{ $member->surname }}</td>
                        <td class="px-4 py-4 font-semibold text-[#0B0B0E]">{{ $member->given_name ?: '—' }}</td>
                        <td class="px-4 py-4 font-bold whitespace-nowrap {{ $isActive ? ($soon ? 'text-amber-600' : 'text-[#0B0B0E]') : ($isPartial ? 'text-amber-600' : 'text-red-600') }}">{{ optional($member->end_date)->format('M j, Y') ?? '—' }}</td>
                        <td class="px-4 py-4 font-mono font-bold whitespace-nowrap {{ $isActive ? ($soon ? 'text-amber-600' : 'text-emerald-700') : ($isPartial ? 'text-amber-600' : 'text-red-600') }}">{{ $member->days_remaining }} {{ $member->days_remaining === 1 ? 'day' : 'days' }}</td>
                        <td class="px-4 py-4">
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $isActive ? ($soon ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') : ($isPartial ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ $isActive ? ($soon ? 'Expiring' : 'Active') : $member->membership_status }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-semibold text-neutral-800">{{ str_replace(' Membership', '', $member->plan_type) }}</div>
                        </td>
                        <td class="px-4 py-4 text-neutral-600 text-xs max-w-[190px]">
                            <div class="font-semibold text-neutral-800">{{ $member->phone }}</div>
                            <div class="break-all">{{ $member->email }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('memberships.show', $member) }}" class="px-3.5 py-2 text-xs font-bold text-white bg-[#0B0B0E] hover:bg-neutral-800 rounded-lg whitespace-nowrap">View Card</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-sm text-neutral-500">No memberships found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
(() => {
    const form = document.getElementById('membership-search-form');
    const input = document.getElementById('membership-search');
    const popover = document.getElementById('membership-search-popover');
    const results = document.getElementById('membership-search-results');
    const feedback = document.getElementById('membership-search-feedback');
    if (!form || !input || !popover || !results || !feedback) return;

    let timer;
    let requestId = 0;
    input.addEventListener('input', () => {
        clearTimeout(timer);
        const query = input.value.trim();
        const currentRequest = ++requestId;
        results.replaceChildren();
        results.classList.add('hidden');
        feedback.classList.add('hidden');
        popover.classList.toggle('hidden', !query);
        if (!query) return;

        timer = setTimeout(async () => {
            feedback.textContent = 'Searching members...';
            feedback.className = 'rounded-t-lg border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-500 shadow-lg';
            popover.classList.remove('hidden');
            feedback.classList.remove('hidden');
            try {
                const response = await fetch(`{{ route('memberships.search') }}?q=${encodeURIComponent(query)}`);
                if (!response.ok) throw new Error('Member search failed.');
                const data = await response.json();
                if (currentRequest !== requestId) return;
                if (data.members.length === 0) {
                    feedback.textContent = 'No matching members found.';
                    feedback.className = 'rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-500 shadow-lg';
                    return;
                }

                data.members.forEach((member) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.memberId = member.member_id;
                    button.setAttribute('role', 'option');
                    button.className = 'w-full flex items-center justify-between gap-3 border-b border-neutral-100 px-3 py-2.5 text-left last:border-b-0 hover:bg-neutral-50';
                    const details = document.createElement('span');
                    details.className = 'min-w-0';
                    const name = document.createElement('span');
                    name.className = 'block truncate text-xs font-semibold text-[#111111]';
                    name.textContent = member.full_name;
                    const status = document.createElement('span');
                    status.className = 'block truncate text-[10px] text-neutral-500';
                    status.textContent = `${member.membership_type} · ${member.status}`;
                    details.append(name, status);
                    const id = document.createElement('span');
                    id.className = 'shrink-0 font-mono text-[10px] font-bold text-[#E31B23]';
                    id.textContent = member.member_id;
                    button.append(details, id);
                    results.append(button);
                });
                results.classList.remove('hidden');
                feedback.classList.remove('rounded-lg');
                feedback.classList.add('rounded-t-lg');
                feedback.textContent = `${data.members.length} matching member${data.members.length === 1 ? '' : 's'} found. Select one to view the card.`;
                feedback.className = 'rounded-t-lg border border-neutral-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 shadow-lg';
            } catch {
                if (currentRequest !== requestId) return;
                feedback.textContent = 'Could not search for members. Please try again.';
                feedback.className = 'rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 shadow-lg';
            }
        }, 250);
    });

    results.addEventListener('click', (event) => {
        const option = event.target.closest('[data-member-id]');
        if (!option) return;
        input.value = option.dataset.memberId;
        popover.classList.add('hidden');
        form.requestSubmit();
    });
})();
</script>
@endsection
