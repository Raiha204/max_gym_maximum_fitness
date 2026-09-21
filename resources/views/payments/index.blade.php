@extends('layouts.app')
@section('title', 'Walk-in Payments')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white rounded shadow p-5 lg:col-span-1 h-fit">
        <h2 class="font-bold mb-3 text-sm uppercase text-gray-600">Log a Walk-in Payment</h2>
        <form method="POST" action="{{ route('payments.store') }}" class="space-y-3">
            @csrf
            <label class="border border-gray-300 rounded px-4 py-3 flex items-center gap-2 cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50">
                <input type="radio" name="visitor_type" value="regular" checked class="accent-red-600">
                <span class="text-sm">Regular — <span class="font-semibold">₱65</span></span>
            </label>
            <label class="border border-gray-300 rounded px-4 py-3 flex items-center gap-2 cursor-pointer has-[:checked]:border-red-600 has-[:checked]:bg-red-50">
                <input type="radio" name="visitor_type" value="student" class="accent-red-600">
                <span class="text-sm">Student — <span class="font-semibold">₱50</span></span>
            </label>
            <select name="payment_method" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="cash">Cash</option>
                <option value="gcash">GCash</option>
                <option value="other">Other</option>
            </select>
            <button class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded font-semibold text-sm">Log Payment</button>
        </form>
    </div>

    <div class="lg:col-span-2">
        <div class="flex justify-between items-center mb-4">
            <form method="GET">
                <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()"
                       class="border border-gray-300 rounded px-3 py-2 text-sm">
            </form>
            <div class="text-sm font-semibold">Total shown: <span class="text-red-600">₱{{ number_format($totalForDay, 2) }}</span></div>
        </div>
        <div class="bg-white rounded shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-black text-white uppercase text-xs">
                    <tr>
                        <th class="text-left px-5 py-3">Payment</th>
                        <th class="text-left px-5 py-3">Date</th>
                        <th class="text-left px-5 py-3">Method</th>
                        <th class="text-right px-5 py-3">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $p)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-5 py-3 font-medium">{{ $p->label }}</td>
                            <td class="px-5 py-3">{{ \Carbon\Carbon::parse($p->payment_date)->format('M d, Y') }}</td>
                            <td class="px-5 py-3 uppercase text-xs">{{ $p->payment_method }}</td>
                            <td class="px-5 py-3 text-right font-semibold">₱{{ number_format($p->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-gray-400">No payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $payments->links() }}</div>
    </div>
</div>
@endsection
