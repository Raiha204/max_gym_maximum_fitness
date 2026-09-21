<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - MAX Gym</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gym: {
                            black: '#0a0a0a',
                            dark: '#161616',
                            red: '#e11d2e',
                            redDark: '#b30d1c',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white text-gym-black min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-60 bg-gym-black text-white flex flex-col shrink-0">
        <div class="px-5 py-6 border-b border-gray-800">
            <div class="text-gym-red font-extrabold text-xl tracking-wide">MAX GYM</div>
            <div class="text-xs text-gray-400">Integrated Management System</div>
        </div>
        <nav class="flex-1 py-4 space-y-1 text-sm">
            @php
                $navItems = [
                    ['route' => 'dashboard', 'label' => 'Dashboard'],
                    ['route' => 'memberships.index', 'label' => 'Members / Memberships'],
                    ['route' => 'attendance.index', 'label' => 'Attendance'],
                    ['route' => 'payments.index', 'label' => 'Payments'],
                    ['route' => 'equipment.index', 'label' => 'Equipment'],
                    ['route' => 'maintenance.index', 'label' => 'Maintenance'],
                ];
            @endphp
            @foreach ($navItems as $item)
                <a href="{{ route($item['route']) }}"
                   class="block px-5 py-2.5 border-l-4 {{ request()->routeIs(explode('.', $item['route'])[0].'*') ? 'border-gym-red bg-gym-dark text-white' : 'border-transparent text-gray-300 hover:bg-gym-dark hover:text-white' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="p-4 border-t border-gray-800">
            <div class="text-xs text-gray-400 mb-2">{{ auth()->user()->name ?? '' }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full text-sm bg-gym-red hover:bg-gym-redDark text-white font-semibold py-2 rounded">
                    Log Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b-4 border-gym-red px-8 py-4 flex items-center justify-between">
            <h1 class="text-lg font-bold text-gym-black">@yield('title', 'Dashboard')</h1>
            <div class="text-xs text-gray-500">{{ now()->format('F d, Y') }}</div>
        </header>

        <main class="flex-1 p-8 bg-gray-50">
            @if (session('success'))
                <div class="mb-4 px-4 py-3 rounded bg-black text-white border-l-4 border-gym-red text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 px-4 py-3 rounded bg-red-50 border-l-4 border-gym-red text-sm text-gym-redDark">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
