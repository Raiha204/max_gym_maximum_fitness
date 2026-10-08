@extends('layouts.app')

@section('title', 'Attendance & QR Verification — MAX GYM')

@section('content')
<div class="space-y-4">
    <div class="bg-[#111111] text-white rounded-xl border-l-4 border-[#E31B23] p-4 sm:px-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-lg font-bold text-white">Attendance & Membership Verification</h1>
            <p class="text-[10px] text-neutral-300 mt-0.5">Scan a member QR code or search by ID to verify and record attendance.</p>
        </div>
        <span class="shrink-0 rounded-md bg-emerald-100 px-2.5 py-1.5 text-[10px] font-mono font-semibold text-emerald-800">{{ \Carbon\Carbon::now(config('app.display_timezone'))->format('D, M j, Y') }}</span>
    </div>

    @if(session('attendance_verified'))
        <div id="attendance-success-notice" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-800">
            {{ session('attendance_verified') }}
        </div>
        <script>window.setTimeout(() => document.getElementById('attendance-success-notice')?.remove(), 4000);</script>
    @endif

    @error('expired')
        <div class="p-4 rounded-xl bg-red-50 border-2 border-red-200 text-xs font-bold text-red-700">
            {{ $message }}
        </div>
    @enderror

    <!-- 3-Panel Redesigned Attendance Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
        <!-- LEFT PANEL: Attendance Mode -->
        <div class="lg:col-span-3 bg-white rounded-xl border border-neutral-200 p-4 space-y-2.5 shadow-sm">
            <div class="text-[10px] font-bold text-neutral-500 uppercase">Attendance Mode</div>
            <button type="button" id="btn-mode-scan" onclick="switchAttendanceMode('scan')" class="w-full px-3 py-2.5 rounded-lg font-bold text-[11px] text-white bg-[#E31E24] text-left flex justify-between items-center">
                <span>Scan QR Code</span>
                <span class="text-[10px] font-mono">CAMERA</span>
            </button>
            <button type="button" id="btn-mode-search" onclick="switchAttendanceMode('search')" class="w-full px-3 py-2.5 rounded-lg font-bold text-[11px] text-[#0B0B0E] bg-neutral-100 text-left flex justify-between items-center">
                <span>Search Member</span>
                <span class="text-[10px] font-mono">BACKUP</span>
            </button>
        </div>

        <!-- CENTER PANEL: Live Camera Scanner OR Manual Search Backup -->
        <div class="lg:col-span-5 bg-white rounded-xl border border-neutral-200 p-4 shadow-sm">
            <div id="panel-scanner">
                <div class="flex items-center justify-between gap-2 pb-3 mb-3 border-b border-neutral-200">
                    <h2 class="text-[12px] font-bold text-[#0B0B0E]">Live Camera QR Scanner</h2>
                    <span class="px-2 py-1 rounded bg-emerald-100 text-emerald-800 text-[9px] font-mono font-semibold">Ready · Local Network</span>
                </div>
                <div id="qr-reader" class="w-full min-h-[260px] max-h-[340px] bg-[#0B0B0E] rounded-lg overflow-hidden"></div>
                <div class="flex items-center justify-between gap-2 mt-3">
                    <p class="text-[9px] text-neutral-500">Camera idle · Activate the camera to scan a member QR code.</p>
                    <div class="flex shrink-0 gap-2">
                        <button type="button" id="toggle-qr-camera" onclick="toggleQrScanner()" class="px-3 py-2 text-[10px] font-semibold text-white bg-[#E31E24] rounded-lg">Activate Camera</button>
                    </div>
                </div>
            </div>

            <div id="panel-search" class="hidden">
                <div class="pb-3 mb-4 border-b border-neutral-200">
                    <h2 class="text-sm font-bold text-[#0B0B0E]">Manual Search Attendance (Backup)</h2>
                    <p class="text-xs text-neutral-500">Search by Member ID or Full Name for lost/forgotten QR cards.</p>
                </div>
                <form id="manual-search-form" class="flex gap-2">
                    <input type="search" id="manual-search-input" placeholder="Enter member ID or full name..." aria-label="Search member by ID or full name" class="flex-1 px-3.5 py-2 text-xs rounded-lg border border-neutral-300">
                    <button type="submit" id="manual-search-button" class="px-4 py-2 text-[11px] font-semibold text-white bg-[#0B0B0E] rounded-lg">Search</button>
                </form>
                <p id="manual-search-feedback" role="status" aria-live="polite" class="hidden mt-2 text-xs"></p>
                <div id="manual-search-results" class="hidden mt-2 max-h-64 overflow-y-auto rounded-lg border border-neutral-200 divide-y divide-neutral-100" role="listbox" aria-label="Matching members"></div>
            </div>
        </div>

        <!-- RIGHT PANEL: Member Verification Card -->
        <div id="member-verification-card" tabindex="-1" class="lg:col-span-4 bg-white rounded-xl border border-neutral-200 overflow-hidden shadow-sm">
            <div class="bg-[#0B0B0E] text-white px-5 py-3.5 border-b-4 border-[#E31E24]">
                <div class="text-xs font-bold uppercase">Member Verification Card</div>
            </div>
            <div class="p-4 space-y-3">
                <div class="text-center pb-4 border-b border-neutral-200">
                    <img id="ver-photo" src="" alt="Member profile" onerror="this.classList.add('hidden'); document.getElementById('ver-avatar').classList.remove('hidden')" class="hidden w-20 h-20 rounded-xl object-cover border-2 border-emerald-300 mx-auto mb-3">
                    <div id="ver-avatar" class="hidden w-20 h-20 rounded-xl bg-[#111111] text-white border-2 border-[#E31B23] mx-auto mb-3 flex items-center justify-center text-lg font-bold"></div>
                    <div id="ver-name" class="text-lg font-bold text-[#0B0B0E]">Scan or Search Member</div>
                    <div id="ver-id" class="text-xs font-mono font-bold text-[#E31E24] mt-0.5">Member ID: —</div>
                    <div id="ver-dob" class="text-xs text-neutral-500 mt-1">Date of Birth: —</div>
                </div>

                <div class="space-y-2 text-xs border-b border-neutral-200 pb-4">
                    <div class="flex justify-between"><span class="text-neutral-500">Membership:</span><span id="ver-type" class="font-bold text-[#0B0B0E]">—</span></div>
                    <div class="flex justify-between"><span class="text-neutral-500">Status:</span><span id="ver-status" class="font-bold">—</span></div>
                    <div class="flex justify-between"><span class="text-neutral-500">Start Date:</span><span id="ver-start" class="font-semibold text-[#0B0B0E]">—</span></div>
                    <div class="flex justify-between"><span class="text-neutral-500">Expires:</span><span id="ver-expires" class="font-semibold text-[#0B0B0E]">—</span></div>
                    <div class="flex justify-between"><span class="text-neutral-500">Remaining:</span><span id="ver-remaining" class="font-mono font-bold">—</span></div>
                </div>

                <div class="pt-1">
                    <div class="text-[10px] font-bold uppercase tracking-wide text-neutral-600 mb-2">Member Identity Verification</div>
                    <div class="grid grid-cols-3 gap-1.5">
                        <label class="flex min-w-0 cursor-pointer items-center gap-1.5 rounded-md border border-neutral-200 bg-neutral-50 px-2 py-2 text-[10px] text-neutral-700">
                            <input id="verify-photo-check" type="checkbox" class="h-3.5 w-3.5 shrink-0 rounded border-neutral-300 accent-[#E31E24] focus:ring-[#E31E24]" disabled>
                            <span>Photo</span>
                        </label>
                        <label class="flex min-w-0 cursor-pointer items-center gap-1.5 rounded-md border border-neutral-200 bg-neutral-50 px-2 py-2 text-[10px] text-neutral-700">
                            <input id="verify-name-check" type="checkbox" class="h-3.5 w-3.5 shrink-0 rounded border-neutral-300 accent-[#E31E24] focus:ring-[#E31E24]" disabled>
                            <span>Name</span>
                        </label>
                        <label class="flex min-w-0 cursor-pointer items-center gap-1.5 rounded-md border border-neutral-200 bg-neutral-50 px-2 py-2 text-[10px] text-neutral-700">
                            <input id="verify-dob-check" type="checkbox" class="h-3.5 w-3.5 shrink-0 rounded border-neutral-300 accent-[#E31E24] focus:ring-[#E31E24]" disabled>
                            <span>Birth Date</span>
                        </label>
                    </div>
                </div>

                <div id="ver-status-banner" role="status" aria-live="polite" class="hidden rounded-lg border p-3.5">
                    <div class="flex items-start gap-2.5">
                        <span id="ver-status-icon" aria-hidden="true" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-sm font-bold"></span>
                        <div>
                            <p id="ver-status-title" class="text-[11px] font-bold"></p>
                            <p id="ver-status-description" class="mt-0.5 text-[10px]"></p>
                        </div>
                    </div>
                </div>

                <form id="record-attendance-form" method="POST" action="{{ route('attendance.store') }}">
                    @csrf
                    <input type="hidden" id="form-member-id" name="member_id" value="">
                    <input type="hidden" id="form-method" name="verification_method" value="QR Code Scan">
                    <button type="submit" id="btn-record-attendance" disabled class="w-full px-4 py-3 text-[11px] font-bold text-white bg-[#E31E24] disabled:opacity-40 rounded-lg">Record Attendance</button>
                </form>
            </div>
        </div>
    </div>

    <section class="bg-white rounded-xl border border-neutral-200 p-4 shadow-sm">
        <div class="flex items-center justify-between gap-3 pb-3 border-b border-neutral-200">
            <h2 class="text-[12px] font-bold text-[#0B0B0E]">Verified Attendance Log</h2>
            <span class="text-[10px] font-mono text-neutral-500">Total Check-Ins: {{ $attendances->count() }}</span>
        </div>
        <div class="overflow-x-auto mt-3">
            <table class="w-full min-w-[680px] text-left text-[10px]">
                <thead><tr class="bg-[#111111] text-white"><th class="px-3 py-2.5">Member ID</th><th class="px-3 py-2.5">Member</th><th class="px-3 py-2.5">Membership Type</th><th class="px-3 py-2.5">Date</th><th class="px-3 py-2.5">Time</th><th class="px-3 py-2.5">Method</th><th class="px-3 py-2.5">Verification</th></tr></thead>
                <tbody class="divide-y divide-neutral-200">
                    @forelse($attendances as $attendance)
                        <tr>
                            <td class="px-3 py-2.5 font-mono text-[#E31B24]">{{ $attendance->member_id }}</td>
                            <td class="px-3 py-2.5 font-semibold">{{ optional($attendance->membership)->full_name ?? 'Member' }}</td>
                            <td class="px-3 py-2.5 text-neutral-500">{{ optional($attendance->membership)->plan_type ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-neutral-500">{{ $attendance->checked_in_at?->copy()->setTimezone(config('app.display_timezone'))->format('F j, Y') }}</td>
                            <td class="px-3 py-2.5 font-mono">{{ $attendance->checked_in_at?->copy()->setTimezone(config('app.display_timezone'))->format('g:i:s A') }}</td>
                            <td class="px-3 py-2.5">{{ $attendance->verification_method }}</td>
                            <td class="px-3 py-2.5"><span class="px-2 py-1 rounded bg-emerald-100 text-emerald-800 font-bold">Verified Active</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-8 text-center text-neutral-500">No check-ins have been recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
    let html5QrScanner = null;
    let qrCameraActive = false;
    let qrScanHandled = false;

    function dismissAttendanceSuccess() {
        document.getElementById('attendance-success-notice')?.remove();
    }

    function switchAttendanceMode(mode) {
        dismissAttendanceSuccess();
        document.getElementById('panel-scanner').classList.toggle('hidden', mode !== 'scan');
        document.getElementById('panel-search').classList.toggle('hidden', mode !== 'search');
        document.getElementById('btn-mode-scan').className = mode === 'scan'
            ? 'w-full px-4 py-3 rounded-xl font-bold text-xs text-white bg-[#E31E24] text-left flex justify-between items-center'
            : 'w-full px-4 py-3 rounded-xl font-bold text-xs text-[#0B0B0E] bg-neutral-100 text-left flex justify-between items-center';
        document.getElementById('btn-mode-search').className = mode === 'search'
            ? 'w-full px-4 py-3 rounded-xl font-bold text-xs text-white bg-[#E31E24] text-left flex justify-between items-center'
            : 'w-full px-4 py-3 rounded-xl font-bold text-xs text-[#0B0B0E] bg-neutral-100 text-left flex justify-between items-center';
    }

    async function toggleQrScanner() {
        const button = document.getElementById('toggle-qr-camera');
        if (qrCameraActive) {
            try {
                await html5QrScanner.stop();
                qrCameraActive = false;
                qrScanHandled = false;
                button.textContent = 'Activate Camera';
            } catch (error) {
                alert('Unable to stop the camera. Please close this page to release it.');
            }
            return;
        }

        try {
            if (!html5QrScanner) {
                html5QrScanner = new Html5Qrcode("qr-reader");
            }
            qrScanHandled = false;
            await html5QrScanner.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                (decodedText) => {
                    if (!qrScanHandled) {
                        qrScanHandled = true;
                        verifyMemberQuery(decodedText, 'QR Code Scan');
                    }
                }
            );
            qrCameraActive = true;
            button.textContent = 'Stop Camera';
        } catch (error) {
            qrCameraActive = false;
            qrScanHandled = false;
            button.textContent = 'Activate Camera';
            alert('Unable to start the camera. Check camera permissions and try again, or use Search Member.');
        }
    }

    function resetIdentityChecks() {
        ['verify-photo-check', 'verify-name-check', 'verify-dob-check'].forEach((id) => {
            const checkbox = document.getElementById(id);
            if (checkbox) {
                checkbox.checked = false;
                checkbox.disabled = true;
            }
        });
    }

    function resetVerificationCard() {
        document.getElementById('ver-name').textContent = 'Scan or Search Member';
        document.getElementById('ver-id').textContent = 'Member ID: —';
        document.getElementById('ver-dob').textContent = 'Date of Birth: —';
        document.getElementById('ver-type').textContent = '—';
        document.getElementById('ver-status').textContent = '—';
        document.getElementById('ver-status').className = 'font-bold';
        document.getElementById('ver-start').textContent = '—';
        document.getElementById('ver-expires').textContent = '—';
        document.getElementById('ver-remaining').textContent = '—';
        document.getElementById('form-member-id').value = '';
        document.getElementById('ver-photo').removeAttribute('src');
        document.getElementById('ver-photo').classList.add('hidden');
        document.getElementById('ver-avatar').textContent = '';
        document.getElementById('ver-avatar').classList.remove('hidden');
        document.getElementById('ver-status-banner').classList.add('hidden');
        document.getElementById('btn-record-attendance').disabled = true;
        resetIdentityChecks();
    }

    function updateIdentityChecks() {
        ['verify-photo-check', 'verify-name-check', 'verify-dob-check'].forEach((id) => {
            const checkbox = document.getElementById(id);
            checkbox.checked = false;
            checkbox.disabled = false;
        });
    }

    function updateIdentityVerificationState() {
        const checks = ['verify-photo-check', 'verify-name-check', 'verify-dob-check']
            .map((id) => document.getElementById(id));
        const memberId = document.getElementById('form-member-id').value;
        const status = document.getElementById('ver-status').dataset.status;
        const isPartial = status === 'Partial';
        const membershipActive = ['Active', 'Partial'].includes(status);
        const identityConfirmed = Boolean(memberId) && checks.every((checkbox) => checkbox.checked);
        const button = document.getElementById('btn-record-attendance');
        const banner = document.getElementById('ver-status-banner');
        const icon = document.getElementById('ver-status-icon');
        const title = document.getElementById('ver-status-title');
        const description = document.getElementById('ver-status-description');

        button.disabled = !membershipActive || !identityConfirmed;
        if (!memberId || !membershipActive) return;

        banner.className = isPartial
            ? 'rounded-lg border border-[#E9D5A8] bg-[#FBF7E8] p-3.5 text-[#7A6238]'
            : identityConfirmed
                ? 'rounded-lg border border-[#C8DCCB] bg-[#F1F6F2] p-3.5 text-[#365E42]'
                : 'rounded-lg border border-[#E9E4CA] bg-[#FBF7E8] p-3.5 text-[#7A6238]';
        icon.className = isPartial
            ? 'flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#F3EAC5] text-sm font-bold'
            : identityConfirmed
                ? 'flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#DFEBE1] text-sm font-bold'
                : 'flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#F3EAC5] text-sm font-bold';
        icon.textContent = isPartial ? '!' : (identityConfirmed ? '✓' : '!');
        title.textContent = isPartial
            ? (identityConfirmed ? 'Partial membership verified' : 'Partial membership · identity verification required')
            : (identityConfirmed ? 'Membership Verified' : 'Identity verification required');
        description.textContent = isPartial
            ? `Available credit: ₱${Number(document.getElementById('ver-status').dataset.credit).toFixed(2)}. ₱50 is deducted each calendar day. ${identityConfirmed ? 'Identity confirmed; ready to record attendance.' : 'Confirm Photo, Name, and Birth Date before recording attendance.'}`
            : identityConfirmed
                ? 'Identity confirmed: Photo, Name, and Birth Date match. Ready to record attendance.'
                : 'Confirm Photo, Name, and Birth Date before recording attendance.';
    }

    ['verify-photo-check', 'verify-name-check', 'verify-dob-check'].forEach((id) => {
        document.getElementById(id).addEventListener('change', updateIdentityVerificationState);
    });

    let memberSearchTimer = null;
    let memberSearchRequest = 0;
    const manualSearchInput = document.getElementById('manual-search-input');
    const searchFeedback = document.getElementById('manual-search-feedback');
    const searchResults = document.getElementById('manual-search-results');

    manualSearchInput.addEventListener('input', () => {
        dismissAttendanceSuccess();
        clearTimeout(memberSearchTimer);
        memberSearchTimer = setTimeout(() => searchMembers(manualSearchInput.value), 200);
    });

    document.getElementById('manual-search-form').addEventListener('submit', (event) => {
        event.preventDefault();
        clearTimeout(memberSearchTimer);
        searchMembers(manualSearchInput.value);
    });

    searchResults.addEventListener('click', (event) => {
        const memberButton = event.target.closest('[data-member-id]');
        if (!memberButton) return;
        searchResults.classList.add('hidden');
        verifyMemberQuery(memberButton.dataset.memberId, 'Manual Search');
    });

    async function searchMembers(query) {
        const normalizedQuery = query.trim();
        const requestId = ++memberSearchRequest;
        searchResults.replaceChildren();
        if (!normalizedQuery) {
            searchResults.classList.add('hidden');
            searchFeedback.textContent = '';
            searchFeedback.className = 'hidden mt-2 text-xs';
            return;
        }

        searchFeedback.textContent = 'Searching members...';
        searchFeedback.className = 'mt-2 text-xs text-neutral-500';

        try {
            const response = await fetch(`{{ route('attendance.search') }}?q=${encodeURIComponent(normalizedQuery)}`);
            if (!response.ok) throw new Error('Member search failed.');
            const data = await response.json();
            if (requestId !== memberSearchRequest) return;

            if (data.members.length === 0) {
                searchResults.classList.add('hidden');
                searchFeedback.textContent = 'No matching members found.';
                searchFeedback.className = 'mt-2 text-xs text-neutral-500';
                return;
            }

            data.members.forEach((member) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.memberId = member.member_id;
                button.setAttribute('role', 'option');
                button.className = 'w-full flex items-center justify-between gap-3 px-3 py-2.5 text-left hover:bg-neutral-50 focus:bg-neutral-50 focus:outline-none';

                const name = document.createElement('span');
                name.className = 'min-w-0';
                const nameLabel = document.createElement('span');
                nameLabel.className = 'block truncate text-xs font-semibold text-[#111111]';
                nameLabel.textContent = member.full_name;
                const details = document.createElement('span');
                details.className = 'block truncate text-[10px] text-neutral-500';
                details.textContent = `${member.membership_type} · ${member.status}`;
                name.append(nameLabel, details);

                const id = document.createElement('span');
                id.className = 'shrink-0 font-mono text-[10px] font-bold text-[#E31B23]';
                id.textContent = member.member_id;
                button.append(name, id);
                searchResults.append(button);
            });

            searchResults.classList.remove('hidden');
            searchFeedback.textContent = `${data.members.length} matching member${data.members.length === 1 ? '' : 's'} found. Select one to verify.`;
            searchFeedback.className = 'mt-2 text-xs font-semibold text-emerald-700';
        } catch (error) {
            if (requestId !== memberSearchRequest) return;
            searchResults.classList.add('hidden');
            searchFeedback.textContent = 'Could not search for members. Please check your connection and try again.';
            searchFeedback.className = 'mt-2 text-xs font-semibold text-red-600';
        }
    }

    async function verifyMemberQuery(query, method) {
        dismissAttendanceSuccess();
        const banner = document.getElementById('ver-status-banner');
        const btn = document.getElementById('btn-record-attendance');
        const feedback = document.getElementById('manual-search-feedback');
        const searchButton = document.getElementById('manual-search-button');
        const normalizedQuery = query.trim();
        if (method === 'Manual Search') {
            feedback.textContent = 'Searching for member...';
            feedback.className = 'mt-2 text-xs text-neutral-500';
            searchButton.disabled = true;
            searchButton.classList.add('opacity-60');
        }
        btn.disabled = true;
        document.getElementById('form-member-id').value = '';
        banner.classList.add('hidden');
        resetIdentityChecks();

        try {
            const res = await fetch(`{{ route('attendance.verify') }}?q=${encodeURIComponent(normalizedQuery)}`);
            const data = await res.json();
            if (!res.ok || !data.found) {
                if (method === 'Manual Search') {
                    feedback.textContent = data.message || 'No matching member found. Check the name or Member ID and try again.';
                    feedback.className = 'mt-2 text-xs font-semibold text-red-600';
                } else {
                    alert(data.message || 'Member not found');
                }
                return;
            }
            if (method === 'Manual Search') {
                feedback.textContent = 'Member found. Review the verification card.';
                feedback.className = 'mt-2 text-xs font-semibold text-emerald-700';
            }
            document.getElementById('ver-name').textContent = data.full_name;
            const photo = document.getElementById('ver-photo');
            const avatar = document.getElementById('ver-avatar');
            avatar.textContent = data.full_name.split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase();
            avatar.classList.remove('hidden');
            photo.classList.add('hidden');
            if (data.photo_url) {
                photo.onerror = () => {
                    photo.classList.add('hidden');
                    avatar.classList.remove('hidden');
                };
                photo.src = data.photo_url;
                photo.classList.remove('hidden');
                avatar.classList.add('hidden');
            }
            document.getElementById('ver-id').textContent = 'Member ID: ' + data.member_id;
            document.getElementById('ver-dob').textContent = 'Date of Birth: ' + data.date_of_birth;
            document.getElementById('ver-type').textContent = data.membership_type;
            document.getElementById('ver-status').textContent = data.status;
            document.getElementById('ver-status').dataset.status = data.status;
            document.getElementById('ver-status').dataset.credit = data.available_credit ?? 0;
            document.getElementById('ver-status').className = data.status === 'Active'
                ? 'font-bold text-emerald-700'
                : (data.status === 'Partial' ? 'font-bold text-amber-700' : 'font-bold text-red-600');
            document.getElementById('ver-start').textContent = data.start_date;
            document.getElementById('ver-expires').textContent = data.expiration_date;
            document.getElementById('ver-remaining').textContent = data.days_remaining + ' Days';
            document.getElementById('form-member-id').value = data.member_id;
            document.getElementById('form-method').value = method;

            updateIdentityChecks();
            const icon = document.getElementById('ver-status-icon');
            const title = document.getElementById('ver-status-title');
            const description = document.getElementById('ver-status-description');
            banner.classList.remove('hidden');

            if (data.checked_in_today) {
                ['verify-photo-check', 'verify-name-check', 'verify-dob-check'].forEach((id) => {
                    document.getElementById(id).disabled = true;
                });
                banner.className = 'rounded-lg border border-[#E9D5A8] bg-[#FBF7E8] p-3.5 text-[#7A6238]';
                icon.className = 'flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#F3EAC5] text-sm font-bold';
                icon.textContent = '!';
                title.textContent = 'Already checked in today';
                description.textContent = data.checked_in_at
                    ? `Check-in recorded at ${data.checked_in_at} PH time. No additional attendance will be recorded.`
                    : 'No additional attendance will be recorded today.';
                btn.disabled = true;
                if (method === 'Manual Search') {
                    feedback.textContent = 'This member has already checked in today.';
                    feedback.className = 'mt-2 text-xs font-semibold text-amber-700';
                }
                const message = `${data.full_name} (ID: ${data.member_id}) has already checked in today.`;
                if (typeof window.MaxGymAlert === 'function') {
                    window.MaxGymAlert('Already Checked In', message, 'No additional attendance was recorded.', 'error');
                } else {
                    window.alert(message);
                }
                resetVerificationCard();
            } else if (data.can_check_in) {
                updateIdentityVerificationState();
            } else {
                banner.className = 'rounded-lg border border-[#E4CACA] bg-[#FAF2F2] p-3.5 text-[#8B4545]';
                icon.className = 'flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#F0DEDE] text-sm font-bold';
                icon.textContent = '!';
                title.textContent = 'Membership has expired';
                description.textContent = 'Renew the membership before recording attendance.';
                btn.disabled = true;
            }

            if (method === 'Manual Search' && !data.checked_in_today) {
                const card = document.getElementById('member-verification-card');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                card.focus({ preventScroll: true });
            }
        } catch (error) {
            if (method === 'Manual Search') {
                feedback.textContent = 'Could not search for the member. Please check your connection and try again.';
                feedback.className = 'mt-2 text-xs font-semibold text-red-600';
            } else {
                alert('Could not verify the member. Please try again.');
            }
        } finally {
            if (method === 'Manual Search') {
                searchButton.disabled = false;
                searchButton.classList.remove('opacity-60');
            }
        }
    }
</script>
@endsection
