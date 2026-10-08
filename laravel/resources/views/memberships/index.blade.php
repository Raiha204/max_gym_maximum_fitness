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
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 bg-white rounded-xl border border-neutral-200 p-4">
        <form method="GET" action="{{ route('memberships.index') }}" class="flex-1 flex gap-2">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by Member ID, Surname, First Name, Email, or Phone..." class="flex-1 px-3.5 py-2 text-xs rounded-lg border border-neutral-300 focus:outline-none focus:border-[#E31E24]">
            <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#0B0B0E] rounded-lg">Search</button>
        </form>

        <div class="flex items-center gap-1 p-1 bg-neutral-100 rounded-lg">
            @foreach(['all' => 'All (' . $counts['all'] . ')', 'active' => 'Active (' . $counts['active'] . ')', 'partial' => 'Partial (' . $counts['partial'] . ')', 'expiring' => 'Expiring (' . $counts['expiring'] . ')', 'expired' => 'Expired (' . $counts['expired'] . ')'] as $key => $label)
                <a href="{{ route('memberships.index', ['status' => $key, 'search' => $search]) }}" class="px-3 py-1.5 text-xs font-semibold rounded-md {{ $statusFilter === $key ? 'bg-[#0B0B0E] text-white' : 'text-neutral-600 hover:text-[#0B0B0E]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Members table: Surname, First Name, then due date (nearest due first) -->
    <div class="bg-white rounded-xl border border-neutral-200 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-[#111111] text-white text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-4 py-3.5 font-bold">Surname</th>
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
                        <td class="px-4 py-4 font-extrabold text-[#0B0B0E]">{{ $member->surname }}</td>
                        <td class="px-4 py-4 font-semibold text-[#0B0B0E]">{{ $member->given_name ?: '—' }}</td>
                        <td class="px-4 py-4 font-bold whitespace-nowrap {{ $isActive ? ($soon ? 'text-amber-600' : 'text-[#0B0B0E]') : ($isPartial ? 'text-amber-700' : 'text-red-600') }}">{{ optional($member->end_date)->format('M j, Y') ?? '—' }}</td>
                        <td class="px-4 py-4 font-mono font-bold whitespace-nowrap {{ $isActive ? ($soon ? 'text-amber-600' : 'text-emerald-700') : ($isPartial ? 'text-amber-700' : 'text-red-600') }}">{{ $member->days_remaining }} {{ $member->days_remaining === 1 ? 'day' : 'days' }}</td>
                        <td class="px-4 py-4">
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $isActive ? ($soon ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') : ($isPartial ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">{{ $isActive ? ($soon ? 'Expiring' : 'Active') : $member->membership_status }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-semibold text-neutral-800">{{ str_replace(' Membership', '', $member->plan_type) }}</div>
                            <div class="font-mono text-xs text-[#E31B23] font-semibold">ID {{ $member->member_id }}</div>
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
@endsection
