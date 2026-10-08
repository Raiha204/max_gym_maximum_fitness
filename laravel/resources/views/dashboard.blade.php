@extends('layouts.app')

@section('title', 'Dashboard — MAX GYM')

@section('content')
<div class="space-y-4">
    <section class="bg-[#111111] text-white rounded-xl border-l-4 border-[#E31B23] p-4 sm:px-5 flex flex-col xl:flex-row xl:items-center justify-between gap-4 shadow-sm">
        <div>
            <h1 class="text-lg font-bold text-white">MAX GYM Operations Dashboard</h1>
            <p class="text-[11px] text-[#D1D5DB] mt-0.5">Membership health, attendance, and revenue at a glance.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('attendance.index') }}" class="px-3 py-2 text-[11px] font-semibold text-[#111111] bg-white border border-[#D1D5DB] hover:bg-[#F3F4F6] rounded-lg">Open QR Scanner</a>
            <a href="{{ route('walk-ins.index') }}" class="px-3 py-2 text-[11px] font-semibold text-[#111111] bg-white border border-[#D1D5DB] hover:bg-[#F3F4F6] rounded-lg">Record Walk-In</a>
            <a href="{{ route('memberships.create') }}" class="px-3 py-2 text-[11px] font-semibold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">+ Register Member</a>
        </div>
    </section>

    <section class="grid grid-cols-2 xl:grid-cols-6 gap-3" aria-label="Gym overview">
        <div class="bg-white rounded-xl border border-[#E5E7EB] border-t-2 border-t-[#E31B23] p-3.5 shadow-sm">
            <div class="text-[11px] text-[#6B7280] font-semibold">Active Memberships</div>
            <div class="text-xl font-mono font-bold text-[#111111] mt-1">{{ $activeCount }}</div>
            <div class="text-[10px] text-[#6B7280] mt-1">Prepaid & valid</div>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] border-t-2 border-t-amber-500 p-3.5 shadow-sm">
            <div class="text-[11px] text-[#6B7280] font-semibold">Partial Memberships</div>
            <div class="text-xl font-mono font-bold text-[#111111] mt-1">{{ $partialCount }}</div>
            <div class="text-[10px] text-[#6B7280] mt-1">Credit decreases by ₱50 daily</div>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] border-t-2 border-t-[#F59E0B] p-3.5 shadow-sm">
            <div class="text-[11px] text-[#6B7280] font-semibold">Expiring Soon</div>
            <div class="text-xl font-mono font-bold text-[#111111] mt-1">{{ $expiringCount }}</div>
            <div class="text-[10px] text-[#6B7280] mt-1">Renewal due within 7 days</div>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] border-t-2 border-t-[#E31B23] p-3.5 shadow-sm">
            <div class="text-[11px] text-[#6B7280] font-semibold">Expired Memberships</div>
            <div class="text-xl font-mono font-bold text-[#111111] mt-1">{{ $expiredCount }}</div>
            <div class="text-[10px] text-[#6B7280] mt-1">Renewal required</div>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] border-t-2 border-t-[#374151] p-3.5 shadow-sm">
            <div class="text-[11px] text-[#6B7280] font-semibold">Walk-In Sessions</div>
            <div class="text-xl font-mono font-bold text-[#111111] mt-1">{{ $walkInCount }}</div>
            <div class="text-[10px] text-[#6B7280] mt-1">₱{{ number_format($walkInRevenue, 2) }} total revenue</div>
        </div>
        <div class="bg-white rounded-xl border border-[#E5E7EB] border-t-2 border-t-[#E31B23] p-3.5 shadow-sm col-span-2 xl:col-span-1">
            <div class="text-[11px] text-[#6B7280] font-semibold">Membership Payments</div>
            <div class="text-xl font-mono font-bold text-[#111111] mt-1">₱{{ number_format($membershipRevenue, 2) }}</div>
            <div class="text-[10px] text-[#6B7280] mt-1">All recorded payments</div>
        </div>
    </section>

    <section class="bg-white rounded-xl border border-[#E1E4E8] p-4 sm:p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4 pb-3 border-b border-[#E5E7EB]">
            <div>
                <h2 class="text-sm font-bold text-[#111111]">Total Revenue Summary</h2>
                <p class="text-[10px] text-[#6B7280] mt-0.5">Membership payments and walk-in sessions across Cash and GCash.</p>
            </div>
            <a href="{{ route('reports.index') }}" class="shrink-0 px-3 py-2 text-[10px] font-bold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">Open Revenue Reports</a>
        </div>

        <div class="mt-3 p-3 rounded-lg border border-[#E1E4E8] bg-[#F9FAFB]">
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-[#E1E4E8]">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 rounded bg-[#E31B23] text-white text-[9px] font-bold uppercase">Specific date lookup</span>
                    <span class="text-[11px] font-semibold">Revenue for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}</span>
                </div>
                <label class="flex items-center gap-2 text-[10px] font-semibold">Select Date:
                    <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()" class="px-2.5 py-1.5 text-[10px] font-mono rounded-lg border border-[#D1D5DB] bg-white">
                </label>
            </form>
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-2.5 pt-3">
                <div class="p-3 rounded-lg bg-white border-l-2 border-[#E31B23] border-y border-r border-[#E5E7EB]">
                    <div class="text-[10px] text-[#6B7280]">Total Revenue</div>
                    <div class="text-base font-mono font-bold text-[#E31B23] mt-1">₱{{ number_format($dateMembershipRevenue + $dateWalkInRevenue, 2) }}</div>
                    <div class="text-[9px] text-[#6B7280] mt-1">{{ $datePayments->count() + $dateWalkIns->count() }} transactions</div>
                </div>
                <div class="p-3 rounded-lg bg-white border border-[#E5E7EB]">
                    <div class="text-[10px] text-[#6B7280]">Membership Payments</div>
                    <div class="text-base font-mono font-bold mt-1">₱{{ number_format($dateMembershipRevenue, 2) }}</div>
                    <div class="text-[9px] text-[#6B7280] mt-1">{{ $datePayments->count() }} payments</div>
                </div>
                <div class="p-3 rounded-lg bg-white border border-[#E5E7EB]">
                    <div class="text-[10px] text-[#6B7280]">Walk-In Revenue</div>
                    <div class="text-base font-mono font-bold mt-1">₱{{ number_format($dateWalkInRevenue, 2) }}</div>
                    <div class="text-[9px] text-[#6B7280] mt-1">{{ $dateWalkIns->count() }} sessions</div>
                </div>
                <div class="p-3 rounded-lg bg-white border border-[#E5E7EB]">
                    <div class="text-[10px] text-[#6B7280]">Cash vs. GCash</div>
                    <div class="text-[10px] font-mono font-semibold mt-1">Cash: ₱{{ number_format($dateCashRevenue, 2) }}</div>
                    <div class="text-[10px] font-mono font-semibold mt-1">GCash: ₱{{ number_format($dateGcashRevenue, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3">
            @foreach([['Today', $todayRevenue, today()->format('F j, Y'), 'border-[#E31B23]'], ['This Week', $weekRevenue, 'Last 7 days', 'border-[#374151]'], ['This Month', $monthRevenue, now()->format('F Y'), 'border-[#E31B23]']] as [$label, $amount, $period, $border])
                <div class="p-3 rounded-lg border border-[#E5E7EB] border-t-2 {{ $border }} bg-[#F9FAFB]">
                    <div class="flex justify-between gap-2 text-[10px]"><span class="font-bold">{{ $label }}'s Revenue</span><span class="text-[#6B7280]">{{ $period }}</span></div>
                    <div class="text-lg font-mono font-bold mt-1">₱{{ number_format($amount, 2) }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="grid grid-cols-1 xl:grid-cols-5 gap-4">
        <div class="xl:col-span-3 bg-white rounded-xl border border-[#E1E4E8] p-4 shadow-sm">
            <div class="flex justify-between items-center pb-3 border-b border-[#E5E7EB]">
                <h2 class="text-sm font-bold">Membership Validity Overview</h2>
                <a href="{{ route('memberships.index') }}" class="text-[10px] font-semibold text-[#E31B23]">View all members</a>
            </div>
            <div class="overflow-x-auto mt-2">
                <table class="w-full min-w-[620px] text-left text-[10px]">
                    <thead><tr class="bg-[#111111] text-white"><th class="px-3 py-2">Member</th><th class="px-3 py-2">Membership Type</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Expires On</th><th class="px-3 py-2 text-right">Days Remaining</th></tr></thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse($memberships->take(8) as $member)
                            <tr>
                                <td class="px-3 py-2.5"><div class="font-semibold">{{ $member->full_name }}</div><div class="font-mono text-[#E31B23] mt-0.5">ID: {{ $member->member_id }}</div></td>
                                <td class="px-3 py-2.5 text-[#6B7280]">{{ $member->plan_type }}</td>
                                <td class="px-3 py-2.5"><span class="px-2 py-1 rounded {{ $member->membership_status === 'Active' ? 'bg-emerald-100 text-emerald-800' : ($member->membership_status === 'Partial' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-700') }} font-bold">{{ $member->membership_status }}</span></td>
                                <td class="px-3 py-2.5 text-[#6B7280]">{{ optional($member->end_date)->format('F j, Y') }}</td>
                                <td class="px-3 py-2.5 text-right font-mono font-semibold">{{ $member->days_remaining }} days</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-8 text-center text-[#6B7280]">No memberships have been registered.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="xl:col-span-2 bg-white rounded-xl border border-[#E1E4E8] p-4 shadow-sm">
            <div class="flex justify-between items-center pb-3 border-b border-[#E5E7EB]">
                <h2 class="text-sm font-bold">Latest Verified Check-Ins</h2>
                <a href="{{ route('attendance.index') }}" class="text-[10px] font-semibold text-[#E31B23]">Open scanner</a>
            </div>
            <div class="divide-y divide-[#E5E7EB]">
                @forelse($todayAttendances->take(6) as $attendance)
                    <div class="flex items-center justify-between gap-3 py-3">
                        <div class="min-w-0">
                            <div class="text-[11px] font-semibold truncate">{{ optional($attendance->membership)->full_name ?? 'Member' }}</div>
                            <div class="text-[9px] font-mono text-[#6B7280] mt-0.5">ID: {{ optional($attendance->membership)->member_id ?? '—' }} · {{ $attendance->verification_method }}</div>
                        </div>
                        <div class="shrink-0 text-right">
                            <div class="text-[10px] font-mono">{{ optional($attendance->checked_in_at)->format('g:i:s A') }}</div>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[9px] font-bold">Verified</span>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-[10px] text-[#6B7280]">No check-ins recorded today.</p>
                @endforelse
            </div>
        </div>
    </section>

    <div class="flex items-center justify-between rounded-lg border border-[#E1E4E8] bg-white px-4 py-3 text-[10px] text-[#6B7280]">
        <span>Equipment ready for use</span>
        <span class="font-mono font-semibold text-[#111111]">{{ $operationalEquipment }} / {{ $equipmentCount }} operational</span>
    </div>
</div>
@endsection
