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
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by Member ID, Full Name, Email, or Phone..." class="flex-1 px-3.5 py-2 text-xs rounded-lg border border-neutral-300 focus:outline-none focus:border-[#E31E24]">
            <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#0B0B0E] rounded-lg">Search</button>
        </form>

        <div class="flex items-center gap-1 p-1 bg-neutral-100 rounded-lg">
            @foreach(['all' => 'All (' . $counts['all'] . ')', 'active' => 'Active (' . $counts['active'] . ')', 'expiring' => 'Expiring (' . $counts['expiring'] . ')', 'expired' => 'Expired (' . $counts['expired'] . ')'] as $key => $label)
                <a href="{{ route('memberships.index', ['status' => $key, 'search' => $search]) }}" class="px-3 py-1.5 text-xs font-semibold rounded-md {{ $statusFilter === $key ? 'bg-[#0B0B0E] text-white' : 'text-neutral-600 hover:text-[#0B0B0E]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Membership Cards Grid (NO BALANCE DISPLAY) -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($memberships as $member)
            <div class="bg-white rounded-xl border border-neutral-200 p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-start gap-3.5 pb-4 border-b border-neutral-200">
                        <div class="relative w-14 h-14 shrink-0">
                            @if($member->photo_url)
                                <img src="{{ $member->photo_url }}" alt="{{ $member->full_name }}" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden')" class="w-14 h-14 rounded-xl object-cover border border-neutral-200">
                            @endif
                            <div class="{{ $member->photo_url ? 'hidden' : '' }} absolute inset-0 rounded-xl bg-[#0B0B0E] text-white flex items-center justify-center font-bold text-sm border border-neutral-200">
                                {{ strtoupper(collect(explode(' ', $member->full_name))->filter()->take(2)->map(fn ($part) => $part[0])->implode('')) }}
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-base font-bold text-[#0B0B0E] truncate">{{ $member->full_name }}</h3>
                            <p class="text-xs font-mono font-semibold text-[#E31E24] mt-0.5">Member ID: {{ $member->member_id }}</p>
                            <p class="text-xs text-neutral-500 truncate mt-0.5">{{ $member->phone }} · {{ $member->email }}</p>
                        </div>
                    </div>

                    <!-- Prepaid Membership Information (Replaces Balance) -->
                    <div class="py-4 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-neutral-500">Membership Status:</span>
                            <span class="font-bold {{ $member->membership_status === 'Active' ? 'text-emerald-700' : 'text-red-600' }}">
                                {{ $member->membership_status }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-neutral-500">Membership Type:</span>
                            <span class="font-semibold text-[#0B0B0E]">{{ $member->plan_type }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-neutral-500">Expires On:</span>
                            <span class="font-semibold text-[#0B0B0E]">{{ optional($member->end_date)->format('F j, Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-neutral-500">Days Remaining:</span>
                            <span class="font-mono font-bold {{ $member->days_remaining > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                                {{ $member->days_remaining }} {{ $member->days_remaining === 1 ? 'Day' : 'Days' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-neutral-200 flex items-center gap-2">
                    <a href="{{ route('memberships.show', $member) }}" class="flex-1 text-center px-3 py-2 text-xs font-semibold text-white bg-[#0B0B0E] hover:bg-neutral-800 rounded-lg">View Card & Details</a>
                    <a href="{{ route('attendance.index', ['member_id' => $member->member_id]) }}" class="px-3 py-2 text-xs font-semibold text-neutral-700 bg-neutral-100 hover:bg-neutral-200 rounded-lg">Verify</a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl border border-neutral-200 p-10 text-center text-xs text-neutral-500">
                No memberships found.
            </div>
        @endforelse
    </div>
</div>
@endsection
