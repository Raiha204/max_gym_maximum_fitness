@extends('layouts.app')

@section('title', 'Walk-In Daily Sessions — MAX GYM')

@section('content')
@php
    $totalFees = $walkIns->sum('amount');
    $cashFees = $walkIns->where('payment_method', 'Cash')->sum('amount');
    $gcashFees = $walkIns->where('payment_method', 'GCash')->sum('amount');
@endphp
<div class="space-y-4">
    <div class="bg-[#0B0B0E] text-white rounded-xl border-l-4 border-l-[#E31B24] p-4 sm:px-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-lg font-bold text-white">Walk-In Daily Sessions</h1>
            <p class="text-[11px] text-neutral-300 mt-0.5">Record single-session visits and reconcile front-desk payments.</p>
        </div>
        <span class="px-4 py-2.5 rounded-lg bg-[#252932] text-[11px] font-semibold">Total Sessions: {{ $walkIns->count() }}</span>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        @foreach([['Sessions logged', $walkIns->count(), 'All recorded walk-ins', 'border-t-[#E31B23]'], ['Total collected', '₱'.number_format($totalFees, 2), 'Cash + GCash', 'border-t-[#111111]'], ['Cash', '₱'.number_format($cashFees, 2), 'Collected at the desk', 'border-t-[#374151]'], ['GCash', '₱'.number_format($gcashFees, 2), 'Collected digitally', 'border-t-[#E31B23]']] as [$label, $value, $hint, $accent])
            <div class="bg-white rounded-xl border border-[#E5E7EB] border-t-2 {{ $accent }} p-3.5 shadow-sm">
                <div class="text-[11px] text-[#6B7280] font-semibold">{{ $label }}</div>
                <div class="text-xl font-mono font-bold mt-1">{{ $value }}</div>
                <div class="text-[10px] text-[#6B7280] mt-1">{{ $hint }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">
        <div class="lg:col-span-5 xl:col-span-4 bg-white rounded-xl border border-neutral-200 p-4 shadow-sm">
            <h2 class="text-[12px] font-bold text-[#0B0B0E] pb-3 mb-4 border-b border-neutral-200">Record Walk-In Session</h2>
            <form method="POST" action="{{ route('walk-ins.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="first_name" class="block text-xs font-semibold text-neutral-700 mb-1.5">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" maxlength="120" autocomplete="given-name" class="w-full text-sm rounded-lg border border-neutral-300">
                        @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="last_name" class="block text-xs font-semibold text-neutral-700 mb-1.5">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" maxlength="120" autocomplete="family-name" class="w-full text-sm rounded-lg border border-neutral-300">
                        @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Walk-In Session Type</label>
                    <input type="hidden" name="session_type" id="session_type" value="Regular Walk-In">
                    <div class="grid grid-cols-2 gap-3" role="group" aria-label="Walk-in session type">
                        @foreach(\App\Models\WalkIn::SESSION_FEES as $type => $fee)
                            <button type="button" data-session-type="{{ $type }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" onclick="selectWalkInType('{{ $type }}', {{ $fee }})" class="choice">
                                <span class="choice-title">{{ $type }}</span>
                                <span class="choice-sub">₱{{ number_format($fee, 2) }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Session Fee (₱)</label>
                    <input type="text" inputmode="numeric" id="walkin_amount" name="amount" value="65" readonly aria-label="Session fee, determined by session type" class="w-full text-sm rounded-lg border border-neutral-300 bg-neutral-50 font-mono text-neutral-700">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Payment Method (Cash or GCash Only)</label>
                    <input type="hidden" name="payment_method" id="walkin_payment_method" value="Cash">
                    <div class="grid grid-cols-2 gap-3" role="group" aria-label="Payment method">
                        <button type="button" data-payment-method="Cash" aria-pressed="true" onclick="selectWalkInPayment('Cash')" class="choice choice-sm"><span class="choice-title">Cash</span></button>
                        <button type="button" data-payment-method="GCash" aria-pressed="false" onclick="selectWalkInPayment('GCash')" class="choice choice-sm"><span class="choice-title">GCash</span></button>
                    </div>
                </div>
                <button type="submit" class="btn-main">+ Record Walk-In Session</button>
            </form>
        </div>

        <div class="lg:col-span-7 xl:col-span-8 bg-white rounded-xl border border-neutral-200 p-4 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-3 mb-3 border-b border-neutral-200">
                <div>
                    <h2 class="text-[12px] font-bold text-[#0B0B0E]">Walk-In Session Log</h2>
                    <p class="text-[10px] text-neutral-500 mt-0.5">Recorded daily walk-in passes ({{ $walkIns->count() }} entries)</p>
                </div>
                <div class="flex gap-2">
                    @foreach(['All', 'Cash', 'GCash'] as $filter)
                        <a href="{{ route('walk-ins.index', ['method' => $filter]) }}" class="px-4 py-2 text-[11px] font-semibold border rounded-lg {{ $method === $filter ? 'bg-[#E31B24] border-[#E31B24] text-white' : 'bg-white border-neutral-300 text-[#111111]' }}">{{ $filter }}</a>
                    @endforeach
                </div>
            </div>
            <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left border-collapse">
                <thead>
                    <tr class="border-b border-neutral-200 text-neutral-500">
                        <th class="py-3 px-3">ID</th>
                        <th class="py-3 px-3">First Name</th>
                        <th class="py-3 px-3">Last Name</th>
                        <th class="py-3 px-3">Session Type</th>
                        <th class="py-3 px-3">Payment Method</th>
                        <th class="py-3 px-3">Date & Time</th>
                        <th class="py-3 px-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach($walkIns as $walkIn)
                        <tr>
                            <td class="py-3.5 px-3 font-mono">{{ $walkIn->id }}</td>
                            <td class="py-3.5 px-3">{{ $walkIn->first_name ?: '—' }}</td>
                            <td class="py-3.5 px-3">{{ $walkIn->last_name ?: '—' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-[#0B0B0E]">{{ $walkIn->session_type }}</td>
                            <td class="py-3.5 px-3 text-neutral-600">{{ $walkIn->payment_method }}</td>
                            <td class="py-3.5 px-3 font-mono text-neutral-500">{{ $walkIn->paid_at?->copy()->setTimezone(config('app.display_timezone'))->format('Y-m-d g:i A') ?? '—' }}</td>
                            <td class="py-3.5 px-3 text-right font-mono font-bold">₱{{ number_format($walkIn->amount, 2) }}</td>
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
        document.querySelectorAll('[data-session-type]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.sessionType === type)));
    }

    function selectWalkInPayment(method) {
        document.getElementById('walkin_payment_method').value = method;
        document.querySelectorAll('[data-payment-method]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.paymentMethod === method)));
    }
</script>
@endsection
