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
    </style>
</head>
<body class="bg-[#E5E7EB] text-[#111111] min-h-screen flex flex-col md:flex-row">
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
                <a href="{{ route('payments.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('payments.index') ? 'bg-[#E31B23] text-white' : 'text-[#D1D5DB] hover:text-white hover:bg-[#2A2A2A]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    <span>Payments</span>
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
        <div class="p-4 border-t border-[#2A2A2A] flex items-center justify-between text-xs">
            <div class="min-w-0">
                <div class="font-bold text-white truncate">{{ auth()->user()->name }}</div>
                <div class="text-neutral-400 font-mono text-[11px] truncate">{{ auth()->user()->email }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-[#2A2A2A] hover:bg-neutral-700 text-neutral-200 font-semibold">Logout</button>
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

        <main class="flex-1 p-4 sm:p-5 lg:p-6 max-w-[1120px] w-full mx-auto">
            @if(session('success'))
                <div class="mb-5 p-4 rounded-xl bg-[#DCFCE7] border border-[#166534]/30 text-xs font-semibold text-[#166534]">
                    {{ session('success') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
