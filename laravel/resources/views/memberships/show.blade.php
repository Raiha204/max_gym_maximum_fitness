@extends('layouts.app')

@section('title', $membership->full_name . ' — Member Profile & QR Card')

@section('content')
<div class="space-y-4">
    @if(request()->boolean('registered'))
        <!-- Step 5 of 5 Progress Header when redirected right after Registration (No Print QR Code button) -->
        <div class="bg-[#111111] text-white rounded-xl border-l-4 border-[#E31B23] px-5 py-3 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-white">Register Member</h1>
                    <p class="text-xs font-medium text-[#D1D5DB] mt-0.5">STEP 5 OF 5 · QR Code & Digital Member Pass</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('memberships.index') }}" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">Finish ✓</a>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
        <!-- Left 7 Cols: Prepaid Membership Information (NO BALANCE DISPLAY) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-xl border border-[#E5E7EB] p-6">
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-[#E5E7EB]">
                    <div>
                        <h2 class="text-xl font-bold text-[#111111]">{{ $membership->surname }}@if($membership->given_name), {{ $membership->given_name }}@endif</h2>
                        <p class="text-xs font-mono font-bold text-[#E31B23]">Member ID: {{ $membership->member_id }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('memberships.edit', $membership) }}" class="px-3 py-1.5 text-xs font-semibold text-[#111111] bg-white border border-[#D1D5DB] rounded-lg">Edit Profile</a>
                        <a href="{{ route('memberships.index') }}" class="px-3 py-1.5 text-xs font-semibold text-neutral-700 bg-neutral-100 rounded-lg">← Back to List</a>
                    </div>
                </div>

                <table class="w-full text-xs border border-[#E5E7EB] rounded-lg overflow-hidden">
                    <tbody class="divide-y divide-[#E5E7EB]">
                        <tr><th class="w-2/5 text-left font-semibold text-[#6B7280] bg-[#F9FAFB] px-4 py-2.5">Surname</th><td class="px-4 py-2.5 font-bold text-[#111111]">{{ $membership->surname }}</td></tr>
                        <tr><th class="text-left font-semibold text-[#6B7280] bg-[#F9FAFB] px-4 py-2.5">First Name</th><td class="px-4 py-2.5 font-bold text-[#111111]">{{ $membership->given_name ?: '—' }}</td></tr>
                        <tr><th class="text-left font-semibold text-[#6B7280] bg-[#F9FAFB] px-4 py-2.5">Due Date (Expires On)</th><td class="px-4 py-2.5 font-bold {{ $membership->membership_status === 'Active' ? 'text-[#111111]' : 'text-[#B91C1C]' }}">{{ optional($membership->end_date)->format('F j, Y') }}</td></tr>
                        <tr><th class="text-left font-semibold text-[#6B7280] bg-[#F9FAFB] px-4 py-2.5">Days Remaining</th><td class="px-4 py-2.5 font-mono font-bold {{ $membership->days_remaining > 0 ? 'text-[#166534]' : 'text-[#B91C1C]' }}">{{ $membership->days_remaining }} {{ $membership->days_remaining === 1 ? 'Day' : 'Days' }}</td></tr>
                        <tr><th class="text-left font-semibold text-[#6B7280] bg-[#F9FAFB] px-4 py-2.5">Membership Status</th><td class="px-4 py-2.5 font-bold {{ $membership->membership_status === 'Active' ? 'text-[#166534]' : ($membership->membership_status === 'Partial' ? 'text-amber-700' : 'text-[#B91C1C]') }}">{{ $membership->membership_status }}</td></tr>
                        <tr><th class="text-left font-semibold text-[#6B7280] bg-[#F9FAFB] px-4 py-2.5">Membership Type</th><td class="px-4 py-2.5 font-semibold text-[#111111]">{{ $membership->plan_type }}</td></tr>
                        <tr><th class="text-left font-semibold text-[#6B7280] bg-[#F9FAFB] px-4 py-2.5">Start Date</th><td class="px-4 py-2.5 font-semibold text-[#111111]">{{ optional($membership->start_date)->format('F j, Y') }}</td></tr>
                        <tr><th class="text-left font-semibold text-[#6B7280] bg-[#F9FAFB] px-4 py-2.5">Date of Birth</th><td class="px-4 py-2.5 font-semibold text-[#111111]">{{ optional($membership->date_of_birth)->format('d/m/Y') ?? '—' }}</td></tr>
                    </tbody>
                </table>
                @if($membership->daily_credit_enabled)
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm">
                        <div class="flex justify-between gap-4"><span>Available Credit</span><strong>₱{{ number_format($membership->available_credit, 2) }}</strong></div>
                        <div class="mt-1 flex justify-between gap-4"><span>Unpaid Plan Balance</span><strong>₱{{ number_format($membership->remaining_amount, 2) }}</strong></div>
                        <p class="mt-2 text-xs text-amber-800">₱50 is deducted from available credit each calendar day, starting the day after the partial payment.</p>
                    </div>
                @endif
            </div>

            <!-- Renew Membership Card -->
            <div class="bg-white rounded-xl border border-[#E5E7EB] p-6">
                <h3 class="text-sm font-bold text-[#111111] pb-3 mb-4 border-b border-[#E5E7EB]">Renew Prepaid Membership</h3>
                <form id="renew-membership-form" method="POST" action="{{ route('memberships.renew', $membership) }}" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 items-end">
                    @csrf
                    <div class="min-w-0">
                        <label class="block text-xs font-semibold text-[#111111] mb-1">Plan</label>
                        <select name="plan_type" class="w-full px-3 py-2 text-xs rounded-lg border border-[#D1D5DB] bg-white">
                            <option value="Student Membership" @selected($membership->plan_type === 'Student Membership')>Student (₱650/mo)</option>
                            <option value="Regular Membership" @selected($membership->plan_type === 'Regular Membership')>Regular (₱750/mo)</option>
                        </select>
                    </div>
                    <div class="min-w-0">
                        <label class="block text-xs font-semibold text-[#111111] mb-1">Duration</label>
                        <select name="duration_months" class="w-full px-3 py-2 text-xs rounded-lg border border-[#D1D5DB] bg-white">
                            @foreach([1, 2, 3, 6, 12] as $m)
                                <option value="{{ $m }}">{{ $m }} {{ $m === 1 ? 'Month' : 'Months' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-0">
                        <label class="block text-xs font-semibold text-[#111111] mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full px-3 py-2 text-xs rounded-lg border border-[#D1D5DB] bg-white">
                            <option value="Cash">Cash</option>
                            <option value="GCash">GCash</option>
                        </select>
                    </div>
                    <div class="min-w-0">
                        <label for="renew-amount-paid" class="block text-xs font-semibold text-[#111111] mb-1">Amount Paid</label>
                        <input id="renew-amount-paid" type="number" name="amount_paid" value="{{ old('amount_paid', $membership->monthly_rate) }}" min="0.01" max="{{ $membership->monthly_rate }}" step="0.01" required class="w-full px-3 py-2 text-xs rounded-lg border border-[#D1D5DB]">
                    </div>
                    <div>
                        <button type="submit" class="w-full whitespace-nowrap px-2 py-2 text-[11px] font-semibold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">Renew Membership</button>
                    </div>
                </form>
                <p class="mt-2 text-xs text-[#6B7280]">Enter less than the plan total to start daily ₱50 credit deductions the next calendar day.</p>
            </div>

            @if($membership->daily_credit_enabled && ($membership->remaining_amount > 0 || $membership->available_credit <= 0))
                <div class="bg-white rounded-xl border border-[#E5E7EB] p-6">
                    <h3 class="text-sm font-bold text-[#111111] pb-3 mb-4 border-b border-[#E5E7EB]">Record Additional Payment</h3>
                    <form method="POST" action="{{ route('memberships.payments.store', $membership) }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                        @csrf
                        <div>
                            <label for="additional-amount-paid" class="block text-xs font-semibold text-[#111111] mb-1">Amount Paid</label>
                            <input id="additional-amount-paid" type="number" name="amount_paid" min="0.01" step="0.01" required class="w-full px-3 py-2 text-xs rounded-lg border border-[#D1D5DB]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#111111] mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full px-3 py-2 text-xs rounded-lg border border-[#D1D5DB] bg-white">
                                <option value="Cash">Cash</option>
                                <option value="GCash">GCash</option>
                            </select>
                        </div>
                        <button type="submit" class="px-2 py-2 text-[11px] font-semibold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">Record Payment</button>
                    </form>
                    <p class="mt-2 text-xs text-[#6B7280]">This payment adds to available credit and reduces the unpaid plan balance. Daily deductions continue.</p>
                </div>
            @endif
        </div>

        <!-- Right 5 Cols: Digital QR Code Membership Card & Offline QR Profile -->
        <div class="lg:col-span-5 bg-white rounded-xl border border-[#E5E7EB] p-6 flex flex-col items-center">
            <div class="printable-qr-card w-full max-w-[350px] bg-white rounded-xl border-2 border-[#111111] overflow-hidden shadow-sm">
                <div class="bg-[#111111] text-white px-5 py-3.5 border-b-4 border-[#E31B23] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <svg viewBox="0 0 200 200" class="w-9 h-9 rounded-xl bg-black border border-neutral-800 shrink-0">
                            <rect width="200" height="200" fill="#050507" />
                            <polygon points="100,14 172,55 172,145 100,186 28,145 28,55" fill="#E31B23" stroke="#111111" stroke-width="4" />
                            <path d="M52,96 Q100,82 148,96" stroke="#111111" stroke-width="6" fill="none" />
                            <polygon points="14,116 186,116 174,135 186,154 14,154 26,135" fill="#FFFFFF" />
                            <text x="100" y="134" text-anchor="middle" fill="#111111" font-size="19" font-weight="900" font-family="sans-serif">MAX GYM</text>
                        </svg>
                        <div>
                            <span class="font-extrabold tracking-wider text-base block leading-none">MAX GYM</span>
                            <span class="text-[9px] font-bold tracking-widest text-[#E31B23] uppercase block mt-0.5">MAXIMUM FITNESS</span>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono text-neutral-300">MEMBER PASS</span>
                </div>
                <div class="p-5 flex flex-col items-center">
                    <div class="flex items-center gap-4 w-full">
                        <div class="relative w-16 h-16 shrink-0">
                            @if($membership->photo_url)
                                <img src="{{ $membership->photo_url }}" alt="{{ $membership->full_name }}" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden')" class="w-16 h-16 rounded-xl object-cover border-2 border-[#E31B23]">
                            @endif
                            <div class="{{ $membership->photo_url ? 'hidden' : '' }} absolute inset-0 rounded-xl bg-[#111111] text-white flex items-center justify-center font-bold text-lg border-2 border-[#E31B23]">
                                {{ $membership->initials }}
                            </div>
                        </div>
                        <div class="min-w-0 text-left">
                            <h3 class="text-base font-bold text-[#111111] leading-tight truncate">{{ $membership->surname }}@if($membership->given_name), {{ $membership->given_name }}@endif</h3>
                            <p class="text-xs font-mono font-semibold text-[#E31B23]">ID: {{ $membership->member_id }}</p>
                            <p class="text-[11px] font-semibold text-neutral-700">{{ $membership->plan_type }} · Due {{ optional($membership->end_date)->format('M j, Y') }}</p>
                        </div>
                    </div>

                    <div id="member-qrcode" class="mt-5 p-3 bg-neutral-50 border border-neutral-200 rounded-lg"></div>
                </div>
                <div class="bg-[#111111] px-4 py-3 text-center">
                    <p class="text-[11px] text-[#D1D5DB] font-medium">Present QR code at MAX GYM entrance for attendance check-in</p>
                </div>
            </div>

            <!-- Offline Member QR Profile Display -->
            <div class="mt-5 w-full max-w-[350px] bg-[#111111] text-neutral-200 rounded-xl px-5 py-4 font-mono text-[11px] leading-6">
                <div class="text-[#E31B23] font-bold">MAX GYM MEMBER</div>
                <div class="text-white font-semibold">{{ $membership->surname }}@if($membership->given_name), {{ $membership->given_name }}@endif</div>
                <div>Due Date: {{ optional($membership->end_date)->format('F j, Y') }}</div>
                <div>Member ID: {{ $membership->member_id }} · {{ $membership->membership_status }}</div>
                <div>Membership: {{ $membership->plan_type }}</div>
            </div>
        </div>
    </div>
</div>

<script>
    new QRCode(document.getElementById('member-qrcode'), {
        text: @json($membership->offline_qr_text),
        width: 144,
        height: 144,
        colorDark: '#0B0B0E',
        colorLight: '#FFFFFF',
        correctLevel: QRCode.CorrectLevel.M
    });
</script>
<script>
    const renewalForm = document.getElementById('renew-membership-form');
    const renewalAmountInput = document.getElementById('renew-amount-paid');
    let renewalAmountEdited = @json(old('amount_paid') !== null);

    renewalAmountInput.addEventListener('input', () => {
        renewalAmountEdited = true;
    });

    function updateRenewalAmountLimit() {
        const plan = renewalForm.elements.plan_type.value;
        const duration = Number(renewalForm.elements.duration_months.value);
        const monthlyRate = plan === 'Student Membership' ? 650 : 750;
        const total = monthlyRate * duration;
        renewalAmountInput.max = total.toFixed(2);
        if (!renewalAmountEdited || Number(renewalAmountInput.value) > total) {
            renewalAmountInput.value = total.toFixed(2);
        }
    }

    renewalForm.elements.plan_type.addEventListener('change', updateRenewalAmountLimit);
    renewalForm.elements.duration_months.addEventListener('change', updateRenewalAmountLimit);
    updateRenewalAmountLimit();
</script>
@endsection
