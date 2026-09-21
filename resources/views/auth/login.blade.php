<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MAX Gym</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm bg-white rounded-lg shadow-xl overflow-hidden">
        <div class="bg-black py-6 text-center border-b-4 border-red-600">
            <div class="text-red-600 font-extrabold text-2xl tracking-wide">MAX GYM</div>
            <div class="text-gray-400 text-xs mt-1">Integrated Management System</div>
        </div>
        <form method="POST" action="{{ route('login.attempt') }}" class="p-8 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-600">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-600">
            </div>
            <label class="flex items-center text-sm text-gray-600">
                <input type="checkbox" name="remember" class="mr-2"> Remember me
            </label>
            <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 rounded transition">
                Log In
            </button>
            <p class="text-xs text-gray-400 text-center pt-2">Default admin: admin@maxgym.test / password</p>
        </form>
    </div>
</body>
</html>
