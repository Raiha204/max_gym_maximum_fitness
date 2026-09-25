@extends('layouts.app')
@section('title', 'Members & Memberships')

@section('content')
<div class="flex justify-between items-center mb-5">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name..."
               class="border border-gray-300 rounded px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-red-600">
        <button class="bg-black text-white px-4 py-2 rounded text-sm">Search</button>
    </form>
    <a href="{{ route('memberships.create') }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-semibold">
        + Register Member
    </a>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-black text-white uppercase text-xs">
            <tr>
                <th class="text-left px-5 py-3">Name</th>
                <th class="text-left px-5 py-3">Member ID / Barcode</th>
                <th class="text-left px-5 py-3">Type</th>
                <th class="text-right px-5 py-3">Due</th>
                <th class="text-right px-5 py-3">Paid</th>
                <th class="text-right px-5 py-3">Balance</th>
                <th class="text-left px-5 py-3">Start / End</th>
                <th class="text-left px-5 py-3">Due By</th>
                <th class="text-left px-5 py-3">Status</th>
                <th class="text-right px-5 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($memberships as $m)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium">{{ $m->fullName() }}</td>
                    <td class="px-5 py-3 font-mono text-xs tracking-wide">{{ $m->member->member_number }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $m->member_type === 'regular' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                            {{ ucfirst($m->member_type) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">₱{{ number_format($m->amount_due, 2) }}</td>
                    <td class="px-5 py-3 text-right">₱{{ number_format($m->amount_paid, 2) }}</td>
                    <td class="px-5 py-3 text-right font-semibold {{ $m->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                        ₱{{ number_format($m->balance, 2) }}
                    </td>
                    <td class="px-5 py-3 text-xs">
                        {{ $m->start_date ? \Carbon\Carbon::parse($m->start_date)->format('M d, Y') : 'Not started' }}
                        —
                        {{ $m->end_date ? \Carbon\Carbon::parse($m->end_date)->format('M d, Y') : '—' }}
                    </td>
                    <td class="px-5 py-3">
                        @if ($m->balance > 0 && $m->payment_due_date)
                            <div class="text-xs {{ $m->is_past_due ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                {{ \Carbon\Carbon::parse($m->payment_due_date)->format('M d, Y') }}
                            </div>
                            @if ($m->penalty_applied)
                                <div class="text-xs text-red-500">₱{{ \App\Models\Membership::LATE_PENALTY }} penalty applied</div>
                            @endif
                        @else
                            <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @php
                            $badge = match($m->status) {
                                'active' => 'bg-green-100 text-green-700',
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'expired' => 'bg-gray-200 text-gray-600',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">{{ ucfirst($m->status) }}</span>
                    </td>
                    <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">
                        @if ($m->balance > 0)
                            <button onclick="document.getElementById('pay-{{ $m->id }}').classList.toggle('hidden')"
                                    class="text-red-600 font-semibold hover:underline">Add Payment</button>
                        @endif
                        <a href="{{ route('memberships.edit', $m) }}" class="text-black font-semibold hover:underline">Edit</a>
                        <form action="{{ route('memberships.destroy', $m) }}" method="POST" class="inline" onsubmit="return confirm('Delete this member?');">
                            @csrf @method('DELETE')
                            <button class="text-gray-500 hover:text-black font-semibold">Delete</button>
                        </form>
                    </td>
                </tr>
                @if ($m->balance > 0)
                    <tr id="pay-{{ $m->id }}" class="hidden bg-gray-50 border-t">
                        <td colspan="10" class="px-5 py-3">
                            <form method="POST" action="{{ route('memberships.pay', $m) }}" class="flex items-end gap-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Amount (max ₱{{ number_format($m->balance, 2) }})</label>
                                    <input type="number" step="0.01" min="0.01" max="{{ $m->balance }}" name="amount" required
                                           class="border border-gray-300 rounded px-3 py-1.5 text-sm w-40">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold mb-1">Method</label>
                                    <select name="payment_method" class="border border-gray-300 rounded px-3 py-1.5 text-sm">
                                        <option value="cash">Cash</option>
                                        <option value="gcash">GCash</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 rounded text-sm font-semibold">Record Payment</button>
                            </form>
                        </td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="10" class="px-5 py-6 text-center text-gray-400">No members registered yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $memberships->links() }}</div>
@endsection
