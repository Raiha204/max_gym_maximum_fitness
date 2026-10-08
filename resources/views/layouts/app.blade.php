<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MAX GYM — Integrated Management System')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        :root { --gym-red: #e31b23; --gym-ink: #111111; --gym-line: #e1e4e8; }
        body { font-family: 'DM Sans', sans-serif; }
        .font-mono { font-family: 'IBM Plex Mono', monospace !important; }
        .rounded-xl { border-radius: 0.55rem !important; }
        .rounded-2xl { border-radius: 0.7rem !important; }

        /* ===== Static brand background: white / black / red ===== */
        html { background: #f3f3f5; }
        .gym-bg { position: fixed; inset: 0; z-index: -1; pointer-events: none;
            background:
                linear-gradient(315deg, #111111 0, #111111 150px, #E31B23 150px, #E31B23 164px, transparent 164px),
                repeating-linear-gradient(135deg, rgba(17,17,17,.045) 0, rgba(17,17,17,.045) 1px, transparent 1px, transparent 16px),
                linear-gradient(180deg, #ffffff 0%, #f1f1f4 100%); }
        .gym-bg::before { content: ''; position: absolute; left: 0; right: 0; top: 0; height: 220px;
            background: linear-gradient(180deg, rgba(17,17,17,.07), transparent); }

        /* Content cards float softly above the animated background */
        main .bg-white.rounded-xl, main .bg-white.rounded-2xl { background-color: rgba(255,255,255,.90); -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px);
            box-shadow: 0 1px 2px rgba(17,17,17,.04), 0 12px 28px -16px rgba(17,17,17,.22); }
        input, select, textarea { font: inherit; }
        table tbody tr { transition: background-color 120ms ease; }
        table tbody tr:hover { background-color: #f8f9fa; }
        @media print {
            body * { visibility: hidden; }
            .printable-qr-card, .printable-qr-card * { visibility: visible; }
            .printable-qr-card { position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); }
        }
        @media (max-width: 1023px) {
            .desktop-top-nav { display: none !important; }
        }
        @media (max-width: 767px) {
            .gym-sidebar nav { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        /* ===== Readability: lift the tiny 9-11px type used across pages ===== */
        body .text-\[9px\]  { font-size: 12px !important;   line-height: 1.45 !important; }
        body .text-\[10px\] { font-size: 13px !important;   line-height: 1.5 !important; }
        body .text-\[11px\] { font-size: 14px !important;   line-height: 1.5 !important; }
        body .text-\[12px\] { font-size: 15.5px !important; line-height: 1.4 !important; }
        body .text-xs       { font-size: 13.5px !important; line-height: 1.5 !important; }
        body .text-sm       { font-size: 15px !important; }
        body .text-base     { font-size: 16.5px !important; }
        body .text-lg       { font-size: 22px !important; line-height: 1.25 !important; }
        body .text-xl       { font-size: 26px !important; line-height: 1.2 !important; }
        body main input, body main select, body main textarea { font-size: 14px !important; min-height: 44px; padding: 10px 14px !important; }
        body main textarea { min-height: 96px; }
        body main select { padding-right: 2.4rem !important; }
        body main input[type=checkbox], body main input[type=radio] { min-height: 0; padding: 0 !important; }
        body main table th { font-size: 13px !important; letter-spacing: .01em; }
        body main table td { font-size: 13.5px !important; }

        /* ===== Layout: use the full width, breathe between blocks ===== */
        body main { max-width: 1680px !important; padding: 28px 36px 48px !important; }
        main > div.space-y-4 > * + *, main > div.space-y-6 > * + * { margin-top: 1.6rem !important; }
        main .p-4 { padding: 1.5rem !important; }
        main .p-3\.5 { padding: 1.25rem !important; }
        main .p-3 { padding: 1.05rem !important; }
        main .gap-3 { gap: 1rem !important; }
        main .gap-4 { gap: 1.5rem !important; }
        main .space-y-3 > * + * { margin-top: 1.1rem !important; }
        @media (min-width: 768px) { .gym-sidebar { width: 15rem !important; } .gym-sidebar nav a { font-size: 14px !important; padding-top: 12px; padding-bottom: 12px; } }
        header .text-\[11px\] { font-size: 13.5px !important; }

        /* ===== Motion: blur-in on page load + press feedback ===== */
        @keyframes blurIn { from { opacity: 0; filter: blur(14px); transform: translateY(18px) scale(.985); } to { opacity: 1; filter: blur(0); transform: none; } }
        main > div > *, main > div > .grid > *, main > div > section > .grid > * { animation: blurIn .7s cubic-bezier(.2,.7,.2,1) both; }
        main > div > *:nth-child(2) { animation-delay: .06s; } main > div > *:nth-child(3) { animation-delay: .12s; }
        main > div > *:nth-child(4) { animation-delay: .18s; } main > div > *:nth-child(5) { animation-delay: .24s; }
        main > div > *:nth-child(n+6) { animation-delay: .3s; }
        main > div > .grid > *:nth-child(2) { animation-delay: .14s; } main > div > .grid > *:nth-child(3) { animation-delay: .2s; }
        main > div > .grid > *:nth-child(4) { animation-delay: .26s; } main > div > .grid > *:nth-child(5) { animation-delay: .32s; }
        .equipment-category { animation: blurIn .5s cubic-bezier(.2,.7,.2,1) both; }
        main button, main a[class*="rounded"] { transition: transform .15s ease, box-shadow .2s ease, background-color .2s ease, filter .2s ease; }
        main button:not(:disabled):hover, main a[class*="rounded-lg"]:hover { transform: translateY(-1px); }
        main button:not(:disabled):active, main a[class*="rounded-lg"]:active { transform: scale(.97); }
        @media (prefers-reduced-motion: reduce) { main *, .mg-modal * { animation: none !important; transition: none !important; } }

        /* ===== Selectable option buttons (walk-in type / payment) ===== */
        .choice { position: relative; display: flex; flex-direction: column; justify-content: center; gap: 2px; min-height: 68px; padding: 12px 18px; border: 1.5px solid #D1D5DB; border-radius: 12px; background: #fff; color: #111; text-align: left; cursor: pointer; }
        .choice.choice-sm { min-height: 52px; align-items: center; text-align: center; }
        .choice:hover { border-color: #111; box-shadow: 0 10px 20px -12px rgba(17,17,17,.5); }
        .choice[aria-pressed="true"] { background: #E31B23; border-color: #E31B23; color: #fff; box-shadow: 0 12px 24px -12px rgba(227,27,35,.8); }
        .choice[aria-pressed="true"]::after { content: '\2713'; position: absolute; top: 10px; right: 14px; font-weight: 800; font-size: 14px; }
        .choice-title { font-size: 15px; font-weight: 700; }
        .choice-sub { font-size: 13px; font-family: 'IBM Plex Mono', monospace; color: #E31B23; font-weight: 600; }
        .choice[aria-pressed="true"] .choice-sub { color: #fff; }
        .btn-main { width: 100%; min-height: 52px; font-size: 15px; font-weight: 700; color: #fff; background: #E31B23; border-radius: 12px; box-shadow: 0 12px 24px -12px rgba(227,27,35,.85); }
        .btn-main:hover { background: #c4151c; }

        /* ===== Equipment: balanced two-column flow, roomier rows ===== */
        @media (min-width: 1280px) { #equipment-list:not(.hidden) { display: block; column-count: 2; column-gap: 1.5rem; } }
        .equipment-category { break-inside: avoid; margin-bottom: 1.5rem; }
        .equipment-category > .p-2 { padding: 1rem !important; }
        .equipment-category > .p-2 > * + * { margin-top: .85rem !important; }
        .equipment-item { padding: 1rem 1.1rem !important; }
        .equipment-category > div:first-child { padding: .85rem 1.1rem !important; font-size: 14px !important; }
        .equipment-filter { padding: 8px 16px !important; font-size: 13px !important; }

        /* ===== Logout button ===== */
        .logout-btn { width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; min-height: 48px; padding: 12px 16px; border-radius: 12px; background: #E31B23; color: #fff; font-size: 14.5px; font-weight: 700; box-shadow: 0 12px 24px -14px rgba(227,27,35,.9); transition: background-color .2s ease, transform .15s ease, box-shadow .2s ease; }
        .logout-btn:hover { background: #c4151c; transform: translateY(-1px); }
        .logout-btn:active { transform: scale(.97); }
        .logout-btn:focus-visible { outline: 3px solid #fff; outline-offset: 2px; }

        /* ===== JOptionPane-style popup + toast ===== */
        .mg-modal { position: fixed; inset: 0; z-index: 100; display: none; align-items: center; justify-content: center; padding: 24px; background: rgba(10,10,12,.55); -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px); }
        .mg-modal.open { display: flex; animation: mgFade .25s ease both; }
        .mg-dialog { width: 100%; max-width: 440px; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 30px 80px -20px rgba(0,0,0,.6); animation: mgPop .45s cubic-bezier(.2,1.3,.4,1) both; }
        .mg-dialog-bar { background: #111; color: #fff; padding: 14px 24px; border-bottom: 4px solid #E31B23; font-weight: 700; font-size: 15px; }
        .mg-dialog-body { display: flex; gap: 20px; align-items: center; padding: 28px 28px 22px; }
        .mg-icon { flex: none; width: 60px; height: 60px; border-radius: 50%; display: grid; place-items: center; background: #DCFCE7; color: #166534; }
        .mg-icon.error { background: #FEE2E2; color: #B91C1C; }
        .mg-icon svg { width: 32px; height: 32px; stroke-dasharray: 30; stroke-dashoffset: 30; animation: mgDraw .5s .25s ease forwards; }
        .mg-msg { font-size: 16px; line-height: 1.5; color: #111; font-weight: 600; }
        .mg-sub { font-size: 14px; color: #4B5563; margin-top: 4px; font-weight: 500; }
        .mg-dialog-foot { display: flex; justify-content: flex-end; padding: 0 28px 24px; }
        .mg-ok { min-width: 110px; padding: 11px 26px; border-radius: 10px; background: #E31B23; color: #fff; font-weight: 700; font-size: 15px; box-shadow: 0 10px 20px -10px rgba(227,27,35,.8); }
        .mg-ok:hover { background: #c4151c; } .mg-ok:active { transform: scale(.96); } .mg-ok:focus-visible { outline: 3px solid #111; outline-offset: 2px; }
        .mg-toast { pointer-events: none; position: fixed; right: 28px; bottom: 28px; z-index: 90; display: flex; gap: 12px; align-items: center; padding: 14px 20px; background: #111; color: #fff; border-left: 4px solid #22c55e; border-radius: 12px; font-size: 14px; font-weight: 600; box-shadow: 0 20px 40px -16px rgba(0,0,0,.6); animation: blurIn .5s both, mgOut .4s 3.6s both; }
        @keyframes mgFade { from { opacity: 0; } to { opacity: 1; } }
        @keyframes mgPop { from { opacity: 0; transform: scale(.85) translateY(20px); } to { opacity: 1; transform: none; } }
        @keyframes mgDraw { to { stroke-dashoffset: 0; } }
        @keyframes mgOut { to { opacity: 0; transform: translateY(12px); filter: blur(6px); } }
    </style>
</head>
<body class="text-[#111111] min-h-screen flex flex-col md:flex-row">
    <!-- Background -->
    <div class="gym-bg" aria-hidden="true"></div>
    <!-- Left Sidebar (#111111 with MAX GYM Logo & Icons) -->
    <aside class="gym-sidebar w-full md:w-52 md:min-h-screen md:sticky md:top-0 bg-[#111111] text-white shrink-0 border-b md:border-b-0 md:border-r border-[#2A2A2A] flex flex-col justify-between">
        <div>
            <div class="py-5 px-6 flex flex-col items-center text-center border-b border-[#2A2A2A] bg-[#111111]">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center">
                    <svg viewBox="0 0 200 200" class="w-24 h-24 rounded-xl bg-black border border-[#E31B23]/60 shadow-sm">
                        <rect width="200" height="200" fill="#050507" />
                        <polygon points="100,14 172,55 172,145 100,186 28,145 28,55" fill="#E31B23" stroke="#111111" stroke-width="4" />
                        <path d="M52,96 Q100,82 148,96" stroke="#111111" stroke-width="6" fill="none" />
                        <rect x="38" y="76" width="10" height="38" rx="2" fill="#111111" />
                        <rect x="50" y="72" width="12" height="44" rx="2" fill="#111111" />
                        <rect x="138" y="72" width="12" height="44" rx="2" fill="#111111" />
                        <rect x="152" y="76" width="10" height="38" rx="2" fill="#111111" />
                        <path d="M106,38 L112,60 L118,40 L122,68 C126,78 128,95 132,108 L92,115 L95,94 L76,92 L72,82 L92,68 L102,58 Z" fill="#111111" />
                        <polygon points="14,116 186,116 174,135 186,154 14,154 26,135" fill="#FFFFFF" />
                        <text x="100" y="134" text-anchor="middle" fill="#111111" font-size="19" font-weight="900" font-family="sans-serif">MAX GYM</text>
                        <text x="100" y="148" text-anchor="middle" fill="#111111" font-size="9.5" font-weight="700" letter-spacing="1.5" font-family="sans-serif">MAXIMUM FITNESS</text>
                        <circle cx="84" cy="168" r="3.5" fill="#FFFFFF" />
                        <circle cx="100" cy="165" r="5" fill="#FFFFFF" />
                        <circle cx="116" cy="168" r="3.5" fill="#FFFFFF" />
                    </svg>
                    <span class="font-extrabold tracking-widest text-lg text-white mt-3">MAX GYM</span>
                    <span class="text-[10px] font-bold tracking-[0.2em] text-[#E31B23] uppercase mt-0.5">MAXIMUM FITNESS</span>
                </a>
            </div>

            <nav class="p-3 space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-[#E31B23] text-white' : 'text-[#D1D5DB] hover:text-white hover:bg-[#2A2A2A]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('attendance.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('attendance.*') ? 'bg-[#E31B23] text-white' : 'text-[#D1D5DB] hover:text-white hover:bg-[#2A2A2A]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
                    <span>Attendance & QR</span>
                </a>
                <a href="{{ route('memberships.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('memberships.index', 'memberships.show', 'memberships.edit') ? 'bg-[#E31B23] text-white' : 'text-[#D1D5DB] hover:text-white hover:bg-[#2A2A2A]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Members & Cards</span>
                </a>
                <a href="{{ route('memberships.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('memberships.create') ? 'bg-[#E31B23] text-white' : 'text-[#D1D5DB] hover:text-white hover:bg-[#2A2A2A]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                    <span>Register Member</span>
                </a>
                <a href="{{ route('walk-ins.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('walk-ins.*') ? 'bg-[#E31B23] text-white' : 'text-[#D1D5DB] hover:text-white hover:bg-[#2A2A2A]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
                    <span>Walk-In</span>
                </a>
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('reports.*') ? 'bg-[#E31B23] text-white' : 'text-[#D1D5DB] hover:text-white hover:bg-[#2A2A2A]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg>
                    <span>Revenue Reports</span>
                </a>
                <a href="{{ route('equipment.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('equipment.*') ? 'bg-[#E31B23] text-white' : 'text-[#D1D5DB] hover:text-white hover:bg-[#2A2A2A]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6.5 6.5 11 11"/><path d="m21 21-1-1"/><path d="m3 3 1 1"/><path d="m18 22 4-4"/><path d="m2 6 4-4"/><path d="m3 10 7-7"/><path d="m14 21 7-7"/></svg>
                    <span>Equipment</span>
                </a>
                <a href="{{ route('maintenance.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('maintenance.*') ? 'bg-[#E31B23] text-white' : 'text-[#D1D5DB] hover:text-white hover:bg-[#2A2A2A]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    <span>Maintenance</span>
                </a>
            </nav>
        </div>

        @auth
        <div class="p-4 border-t border-[#2A2A2A]">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/></svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
        @endauth
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Bar -->
        <header class="h-14 bg-[#111111] text-white border-b-2 border-[#E31B23] px-4 sm:px-5 flex items-center justify-between gap-4 sticky top-0 z-30 shadow-sm">
            <div class="flex items-center gap-3">
                <svg viewBox="0 0 200 200" class="w-9 h-9 rounded-xl bg-black border border-neutral-800 shrink-0">
                    <rect width="200" height="200" fill="#050507" />
                    <polygon points="100,14 172,55 172,145 100,186 28,145 28,55" fill="#E31B23" stroke="#111111" stroke-width="4" />
                    <path d="M52,96 Q100,82 148,96" stroke="#111111" stroke-width="6" fill="none" />
                    <rect x="38" y="76" width="10" height="38" rx="2" fill="#111111" />
                    <rect x="50" y="72" width="12" height="44" rx="2" fill="#111111" />
                    <rect x="138" y="72" width="12" height="44" rx="2" fill="#111111" />
                    <rect x="152" y="76" width="10" height="38" rx="2" fill="#111111" />
                    <path d="M106,38 L112,60 L118,40 L122,68 C126,78 128,95 132,108 L92,115 L95,94 L76,92 L72,82 L92,68 L102,58 Z" fill="#111111" />
                    <polygon points="14,116 186,116 174,135 186,154 14,154 26,135" fill="#FFFFFF" />
                    <text x="100" y="134" text-anchor="middle" fill="#111111" font-size="19" font-weight="900" font-family="sans-serif">MAX GYM</text>
                </svg>
                <span class="text-sm font-bold tracking-tight text-white">MAX GYM Management</span>
            </div>
            <nav class="desktop-top-nav flex items-center gap-5 text-[11px] font-semibold text-neutral-300" aria-label="Primary navigation">
                <a href="{{ route('dashboard') }}" class="py-4 border-b-2 {{ request()->routeIs('dashboard') ? 'border-[#E31B23] text-[#FF4B52]' : 'border-transparent hover:text-white' }}">Dashboard</a>
                <a href="{{ route('attendance.index') }}" class="py-4 border-b-2 {{ request()->routeIs('attendance.*') ? 'border-[#E31B23] text-[#FF4B52]' : 'border-transparent hover:text-white' }}">Attendance Scanner</a>
                <a href="{{ route('memberships.index') }}" class="py-4 border-b-2 {{ request()->routeIs('memberships.index', 'memberships.show', 'memberships.edit') ? 'border-[#E31B23] text-[#FF4B52]' : 'border-transparent hover:text-white' }}">Members</a>
                <a href="{{ route('memberships.create') }}" class="py-4 border-b-2 {{ request()->routeIs('memberships.create') ? 'border-[#E31B23] text-[#FF4B52]' : 'border-transparent hover:text-white' }}">Register Member</a>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('attendance.index') }}" class="hidden sm:inline-flex px-3 py-2 text-[11px] font-semibold text-[#111111] bg-white hover:bg-[#F3F4F6] border border-[#D1D5DB] rounded-lg">Open Scanner</a>
                <a href="{{ route('memberships.create') }}" class="px-3 py-2 text-[11px] font-semibold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">+ Register Member</a>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-5 lg:p-6 max-w-[1280px] w-full mx-auto">
            @if(session('success') && !session('popup'))
                <div class="mg-toast" role="status"><span style="color:#22c55e">&#10003;</span> {{ session('success') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
    <!-- JOptionPane-style dialog -->
    <div id="mg-modal" class="mg-modal" role="alertdialog" aria-modal="true" aria-labelledby="mg-title" aria-describedby="mg-msg">
        <div class="mg-dialog">
            <div class="mg-dialog-bar" id="mg-title">Success</div>
            <div class="mg-dialog-body">
                <div class="mg-icon" id="mg-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div>
                <div><div class="mg-msg" id="mg-msg"></div><div class="mg-sub" id="mg-sub"></div></div>
            </div>
            <div class="mg-dialog-foot"><button type="button" class="mg-ok" id="mg-ok">OK</button></div>
        </div>
    </div>
    <script>
        window.MaxGymAlert = function (title, message, sub, type) {
            const m = document.getElementById('mg-modal');
            document.getElementById('mg-title').textContent = title || 'Success';
            document.getElementById('mg-msg').textContent = message || '';
            document.getElementById('mg-sub').textContent = sub || '';
            document.getElementById('mg-icon').classList.toggle('error', type === 'error');
            m.classList.add('open');
            document.getElementById('mg-ok').focus();
        };
        (function () {
            const m = document.getElementById('mg-modal');
            const close = () => m.classList.remove('open');
            document.getElementById('mg-ok').addEventListener('click', close);
            m.addEventListener('click', (e) => { if (e.target === m) close(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
            @if(session('popup'))
                MaxGymAlert(@json(session('popup.title')), @json(session('popup.message')), @json(session('popup.sub')));
            @endif
        })();
    </script>
</body>
</html>
