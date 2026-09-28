@extends('layouts.app')

@section('title', 'Walk-In Daily Sessions — MAX GYM')

@section('content')
@php
    $cashTotal = $walkIns->where('payment_method', 'Cash')->sum('amount');
    $gcashTotal = $walkIns->where('payment_method', 'GCash')->sum('amount');
    $walkInTotal = $cashTotal + $gcashTotal;
@endphp
<div class="space-y-4">
    <div class="bg-[#0B0B0E] text-white rounded-xl border-l-4 border-l-[#E31B24] p-4 sm:px-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-white">Walk-In Daily Sessions</h1>
            <p class="text-[10px] text-neutral-300 mt-0.5">Record single-session visits and reconcile front-desk payments.</p>
        </div>
        <div class="flex gap-2">
            <span class="px-3 py-2 rounded-lg bg-[#252932] text-[10px] font-semibold">Total Sessions: {{ $walkIns->count() }}</span>
            <span class="px-3 py-2 rounded-lg bg-[#E31B24] text-[10px] font-bold">Walk-In Revenue: ₱{{ number_format($walkInTotal, 2) }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @foreach([['Cash Total', $cashTotal, 'border-[#374151]'], ['GCash Total', $gcashTotal, 'border-[#374151]'], ['Combined Revenue', $walkInTotal, 'border-[#E31B24]']] as [$label, $amount, $border])
            <div class="bg-white rounded-xl border border-neutral-200 border-t-2 {{ $border }} p-3.5 shadow-sm">
                <div class="text-[10px] text-neutral-500 font-semibold">{{ $label }}</div>
                <div class="text-lg font-mono font-bold mt-1">₱{{ number_format($amount, 2) }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
        <div class="lg:col-span-4 bg-white rounded-xl border border-neutral-200 p-4 shadow-sm">
            <h2 class="text-[12px] font-bold text-[#0B0B0E] pb-3 mb-3 border-b border-neutral-200">Record Walk-In Session</h2>
            <form method="POST" action="{{ route('walk-ins.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Walk-In Session Type</label>
                    <input type="hidden" name="session_type" id="session_type" value="Regular Walk-In">
                    <div class="grid grid-cols-2 gap-2" role="group" aria-label="Walk-in session type">
                        @foreach(['Regular Walk-In' => 65, 'Student Walk-In' => 50] as $type => $fee)
                            <button type="button" data-session-type="{{ $type }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" onclick="selectWalkInType('{{ $type }}', {{ $fee }})" class="walkin-type-option min-h-[58px] rounded-lg border px-3 py-2 text-left {{ $loop->first ? 'border-[#E31B23] bg-[#E31B23] text-white' : 'border-neutral-300 bg-white text-[#111111]' }}">
                                <span class="block text-[11px] font-semibold">{{ $type }}</span>
                                <span class="block text-[10px] font-mono {{ $loop->first ? 'text-white' : 'text-[#E31B23]' }}">₱{{ number_format($fee, 2) }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Session Fee (₱)</label>
                    <input type="text" inputmode="numeric" id="walkin_amount" name="amount" value="65" readonly aria-label="Session fee, determined by session type" class="w-full px-3.5 py-2 text-sm rounded-lg border border-neutral-300 bg-neutral-50 font-mono text-neutral-700">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Payment Method (Cash or GCash Only)</label>
                    <input type="hidden" name="payment_method" id="walkin_payment_method" value="Cash">
                    <div class="grid grid-cols-2 gap-2" role="group" aria-label="Payment method">
                        <button type="button" data-payment-method="Cash" aria-pressed="true" onclick="selectWalkInPayment('Cash')" class="walkin-payment-option rounded-lg border border-[#E31B23] bg-[#E31B23] px-3 py-2 text-[11px] font-semibold text-white">Cash</button>
                        <button type="button" data-payment-method="GCash" aria-pressed="false" onclick="selectWalkInPayment('GCash')" class="walkin-payment-option rounded-lg border border-neutral-300 bg-white px-3 py-2 text-[11px] font-semibold text-[#111111]">GCash</button>
                    </div>
                </div>
                <button type="submit" class="w-full py-2.5 text-xs font-semibold text-white bg-[#E31E24] hover:bg-[#c8191f] rounded-lg">+ Record Walk-In Session</button>
            </form>
        </div>

        <div class="lg:col-span-8 bg-white rounded-xl border border-neutral-200 p-4 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 pb-3 mb-3 border-b border-neutral-200">
                <div>
                    <h2 class="text-[12px] font-bold text-[#0B0B0E]">Walk-In Session Log</h2>
                    <p class="text-[9px] text-neutral-500 mt-0.5">Recorded daily walk-in passes ({{ $walkIns->count() }} entries)</p>
                </div>
                <div class="flex gap-1">
                    @foreach(['All', 'Cash', 'GCash'] as $filter)
                        <a href="{{ route('walk-ins.index', ['method' => $filter]) }}" class="px-2.5 py-1.5 text-[9px] font-semibold border rounded-lg {{ $method === $filter ? 'bg-[#E31B24] border-[#E31B24] text-white' : 'bg-white border-neutral-300 text-[#111111]' }}">{{ $filter }}</a>
                    @endforeach
                </div>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full min-w-[520px] text-left border-collapse text-[10px]">
                <thead>
                    <tr class="border-b border-neutral-200 text-neutral-500">
                        <th class="py-2.5 px-2">Session Type</th>
                        <th class="py-2.5 px-2">Payment Method</th>
                        <th class="py-2.5 px-2">Date & Time</th>
                        <th class="py-2.5 px-2 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach($walkIns as $walkIn)
                        <tr>
                            <td class="py-2.5 px-2 font-semibold text-[#0B0B0E]">{{ $walkIn->session_type }}</td>
                            <td class="py-2.5 px-2 text-neutral-600">{{ $walkIn->payment_method }}</td>
                            <td class="py-2.5 px-2 font-mono text-neutral-500">{{ optional($walkIn->paid_at)->format('Y-m-d g:i A') }}</td>
                            <td class="py-2.5 px-2 text-right font-mono font-bold">₱{{ number_format($walkIn->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
<script>
    function selectWalkInType(type, fee) {
        document.getElementById('session_type').value = type;
        document.getElementById('walkin_amount').value = fee;

        document.querySelectorAll('.walkin-type-option').forEach((button) => {
            const selected = button.dataset.sessionType === type;
            button.className = selected
                ? 'walkin-type-option min-h-[58px] rounded-lg border border-[#E31B23] bg-[#E31B23] px-3 py-2 text-left text-white'
                : 'walkin-type-option min-h-[58px] rounded-lg border border-neutral-300 bg-white px-3 py-2 text-left text-[#111111]';
            button.setAttribute('aria-pressed', String(selected));
            button.querySelector('span:last-child').className = selected
                ? 'block text-[10px] font-mono text-white'
                : 'block text-[10px] font-mono text-[#E31B23]';
        });
    }

    function selectWalkInPayment(method) {
        document.getElementById('walkin_payment_method').value = method;

        document.querySelectorAll('.walkin-payment-option').forEach((button) => {
            const selected = button.dataset.paymentMethod === method;
            button.className = selected
                ? 'walkin-payment-option rounded-lg border border-[#E31B23] bg-[#E31B23] px-3 py-2 text-[11px] font-semibold text-white'
                : 'walkin-payment-option rounded-lg border border-neutral-300 bg-white px-3 py-2 text-[11px] font-semibold text-[#111111]';
            button.setAttribute('aria-pressed', String(selected));
        });
    }
</script>
@endsection
