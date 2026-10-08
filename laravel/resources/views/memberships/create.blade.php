@extends('layouts.app')

@section('title', 'Register Member — MAX GYM')

@section('content')
<style>
    .rm-wrap { max-width: 56rem; margin: 0 auto; }
    .rm-card { background: rgba(255,255,255,.92); -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px);
        border: 1px solid rgba(17,17,17,.07); border-radius: .9rem;
        box-shadow: 0 1px 2px rgba(17,17,17,.05), 0 18px 40px -22px rgba(17,17,17,.35); }
    .rm-card--top { border-top: 3px solid #E31B23; }
    .rm-panel { animation: rmIn .38s cubic-bezier(.2,.7,.2,1) both; }
    @keyframes rmIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }

    .rm-eyebrow { font-size: .65rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #E31B23; }
    .rm-h1 { font-size: 1.6rem; line-height: 1.15; font-weight: 800; letter-spacing: -.02em; color: #0B0B0E; }
    .rm-h2 { font-size: 1.1rem; font-weight: 800; color: #0B0B0E; letter-spacing: -.01em; }
    .rm-sub { font-size: .75rem; color: #6b7280; margin-top: .2rem; }
    .rm-head { padding-bottom: 1rem; margin-bottom: 1.4rem; border-bottom: 1px solid #eceef2; }
    .rm-pill { font-family: 'IBM Plex Mono', monospace; font-size: .68rem; color: #4b5563; background: #f3f4f6; border: 1px solid #e5e7eb; padding: .35rem .7rem; border-radius: 999px; white-space: nowrap; }

    /* Stepper */
    .rm-steps { display: grid; grid-template-columns: repeat(5, minmax(0,1fr)); gap: .5rem; margin-top: 1.2rem; }
    .rm-step { display: flex; flex-direction: column; gap: .55rem; }
    .rm-step__top { display: flex; align-items: center; gap: .55rem; min-width: 0; }
    .rm-badge { width: 1.85rem; height: 1.85rem; border-radius: .55rem; display: grid; place-items: center; flex-shrink: 0; font-size: .75rem; font-weight: 800;
        background: #f3f4f6; color: #9ca3af; border: 1px solid #e5e7eb; transition: all .3s ease; }
    .rm-label { font-size: .72rem; font-weight: 700; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; transition: color .3s ease; }
    .rm-bar { height: .3rem; border-radius: 999px; background: #e5e7eb; position: relative; overflow: hidden; }
    .rm-bar::after { content: ''; position: absolute; inset: 0; transform: scaleX(0); transform-origin: left; background: #E31B23; transition: transform .45s cubic-bezier(.2,.7,.2,1); border-radius: inherit; }
    .rm-step[data-state="active"] .rm-badge { background: #E31B23; border-color: #E31B23; color: #fff; box-shadow: 0 0 0 4px rgba(227,27,35,.15); }
    .rm-step[data-state="active"] .rm-label { color: #E31B23; }
    .rm-step[data-state="active"] .rm-bar::after, .rm-step[data-state="done"] .rm-bar::after { transform: scaleX(1); }
    .rm-step[data-state="done"] .rm-badge { background: #0B0B0E; border-color: #0B0B0E; color: #fff; }
    .rm-step[data-state="done"] .rm-label { color: #0B0B0E; }
    .rm-step[data-state="done"] .rm-bar::after { background: #0B0B0E; }

    /* Fields */
    .rm-field label, .rm-lbl { display: block; font-size: .72rem; font-weight: 700; color: #374151; margin-bottom: .4rem; letter-spacing: .01em; }
    .rm-req { color: #E31B23; }
    .rm-input { width: 100%; padding: .72rem .9rem; font-size: .875rem; color: #0B0B0E; background: #fff; border: 1px solid #d5d8de; border-radius: .6rem;
        transition: border-color .15s ease, box-shadow .15s ease; outline: none; }
    .rm-input::placeholder { color: #a1a7b2; }
    .rm-input:hover { border-color: #b8bdc7; }
    .rm-input:focus { border-color: #E31B23; box-shadow: 0 0 0 4px rgba(227,27,35,.13); }
    select.rm-input { appearance: none; -webkit-appearance: none; padding-right: 2.4rem; cursor: pointer;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%236b7280' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right .8rem center; }

    /* Photo drop zone */
    .rm-drop { display: flex; align-items: center; gap: .9rem; padding: .9rem 1rem; border: 1.5px dashed #c5c9d2; border-radius: .7rem; background: #f8f9fb; cursor: pointer; transition: all .2s ease; }
    .rm-drop:hover, .rm-drop:focus-within { border-color: #E31B23; background: #fff5f5; }
    .rm-drop__icon { width: 2.8rem; height: 2.8rem; border-radius: .7rem; background: #0B0B0E; color: #fff; display: grid; place-items: center; flex-shrink: 0; }
    .rm-drop__title { font-size: .8rem; font-weight: 700; color: #0B0B0E; }
    .rm-drop__hint { font-size: .68rem; color: #6b7280; margin-top: .1rem; }
    .rm-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; }

    /* Buttons */
    .rm-actions { margin-top: 1.6rem; padding-top: 1.2rem; border-top: 1px solid #eceef2; display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap; }
    .rm-btn { display: inline-flex; align-items: center; justify-content: center; gap: .4rem; padding: .7rem 1.2rem; font-size: .75rem; font-weight: 700; border-radius: .6rem; cursor: pointer; transition: all .15s ease; text-decoration: none; border: 1px solid transparent; }
    .rm-btn--primary { color: #fff; background: #E31B23; box-shadow: 0 6px 16px -8px rgba(227,27,35,.8); }
    .rm-btn--primary:hover { background: #c8151c; transform: translateY(-1px); }
    .rm-btn--ghost { color: #374151; background: #fff; border-color: #d5d8de; }
    .rm-btn--ghost:hover { background: #f3f4f6; border-color: #b8bdc7; }
    .rm-btn:focus-visible { outline: none; box-shadow: 0 0 0 4px rgba(227,27,35,.25); }

    /* Plan + duration choices */
    .rm-plan { text-align: left; padding: 1rem 1.1rem; border-radius: .8rem; border: 2px solid #e5e7eb; background: #fff; cursor: pointer; transition: all .2s ease; position: relative; }
    .rm-plan:hover { border-color: #f0a3a6; transform: translateY(-2px); }
    .rm-plan[data-selected="true"] { border-color: #E31B23; background: linear-gradient(180deg, #fff6f6, #fff); box-shadow: 0 0 0 4px rgba(227,27,35,.10); }
    .rm-plan__check { position: absolute; top: .7rem; right: .7rem; width: 1.15rem; height: 1.15rem; border-radius: 50%; border: 2px solid #d1d5db; display: grid; place-items: center; transition: all .2s ease; }
    .rm-plan[data-selected="true"] .rm-plan__check { background: #E31B23; border-color: #E31B23; }
    .rm-plan__check svg { width: .6rem; height: .6rem; stroke: #fff; opacity: 0; transition: opacity .2s ease; }
    .rm-plan[data-selected="true"] .rm-plan__check svg { opacity: 1; }
    .rm-plan__name { font-size: .85rem; font-weight: 800; color: #0B0B0E; padding-right: 1.6rem; }
    .rm-plan__price { font-family: 'IBM Plex Mono', monospace; font-size: 1.15rem; font-weight: 700; color: #E31B23; margin-top: .35rem; }
    .rm-plan__price small { font-size: .65rem; color: #6b7280; font-weight: 500; }
    .rm-plan__desc { font-size: .7rem; color: #6b7280; margin-top: .3rem; line-height: 1.4; }
    .rm-dur { padding: .75rem .4rem; border-radius: .7rem; border: 2px solid #e5e7eb; background: #fff; text-align: center; font-size: .75rem; font-weight: 700; color: #0B0B0E; cursor: pointer; transition: all .2s ease; }
    .rm-dur:hover { border-color: #f0a3a6; }
    .rm-dur[data-selected="true"] { background: #E31B23; border-color: #E31B23; color: #fff; box-shadow: 0 8px 18px -10px rgba(227,27,35,.9); }

    /* Summary rows / total box */
    .rm-box { background: #f8f9fb; border: 1px solid #e5e7eb; border-radius: .8rem; padding: .35rem 1.1rem; }
    .rm-row { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: .7rem 0; border-bottom: 1px solid #e9ebef; font-size: .8rem; }
    .rm-row:last-child { border-bottom: 0; }
    .rm-row > span:first-child { color: #6b7280; }
    .rm-row > span:last-child { font-weight: 700; color: #0B0B0E; text-align: right; word-break: break-word; }
    .rm-total { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 1rem; padding: 1rem 1.2rem; border-radius: .8rem; background: #0B0B0E; color: #fff; position: relative; overflow: hidden; }
    .rm-total > * { position: relative; }
    .rm-total__label { font-size: .75rem; color: #d1d5db; font-weight: 600; }
    .rm-total__amt { font-family: 'IBM Plex Mono', monospace; font-size: 1.6rem; font-weight: 700; }

    /* Live preview */
    .rm-preview { background: #0B0B0E; color: #fff; border-radius: .9rem; padding: 1.4rem; position: sticky; top: 4.5rem; overflow: hidden; border: 1px solid #25252b; box-shadow: 0 18px 40px -20px rgba(0,0,0,.7); }
    .rm-preview > * { position: relative; }
    .rm-preview .rm-row { border-color: #25252b; font-size: .75rem; padding: .6rem 0; }
    .rm-preview .rm-row > span:first-child { color: #9ca3af; }
    .rm-preview .rm-row > span:last-child { color: #fff; }

    .rm-alert { margin-bottom: 1.1rem; padding: .9rem 1rem; border-radius: .7rem; background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #E31B23; font-size: .75rem; color: #b91c1c; }

    @media (max-width: 640px) {
        .rm-label { display: none; }
        .rm-h1 { font-size: 1.35rem; }
        .rm-actions .rm-btn { flex: 1; }
        .rm-total__amt { font-size: 1.3rem; }
    }
    @media (prefers-reduced-motion: reduce) { .rm-panel { animation: none; } }
</style>

<div class="rm-wrap py-1">
    <!-- Header + progress -->
    <div class="rm-card rm-card--top p-5 sm:p-6 mb-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="rm-eyebrow">Enrollment</div>
                <h1 class="rm-h1 mt-1">Register Member</h1>
                <p id="step-subtitle" class="rm-sub">STEP 1 OF 5 · Member Information</p>
            </div>
            <div class="rm-pill">Prepaid Membership Enrollment</div>
        </div>

        <div class="rm-steps">
            @foreach([1 => 'Info', 2 => 'Plan', 3 => 'Summary', 4 => 'Confirm', 5 => 'QR Code'] as $num => $label)
                <div id="step-{{ $num }}" class="rm-step" data-state="{{ $num === 1 ? 'active' : 'todo' }}">
                    <div class="rm-step__top">
                        <div id="badge-step-{{ $num }}" class="rm-badge">{{ $num }}</div>
                        <span id="label-step-{{ $num }}" class="rm-label">{{ $label }}</span>
                    </div>
                    <div id="bar-step-{{ $num }}" class="rm-bar"></div>
                </div>
            @endforeach
        </div>
    </div>

    @if($errors->any())
        <div role="alert" class="rm-alert">
            <p class="font-bold">Registration could not be completed.</p>
            <ul class="mt-1 list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div id="wizard-error" role="alert" class="rm-alert hidden font-semibold"></div>

    <form id="register-member-form" method="POST" action="{{ route('memberships.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- STEP 1 -->
        <div id="step-panel-1" class="rm-card p-5 sm:p-7 rm-panel">
            <div class="rm-head">
                <h2 class="rm-h2">Member Information</h2>
                <p class="rm-sub">Enter personal details and upload a profile photo.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-5">
                <div class="rm-field">
                    <label for="surname">Surname <span class="rm-req">*</span></label>
                    <input type="text" id="surname" name="surname" value="{{ old('surname') }}" required class="rm-input" placeholder="e.g. Doe" autocomplete="family-name">
                </div>
                <div class="rm-field">
                    <label for="first_name">First Name <span class="rm-req">*</span></label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required class="rm-input" placeholder="e.g. John" autocomplete="given-name">
                </div>
                <div class="rm-field">
                    <label for="email">Email Address <span class="rm-req">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required class="rm-input" placeholder="e.g. member@maxgym.ph" autocomplete="email">
                </div>
                <div class="rm-field">
                    <label for="phone">Phone Number <span class="rm-req">*</span></label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required class="rm-input" placeholder="e.g. 09174829910" inputmode="numeric" pattern="[0-9]{11}" maxlength="11" title="Enter exactly 11 digits, such as 09174829910" autocomplete="tel">
                </div>
                <div class="rm-field">
                    <label for="gender">Gender <span class="rm-req">*</span></label>
                    <select id="gender" name="gender" class="rm-input">
                        <option value="Male" @selected(old('gender') === 'Male')>Male</option>
                        <option value="Female" @selected(old('gender') === 'Female')>Female</option>
                        <option value="Other" @selected(old('gender') === 'Other')>Other</option>
                    </select>
                </div>
                <div class="rm-field">
                    <label for="date_of_birth">Date of Birth <span class="rm-req">*</span></label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" lang="en-GB" required class="rm-input">
                </div>
                <div class="md:col-span-2">
                    <span class="rm-lbl">Profile Photo</span>
                    <label id="photo-preview-empty" for="photo" class="rm-drop">
                        <span class="rm-drop__icon" aria-hidden="true">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        </span>
                        <span>
                            <span class="rm-drop__title block">Choose a profile photo</span>
                            <span class="rm-drop__hint block">Shown on the member card · JPG or PNG</span>
                        </span>
                    </label>
                    <input type="file" id="photo" name="photo" accept="image/*" onchange="previewMemberPhoto(this)" class="rm-sr">
                    <div id="photo-preview" class="hidden items-center gap-3 rounded-xl border border-neutral-200 bg-neutral-50 p-3" aria-live="polite">
                        <img id="photo-preview-image" src="" alt="Selected member profile photo preview" class="w-16 h-16 rounded-xl object-cover border border-neutral-200">
                        <div class="min-w-0 flex-1">
                            <p id="photo-preview-name" class="truncate text-xs font-bold text-neutral-800"></p>
                            <div class="mt-1 flex gap-3">
                                <label for="photo" class="text-[11px] font-semibold text-neutral-600 hover:underline cursor-pointer">Change</label>
                                <button type="button" onclick="clearMemberPhoto()" class="text-[11px] font-semibold text-[#B51219] hover:underline">Remove photo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rm-actions">
                <a href="{{ route('memberships.index') }}" class="rm-btn rm-btn--ghost">Cancel</a>
                <button type="button" onclick="goToStep(2)" class="rm-btn rm-btn--primary">Continue to Plan →</button>
            </div>
        </div>

        <!-- STEP 2 -->
        <div id="step-panel-2" class="hidden rm-panel">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
                <div class="lg:col-span-2 rm-card p-5 sm:p-7">
                    <div class="rm-head">
                        <h2 class="rm-h2">Membership Plan Selection</h2>
                        <p class="rm-sub">Select membership tier and prepaid duration.</p>
                    </div>

                    <input type="hidden" id="plan_type" name="plan_type" value="Student Membership">
                    <input type="hidden" id="duration_months" name="duration_months" value="1">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <button type="button" id="btn-plan-student" onclick="selectPlan('Student Membership')" class="rm-plan" data-selected="true" aria-pressed="true">
                            <span class="rm-plan__check"><svg fill="none" stroke-width="4" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></span>
                            <div class="rm-plan__name">Student Membership</div>
                            <div class="rm-plan__price">₱650 <small>/ month</small></div>
                            <p class="rm-plan__desc">Discounted rate for students with school ID.</p>
                        </button>
                        <button type="button" id="btn-plan-regular" onclick="selectPlan('Regular Membership')" class="rm-plan" data-selected="false" aria-pressed="false">
                            <span class="rm-plan__check"><svg fill="none" stroke-width="4" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg></span>
                            <div class="rm-plan__name">Regular Membership</div>
                            <div class="rm-plan__price">₱750 <small>/ month</small></div>
                            <p class="rm-plan__desc">Standard full-access prepaid membership.</p>
                        </button>
                    </div>

                    <div class="mb-6">
                        <span class="rm-lbl">Membership Duration</span>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                            @foreach([1 => '1 Month', 2 => '2 Months', 3 => '3 Months', 6 => '6 Months', 12 => '12 Months'] as $m => $label)
                                <button type="button" id="btn-dur-{{ $m }}" onclick="selectDuration({{ $m }})" class="rm-dur" data-selected="{{ $m === 1 ? 'true' : 'false' }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="rm-total">
                        <div>
                            <div class="rm-total__label">Selected Plan</div>
                            <div id="plan-selected-text" class="text-sm font-bold mt-0.5">Student Membership · 1 Month</div>
                        </div>
                        <div class="text-right">
                            <div class="rm-total__label">Calculated Price</div>
                            <div id="plan-price-text" class="rm-total__amt">₱650.00</div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="amount_paid" class="rm-lbl">Amount Paid</label>
                        <input type="number" id="amount_paid" name="amount_paid" value="{{ old('amount_paid', 650) }}" min="0.01" max="650" step="0.01" required class="rm-input">
                        <p class="rm-sub mt-1">A partial payment starts a ₱50 daily credit deduction the day after payment.</p>
                        <p id="sum-remaining" class="mt-2 text-xs font-semibold text-[#B91C1C]">Remaining plan balance: ₱0.00</p>
                    </div>

                    <div class="rm-actions">
                        <button type="button" onclick="goToStep(1)" class="rm-btn rm-btn--ghost">← Back to Information</button>
                        <button type="button" onclick="goToStep(3)" class="rm-btn rm-btn--primary">Continue to Summary →</button>
                    </div>
                </div>

                <!-- Live preview -->
                <div class="rm-preview">
                    <div class="rm-eyebrow">Live Plan Preview</div>
                    <div id="preview-member-name" class="text-base font-bold mt-1.5 break-words">—</div>
                    <div class="mt-4 pt-2 border-t border-[#25252b]">
                        <div class="rm-row"><span>Plan</span><span id="preview-plan">Student Membership</span></div>
                        <div class="rm-row"><span>Duration</span><span id="preview-duration">1 Month</span></div>
                        <div class="rm-row"><span>Monthly Rate</span><span id="preview-rate">₱650.00</span></div>
                        <div class="rm-row"><span>Total</span><span id="preview-total" class="text-lg font-bold" style="color:#ff4b52">₱650.00</span></div>
                        <div class="rm-row"><span>Amount Paid</span><span id="preview-paid">₱650.00</span></div>
                        <div class="rm-row"><span>Plan Balance</span><span id="preview-balance">₱0.00</span></div>
                        <div id="preview-daily-credit-row" class="rm-row hidden"><span>Daily Credit Deduction</span><span id="preview-daily-credit">₱50.00/day starting tomorrow</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 3 -->
        <div id="step-panel-3" class="hidden rm-card p-5 sm:p-7 rm-panel">
            <div class="rm-head">
                <h2 class="rm-h2">Payment Summary</h2>
                <p class="rm-sub">Review the prepaid membership charges.</p>
            </div>

            <div class="rm-box">
                <div class="rm-row"><span>Member Name</span><span id="sum-name">—</span></div>
                <div class="rm-row"><span>Selected Membership</span><span id="sum-plan">Student Membership</span></div>
                <div class="rm-row"><span>Duration</span><span id="sum-duration">1 Month</span></div>
                <div class="rm-row"><span>Monthly Rate</span><span id="sum-rate" class="font-mono">₱650.00</span></div>
            </div>
            <div class="rm-total">
                <span class="rm-total__label">Total Amount</span>
                <span id="sum-total" class="rm-total__amt">₱650.00</span>
            </div>

            <div class="rm-box mt-4">
                <div class="rm-row"><span>Amount Paid</span><span id="sum-paid">₱650.00</span></div>
                <div class="rm-row"><span>Remaining Plan Balance</span><span id="sum-balance">₱0.00</span></div>
            </div>

            <div class="mt-5">
                <label for="payment_method" class="rm-lbl">Payment Method (Cash or GCash Only)</label>
                <select id="payment_method" name="payment_method" class="rm-input">
                    <option value="Cash">Cash</option>
                    <option value="GCash">GCash</option>
                </select>
            </div>

            <div class="rm-actions">
                <button type="button" onclick="goToStep(2)" class="rm-btn rm-btn--ghost">← Back</button>
                <button type="button" onclick="goToStep(4)" class="rm-btn rm-btn--primary">Continue to Confirmation →</button>
            </div>
        </div>

        <!-- STEP 4 -->
        <div id="step-panel-4" class="hidden rm-card p-5 sm:p-7 rm-panel">
            <div class="rm-head">
                <h2 class="rm-h2">Confirm Registration</h2>
                <p class="rm-sub">Confirm all member and membership details below to complete registration and generate the QR Code Pass.</p>
            </div>

            <div class="rm-box">
                <div class="rm-row"><span>Full Name</span><span id="conf-name">—</span></div>
                <div class="rm-row"><span>Email &amp; Phone</span><span id="conf-contact">—</span></div>
                <div class="rm-row"><span>Membership Plan</span><span id="conf-plan">—</span></div>
                <div class="rm-row"><span>Duration</span><span id="conf-duration">—</span></div>
                <div class="rm-row"><span>Amount Paid</span><span id="conf-paid">₱650.00</span></div>
                <div class="rm-row"><span>Remaining Plan Balance</span><span id="conf-remaining">₱0.00</span></div>
            </div>
            <div class="rm-total">
                <span class="rm-total__label">Total Prepaid Amount</span>
                <span id="conf-total" class="rm-total__amt">₱650.00</span>
            </div>

            <div class="rm-actions">
                <button type="button" onclick="goToStep(3)" class="rm-btn rm-btn--ghost">← Back</button>
                <button type="submit" class="rm-btn rm-btn--primary">Confirm Registration ✓</button>
            </div>
        </div>
    </form>
</div>

<script>
    function fullName() {
        const surname = document.getElementById('surname').value.trim();
        const first = document.getElementById('first_name').value.trim();
        return surname && first ? surname + ', ' + first : (surname || first);
    }

    let currentStep = 1;
    let selectedPlan = 'Student Membership';
    let selectedDuration = 1;
    let memberPhotoPreviewUrl = null;
    let amountPaidWasEdited = @json(old('amount_paid') !== null);

    document.getElementById('amount_paid').addEventListener('input', () => {
        amountPaidWasEdited = true;
        updateCalculatedTotals();
    });

    function previewMemberPhoto(input) {
        const file = input.files[0];
        const emptyState = document.getElementById('photo-preview-empty');
        const preview = document.getElementById('photo-preview');

        if (memberPhotoPreviewUrl) {
            URL.revokeObjectURL(memberPhotoPreviewUrl);
            memberPhotoPreviewUrl = null;
        }

        if (!file || !file.type.startsWith('image/')) {
            if (file) input.value = '';
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
        [['btn-plan-student', 'Student Membership'], ['btn-plan-regular', 'Regular Membership']].forEach(([id, name]) => {
            const btn = document.getElementById(id);
            const on = plan === name;
            btn.dataset.selected = on ? 'true' : 'false';
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        updateCalculatedTotals();
    }

    function selectDuration(months) {
        selectedDuration = months;
        document.getElementById('duration_months').value = months;
        [1, 2, 3, 6, 12].forEach(m => {
            document.getElementById('btn-dur-' + m).dataset.selected = (m === months) ? 'true' : 'false';
        });
        updateCalculatedTotals();
    }

    function updateCalculatedTotals() {
        const rate = selectedPlan === 'Student Membership' ? 650 : 750;
        const total = rate * selectedDuration;
        const amountPaidInput = document.getElementById('amount_paid');
        amountPaidInput.max = total.toFixed(2);
        if (!amountPaidWasEdited || Number(amountPaidInput.value) > total) {
            amountPaidInput.value = total.toFixed(2);
        }
        const amountPaid = Number(amountPaidInput.value) || 0;
        const remaining = Math.max(0, total - amountPaid);
        const durLabel = selectedDuration + (selectedDuration === 1 ? ' Month' : ' Months');
        const formattedTotal = '₱' + total.toLocaleString() + '.00';
        const formattedRate = '₱' + rate.toLocaleString() + '.00';
        const formattedPaid = '₱' + amountPaid.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const formattedRemaining = '₱' + remaining.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        document.getElementById('plan-selected-text').textContent = selectedPlan + ' · ' + durLabel;
        document.getElementById('plan-price-text').textContent = formattedTotal;

        document.getElementById('preview-member-name').textContent = fullName() || 'New Member';
        document.getElementById('preview-plan').textContent = selectedPlan;
        document.getElementById('preview-duration').textContent = durLabel;
        document.getElementById('preview-rate').textContent = formattedRate;
        document.getElementById('preview-total').textContent = formattedTotal;
        document.getElementById('preview-paid').textContent = formattedPaid;
        document.getElementById('preview-balance').textContent = formattedRemaining;
        document.getElementById('preview-daily-credit-row').classList.toggle('hidden', remaining <= 0);

        document.getElementById('sum-name').textContent = fullName();
        document.getElementById('sum-plan').textContent = selectedPlan;
        document.getElementById('sum-duration').textContent = durLabel;
        document.getElementById('sum-rate').textContent = formattedRate;
        document.getElementById('sum-total').textContent = formattedTotal;
        document.getElementById('sum-remaining').textContent = 'Remaining plan balance: ' + formattedRemaining;
        document.getElementById('sum-paid').textContent = formattedPaid;
        document.getElementById('sum-balance').textContent = formattedRemaining;

        document.getElementById('conf-name').textContent = fullName();
        document.getElementById('conf-contact').textContent = document.getElementById('email').value + ' · ' + document.getElementById('phone').value;
        document.getElementById('conf-plan').textContent = selectedPlan;
        document.getElementById('conf-duration').textContent = durLabel;
        document.getElementById('conf-total').textContent = formattedTotal;
        document.getElementById('conf-paid').textContent = formattedPaid;
        document.getElementById('conf-remaining').textContent = formattedRemaining;
    }

    function goToStep(step) {
        const errorBox = document.getElementById('wizard-error');
        if (step > 1) {
            const requiredFields = ['surname', 'first_name', 'email', 'phone', 'date_of_birth'];
            const invalidField = requiredFields
                .map((id) => document.getElementById(id))
                .find((field) => !field.checkValidity());

            if (invalidField) {
                errorBox.textContent = invalidField.validationMessage || 'Please complete all required member information.';
                errorBox.classList.remove('hidden');
                goToStepPanel(1);
                invalidField.focus();
                return;
            }
        }
        if (step > 3) {
            const amountPaidField = document.getElementById('amount_paid');
            if (!amountPaidField.checkValidity()) {
                errorBox.textContent = amountPaidField.validationMessage || 'Enter a valid amount paid for the selected plan.';
                errorBox.classList.remove('hidden');
                goToStepPanel(2);
                amountPaidField.focus();
                return;
            }
        }
        errorBox.classList.add('hidden');
        updateCalculatedTotals();
        goToStepPanel(step);
    }

    function goToStepPanel(step) {
        [1, 2, 3, 4].forEach(s => {
            const panel = document.getElementById('step-panel-' + s);
            if (panel) panel.classList.toggle('hidden', s !== step);
        });

        [1, 2, 3, 4, 5].forEach(s => {
            document.getElementById('step-' + s).dataset.state = s === step ? 'active' : (s < step ? 'done' : 'todo');
        });

        if (step !== currentStep) window.scrollTo({ top: 0, behavior: 'smooth' });
        currentStep = step;
        document.getElementById('step-subtitle').textContent = stepTitles[step];
    }

    updateCalculatedTotals();
</script>
@endsection
