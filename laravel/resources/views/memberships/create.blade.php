@extends('layouts.app')

@section('title', 'Register Member — MAX GYM')

@section('content')
<div class="w-full max-w-4xl mx-auto py-2">
    <!-- Single Page Header & 5-Step Progress Bar (No Duplication) -->
    <div class="bg-white rounded-xl border border-neutral-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-5 border-b border-neutral-200">
            <div>
                <h1 class="text-2xl font-bold text-[#0B0B0E] tracking-tight">Register Member</h1>
                <p id="step-subtitle" class="text-xs font-medium text-neutral-500 mt-1">STEP 1 OF 5 · Member Information</p>
            </div>
            <div class="text-xs font-mono text-neutral-500">Prepaid Membership Enrollment</div>
        </div>

        <!-- 5-Step Progress Bar -->
        <div class="mt-5 grid grid-cols-5 gap-2 sm:gap-3">
            @foreach([1 => 'Info', 2 => 'Plan', 3 => 'Summary', 4 => 'Confirm', 5 => 'QR Code'] as $num => $label)
                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <div id="badge-step-{{ $num }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 {{ $num === 1 ? 'bg-[#E31E24] text-white' : 'bg-neutral-100 text-neutral-400 border border-neutral-200' }}">
                            {{ $num }}
                        </div>
                        <span id="label-step-{{ $num }}" class="text-xs font-semibold truncate {{ $num === 1 ? 'text-[#E31E24]' : 'text-neutral-400' }}">
                            {{ $num }}. {{ $label }}
                        </span>
                    </div>
                    <div id="bar-step-{{ $num }}" class="mt-2 h-1.5 w-full rounded-full {{ $num === 1 ? 'bg-[#E31E24]' : 'bg-neutral-200' }}"></div>
                </div>
            @endforeach
        </div>
    </div>

    @if($errors->any())
        <div role="alert" class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700">
            <p class="font-bold">Registration could not be completed.</p>
            <ul class="mt-1 list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div id="wizard-error" role="alert" class="hidden mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-xs font-semibold text-red-700"></div>

    <form id="register-member-form" method="POST" action="{{ route('memberships.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- STEP 1: Member Information ONLY -->
        <div id="step-panel-1" class="bg-white rounded-xl border border-neutral-200 p-6">
            <div class="pb-4 mb-6 border-b border-neutral-200">
                <h2 class="text-lg font-bold text-[#0B0B0E]">Member Information</h2>
                <p class="text-xs text-neutral-500 mt-0.5">Enter personal details and upload a profile photo.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-neutral-300 focus:outline-none focus:border-[#E31E24]" placeholder="e.g. Carl Adrian G. Gilbuena">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Email Address *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-neutral-300 focus:outline-none focus:border-[#E31E24]" placeholder="e.g. member@maxgym.ph">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Phone Number *</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-neutral-300 focus:outline-none focus:border-[#E31E24]" placeholder="e.g. 0917-482-9910">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Gender *</label>
                    <select id="gender" name="gender" class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-neutral-300 bg-white">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Date of Birth *</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', '2005-01-15') }}" required class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-neutral-300">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-neutral-700 mb-1.5">Profile Photo</label>
                    <input type="file" id="photo" name="photo" accept="image/*" onchange="previewMemberPhoto(this)" class="w-full text-xs text-neutral-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#0B0B0E] file:text-white hover:file:bg-neutral-800">
                    <div id="photo-preview-empty" class="mt-3 flex items-center gap-3 rounded-lg border border-dashed border-neutral-300 bg-neutral-50 px-3 py-2.5">
                        <div class="w-10 h-10 rounded-lg bg-neutral-200 flex items-center justify-center text-neutral-500 text-lg" aria-hidden="true">+</div>
                        <p class="text-[11px] text-neutral-500">Choose a photo to preview it on the member card.</p>
                    </div>
                    <div id="photo-preview" class="hidden mt-3 items-center gap-3 rounded-lg border border-neutral-200 bg-neutral-50 p-2.5" aria-live="polite">
                        <img id="photo-preview-image" src="" alt="Selected member profile photo preview" class="w-14 h-14 rounded-lg object-cover border border-neutral-200">
                        <div class="min-w-0 flex-1">
                            <p id="photo-preview-name" class="truncate text-[11px] font-semibold text-neutral-800"></p>
                            <button type="button" onclick="clearMemberPhoto()" class="mt-1 text-[10px] font-semibold text-[#B51219] hover:underline">Remove photo</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-5 border-t border-neutral-200 flex items-center justify-between">
                <a href="{{ route('memberships.index') }}" class="px-4 py-2.5 text-xs font-semibold text-neutral-700 bg-white border border-neutral-300 rounded-lg hover:bg-neutral-50">Cancel</a>
                <button type="button" onclick="goToStep(2)" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#E31E24] hover:bg-[#c8191f] rounded-lg">Continue to Plan →</button>
            </div>
        </div>

        <!-- STEP 2: Membership Plan ONLY -->
        <div id="step-panel-2" class="hidden grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2 bg-white rounded-xl border border-neutral-200 p-6">
                <div class="pb-4 mb-6 border-b border-neutral-200">
                    <h2 class="text-lg font-bold text-[#0B0B0E]">Membership Plan Selection</h2>
                    <p class="text-xs text-neutral-500 mt-0.5">Select membership tier and prepaid duration.</p>
                </div>

                <input type="hidden" id="plan_type" name="plan_type" value="Student Membership">
                <input type="hidden" id="duration_months" name="duration_months" value="1">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <button type="button" id="btn-plan-student" onclick="selectPlan('Student Membership')" class="p-4 rounded-xl border-2 border-[#E31E24] bg-red-50/40 text-left">
                        <div class="flex justify-between font-bold text-sm text-[#0B0B0E]">
                            <span>Student Membership</span>
                            <span class="text-[#E31E24]">₱650 / mo</span>
                        </div>
                        <p class="text-xs text-neutral-500 mt-1">Discounted rate for students with school ID.</p>
                    </button>
                    <button type="button" id="btn-plan-regular" onclick="selectPlan('Regular Membership')" class="p-4 rounded-xl border-2 border-neutral-200 bg-white text-left">
                        <div class="flex justify-between font-bold text-sm text-[#0B0B0E]">
                            <span>Regular Membership</span>
                            <span class="text-[#E31E24]">₱750 / mo</span>
                        </div>
                        <p class="text-xs text-neutral-500 mt-1">Standard full-access prepaid membership.</p>
                    </button>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-semibold text-neutral-700 mb-2.5">Membership Duration</label>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                        @foreach([1 => '1 Month', 2 => '2 Months', 3 => '3 Months', 6 => '6 Months', 12 => '12 Months'] as $m => $label)
                            <button type="button" id="btn-dur-{{ $m }}" onclick="selectDuration({{ $m }})" class="p-3 rounded-xl border-2 text-center {{ $m === 1 ? 'border-[#E31E24] bg-[#E31E24] text-white' : 'border-neutral-200 bg-white text-[#0B0B0E]' }}">
                                <div class="text-xs font-bold">{{ $label }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-neutral-50 border border-neutral-200 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-neutral-500">Selected Plan</div>
                        <div id="plan-selected-text" class="text-sm font-bold text-[#0B0B0E]">Student Membership · 1 Month</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-neutral-500">Calculated Price</div>
                        <div id="plan-price-text" class="text-xl font-mono font-extrabold text-[#E31E24]">₱650.00</div>
                    </div>
                </div>

                <div class="mt-6 pt-5 border-t border-neutral-200 flex items-center justify-between">
                    <button type="button" onclick="goToStep(1)" class="px-4 py-2.5 text-xs font-semibold text-neutral-700 bg-white border border-neutral-300 rounded-lg">← Back to Information</button>
                    <button type="button" onclick="goToStep(3)" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#E31E24] rounded-lg">Continue to Summary →</button>
                </div>
            </div>

            <!-- Contextual Right Preview ONLY on Step 2 -->
            <div class="bg-[#0B0B0E] text-white rounded-xl p-6 border border-neutral-800">
                <div class="text-xs font-semibold text-[#E31E24] uppercase">Live Plan Preview</div>
                <div id="preview-member-name" class="text-base font-bold mt-1">—</div>
                <div class="mt-4 pt-4 border-t border-neutral-800 space-y-2.5 text-xs">
                    <div class="flex justify-between"><span class="text-neutral-400">Plan</span><span id="preview-plan">Student Membership</span></div>
                    <div class="flex justify-between"><span class="text-neutral-400">Duration</span><span id="preview-duration">1 Month</span></div>
                    <div class="flex justify-between"><span class="text-neutral-400">Monthly Rate</span><span id="preview-rate">₱650.00</span></div>
                    <div class="flex justify-between pt-2 border-t border-neutral-800"><span class="text-neutral-400">Total</span><span id="preview-total" class="text-lg font-bold text-[#E31E24]">₱650.00</span></div>
                </div>
            </div>
        </div>

        <!-- STEP 3: Payment Summary ONLY -->
        <div id="step-panel-3" class="hidden bg-white rounded-xl border border-neutral-200 p-6">
            <div class="pb-4 mb-6 border-b border-neutral-200">
                <h2 class="text-lg font-bold text-[#0B0B0E]">Payment Summary</h2>
                <p class="text-xs text-neutral-500 mt-0.5">Review the prepaid membership charges.</p>
            </div>

            <div class="bg-neutral-50 rounded-xl border border-neutral-200 p-5 space-y-3 text-sm">
                <div class="flex justify-between py-1.5 border-b border-neutral-200"><span class="text-neutral-600">Member Name</span><span id="sum-name" class="font-bold text-[#0B0B0E]">—</span></div>
                <div class="flex justify-between py-1.5 border-b border-neutral-200"><span class="text-neutral-600">Selected Membership</span><span id="sum-plan" class="font-semibold text-[#0B0B0E]">Student Membership</span></div>
                <div class="flex justify-between py-1.5 border-b border-neutral-200"><span class="text-neutral-600">Duration</span><span id="sum-duration" class="font-semibold text-[#0B0B0E]">1 Month</span></div>
                <div class="flex justify-between py-1.5 border-b border-neutral-200"><span class="text-neutral-600">Monthly Rate</span><span id="sum-rate" class="font-mono font-semibold text-[#0B0B0E]">₱650.00</span></div>
                <div class="flex justify-between pt-2"><span class="text-base font-bold text-[#0B0B0E]">Total Amount</span><span id="sum-total" class="text-2xl font-mono font-extrabold text-[#E31E24]">₱650.00</span></div>
            </div>

            <div class="mt-5">
                <label class="block text-xs font-semibold text-neutral-700 mb-2">Payment Method (Cash or GCash Only)</label>
                <select id="payment_method" name="payment_method" class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-neutral-300 bg-white">
                    <option value="Cash">Cash</option>
                    <option value="GCash">GCash</option>
                </select>
            </div>

            <div class="mt-6 pt-5 border-t border-neutral-200 flex items-center justify-between">
                <button type="button" onclick="goToStep(2)" class="px-4 py-2.5 text-xs font-semibold text-neutral-700 bg-white border border-neutral-300 rounded-lg">← Back</button>
                <button type="button" onclick="goToStep(4)" class="px-5 py-2.5 text-xs font-semibold text-white bg-[#E31E24] rounded-lg">Continue to Confirmation →</button>
            </div>
        </div>

        <!-- STEP 4: Confirmation ONLY -->
        <div id="step-panel-4" class="hidden bg-white rounded-xl border border-neutral-200 p-6">
            <div class="pb-4 mb-6 border-b border-neutral-200">
                <h2 class="text-lg font-bold text-[#0B0B0E]">Confirm Registration</h2>
                <p class="text-xs text-neutral-500 mt-0.5">Confirm all member and membership details below to complete registration and generate the QR Code Pass.</p>
            </div>

            <div class="p-5 rounded-xl bg-neutral-50 border border-neutral-200 space-y-2.5 text-xs">
                <div class="flex justify-between"><span class="text-neutral-500">Full Name:</span><span id="conf-name" class="font-bold text-[#0B0B0E]">—</span></div>
                <div class="flex justify-between"><span class="text-neutral-500">Email & Phone:</span><span id="conf-contact" class="font-semibold text-[#0B0B0E]">—</span></div>
                <div class="flex justify-between"><span class="text-neutral-500">Membership Plan:</span><span id="conf-plan" class="font-semibold text-[#0B0B0E]">—</span></div>
                <div class="flex justify-between"><span class="text-neutral-500">Duration:</span><span id="conf-duration" class="font-semibold text-[#0B0B0E]">—</span></div>
                <div class="flex justify-between pt-2 border-t border-neutral-200"><span class="font-bold text-[#0B0B0E]">Total Prepaid Amount:</span><span id="conf-total" class="font-mono font-extrabold text-[#E31E24] text-sm">₱650.00</span></div>
            </div>

            <div class="mt-6 pt-5 border-t border-neutral-200 flex items-center justify-between">
                <button type="button" onclick="goToStep(3)" class="px-4 py-2.5 text-xs font-semibold text-neutral-700 bg-white border border-neutral-300 rounded-lg">← Back</button>
                <button type="submit" class="px-6 py-2.5 text-xs font-semibold text-white bg-[#E31E24] hover:bg-[#c8191f] rounded-lg">Confirm Registration ✓</button>
            </div>
        </div>
    </form>
</div>

<script>
    let currentStep = 1;
    let selectedPlan = 'Student Membership';
    let selectedDuration = 1;
    let memberPhotoPreviewUrl = null;

    function previewMemberPhoto(input) {
        const file = input.files[0];
        const emptyState = document.getElementById('photo-preview-empty');
        const preview = document.getElementById('photo-preview');

        if (memberPhotoPreviewUrl) {
            URL.revokeObjectURL(memberPhotoPreviewUrl);
            memberPhotoPreviewUrl = null;
        }

        if (!file) {
            preview.classList.add('hidden');
            preview.classList.remove('flex');
            emptyState.classList.remove('hidden');
            return;
        }

        if (!file.type.startsWith('image/')) {
            input.value = '';
            preview.classList.add('hidden');
            preview.classList.remove('flex');
            emptyState.classList.remove('hidden');
            return;
        }

        memberPhotoPreviewUrl = URL.createObjectURL(file);
        document.getElementById('photo-preview-image').src = memberPhotoPreviewUrl;
        document.getElementById('photo-preview-name').textContent = file.name;
        emptyState.classList.add('hidden');
        preview.classList.remove('hidden');
        preview.classList.add('flex');
    }

    function clearMemberPhoto() {
        const input = document.getElementById('photo');
        input.value = '';
        previewMemberPhoto(input);
    }

    const stepTitles = {
        1: 'STEP 1 OF 5 · Member Information',
        2: 'STEP 2 OF 5 · Membership Plan',
        3: 'STEP 3 OF 5 · Payment Summary',
        4: 'STEP 4 OF 5 · Confirmation',
        5: 'STEP 5 OF 5 · QR Code'
    };

    function selectPlan(plan) {
        selectedPlan = plan;
        document.getElementById('plan_type').value = plan;
        document.getElementById('btn-plan-student').className = plan === 'Student Membership'
            ? 'p-4 rounded-xl border-2 border-[#E31E24] bg-red-50/40 text-left'
            : 'p-4 rounded-xl border-2 border-neutral-200 bg-white text-left';
        document.getElementById('btn-plan-regular').className = plan === 'Regular Membership'
            ? 'p-4 rounded-xl border-2 border-[#E31E24] bg-red-50/40 text-left'
            : 'p-4 rounded-xl border-2 border-neutral-200 bg-white text-left';
        updateCalculatedTotals();
    }

    function selectDuration(months) {
        selectedDuration = months;
        document.getElementById('duration_months').value = months;
        [1, 2, 3, 6, 12].forEach(m => {
            document.getElementById('btn-dur-' + m).className = (m === months)
                ? 'p-3 rounded-xl border-2 text-center border-[#E31E24] bg-[#E31E24] text-white'
                : 'p-3 rounded-xl border-2 text-center border-neutral-200 bg-white text-[#0B0B0E]';
        });
        updateCalculatedTotals();
    }

    function updateCalculatedTotals() {
        const rate = selectedPlan === 'Student Membership' ? 650 : 750;
        const total = rate * selectedDuration;
        const durLabel = selectedDuration + (selectedDuration === 1 ? ' Month' : ' Months');
        const formattedTotal = '₱' + total.toLocaleString() + '.00';
        const formattedRate = '₱' + rate.toLocaleString() + '.00';

        document.getElementById('plan-selected-text').textContent = selectedPlan + ' · ' + durLabel;
        document.getElementById('plan-price-text').textContent = formattedTotal;

        document.getElementById('preview-member-name').textContent = document.getElementById('full_name').value || 'New Member';
        document.getElementById('preview-plan').textContent = selectedPlan;
        document.getElementById('preview-duration').textContent = durLabel;
        document.getElementById('preview-rate').textContent = formattedRate;
        document.getElementById('preview-total').textContent = formattedTotal;

        document.getElementById('sum-name').textContent = document.getElementById('full_name').value;
        document.getElementById('sum-plan').textContent = selectedPlan;
        document.getElementById('sum-duration').textContent = durLabel;
        document.getElementById('sum-rate').textContent = formattedRate;
        document.getElementById('sum-total').textContent = formattedTotal;

        document.getElementById('conf-name').textContent = document.getElementById('full_name').value;
        document.getElementById('conf-contact').textContent = document.getElementById('email').value + ' · ' + document.getElementById('phone').value;
        document.getElementById('conf-plan').textContent = selectedPlan;
        document.getElementById('conf-duration').textContent = durLabel;
        document.getElementById('conf-total').textContent = formattedTotal;
    }

    function goToStep(step) {
        const errorBox = document.getElementById('wizard-error');
        if (step > 1) {
            const requiredFields = ['full_name', 'email', 'phone', 'date_of_birth'];
            const invalidField = requiredFields
                .map((id) => document.getElementById(id))
                .find((field) => !field.checkValidity());

            if (invalidField) {
                errorBox.textContent = invalidField.validationMessage || 'Please complete all required member information.';
                errorBox.classList.remove('hidden');
                invalidField.focus();
                return;
            }
        }
        errorBox.classList.add('hidden');
        updateCalculatedTotals();

        [1, 2, 3, 4].forEach(s => {
            const panel = document.getElementById('step-panel-' + s);
            if (panel) panel.classList.toggle('hidden', s !== step);
        });

        [1, 2, 3, 4, 5].forEach(s => {
            const badge = document.getElementById('badge-step-' + s);
            const label = document.getElementById('label-step-' + s);
            const bar = document.getElementById('bar-step-' + s);
            if (s === step) {
                badge.className = 'w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 bg-[#E31E24] text-white';
                label.className = 'text-xs font-semibold truncate text-[#E31E24]';
                bar.className = 'mt-2 h-1.5 w-full rounded-full bg-[#E31E24]';
            } else if (s < step) {
                badge.className = 'w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 bg-[#0F172A] text-white';
                label.className = 'text-xs font-semibold truncate text-[#0F172A]';
                bar.className = 'mt-2 h-1.5 w-full rounded-full bg-[#0F172A]';
            } else {
                badge.className = 'w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 bg-neutral-100 text-neutral-400 border border-neutral-200';
                label.className = 'text-xs font-semibold truncate text-neutral-400';
                bar.className = 'mt-2 h-1.5 w-full rounded-full bg-neutral-200';
            }
        });

        currentStep = step;
        document.getElementById('step-subtitle').textContent = stepTitles[step];
    }
</script>
@endsection
