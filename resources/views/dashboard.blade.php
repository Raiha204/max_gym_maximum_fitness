@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<p class="text-sm text-gray-500 mb-5">Here's a quick look at how the gym is doing today.</p>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @php
        $cards = [
            ['label' => 'Members registered', 'value' => $stats['total_members'], 'hint' => 'in the system so far'],
            ['label' => 'Active memberships', 'value' => $stats['active_memberships'], 'hint' => 'fully paid and current'],
            ['label' => 'Awaiting full payment', 'value' => $stats['pending_memberships'], 'hint' => 'balance still due'],
            ['label' => 'Expired memberships', 'value' => $stats['expired_memberships'], 'hint' => 'past their end date'],
            ['label' => "Check-ins today", 'value' => $stats['today_attendance'], 'hint' => 'members who visited'],
            ['label' => "Sales today", 'value' => '₱'.number_format($stats['today_sales'], 2), 'hint' => 'from all payments'],
            ['label' => 'Equipment needing repair', 'value' => $stats['equipment_needing_repair'], 'hint' => 'flagged for maintenance'],
        ];
    @endphp
    @foreach ($cards as $card)
        <div class="bg-white border-t-4 border-red-600 rounded shadow p-5">
            <div class="text-2xl font-semibold text-gray-800">{{ $card['value'] }}</div>
            <div class="text-sm text-gray-700 mt-1">{{ $card['label'] }}</div>
            <div class="text-xs text-gray-400">{{ $card['hint'] }}</div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded shadow">
        <div class="px-5 py-3 border-b">
            <div class="font-semibold text-gray-800 text-sm">Recent Payments</div>
            <div class="text-xs text-gray-400">The last 5 payments received, most recent first.</div>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs">
                <tr>
                    <th class="text-left px-5 py-2 font-medium">Paid By</th>
                    <th class="text-left px-5 py-2 font-medium">Date</th>
                    <th class="text-right px-5 py-2 font-medium">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentPayments as $payment)
                    <tr class="border-t">
                        <td class="px-5 py-2">{{ $payment->label }}</td>
                        <td class="px-5 py-2 text-gray-500">{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                        <td class="px-5 py-2 text-right text-gray-800">₱{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded shadow">
        <div class="px-5 py-3 border-b">
            <div class="font-semibold text-gray-800 text-sm">Expiring in the Next 7 Days</div>
            <div class="text-xs text-gray-400">A gentle nudge to follow up on renewals before they lapse.</div>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs">
                <tr>
                    <th class="text-left px-5 py-2 font-medium">Member</th>
                    <th class="text-left px-5 py-2 font-medium">Ends On</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expiringSoon as $m)
                    <tr class="border-t">
                        <td class="px-5 py-2">{{ $m->fullName() }}</td>
                        <td class="px-5 py-2 text-gray-500">{{ \Carbon\Carbon::parse($m->end_date)->format('M d, Y') }}</td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
