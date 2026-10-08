<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login — MAX GYM</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #0B0B0E; }
        .lg-bg { position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                linear-gradient(315deg, #E31B23 0, #E31B23 120px, #ffffff 120px, #ffffff 128px, transparent 128px),
                repeating-linear-gradient(135deg, rgba(255,255,255,.045) 0, rgba(255,255,255,.045) 1px, transparent 1px, transparent 16px),
                linear-gradient(160deg, #1a1a1f 0%, #0B0B0E 60%, #050507 100%); }
        .lg-card { position: relative; z-index: 1; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="lg-bg" aria-hidden="true"></div>
    <div class="lg-card w-full max-w-md bg-white rounded-2xl border-t-4 border-[#E31B23] p-8 shadow-xl">
        <div class="flex flex-col items-center text-center mb-6">
            <svg viewBox="0 0 200 200" class="w-20 h-20 rounded-xl bg-black border-2 border-[#E31B23] shadow-sm mb-3">
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
            <h1 class="text-2xl font-extrabold text-[#111111]">MAX GYM</h1>
            <p class="text-[10px] font-bold tracking-[0.2em] text-[#E31B23] uppercase">MAXIMUM FITNESS</p>
            <p class="text-xs text-neutral-500 mt-1.5">Integrated Management System — Staff Login</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700 font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-neutral-300">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-neutral-300">
            </div>
            <button type="submit" class="w-full py-2.5 text-xs font-bold text-white bg-[#E31B23] hover:bg-[#B51219] rounded-lg">Sign In</button>
        </form>
    </div>
</body>
</html>
