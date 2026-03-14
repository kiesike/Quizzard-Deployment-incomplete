<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzard Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-slate-900 text-white p-6">
            <h1 class="text-2xl font-bold mb-8">Quizzard Admin</h1>

            <nav class="space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded hover:bg-slate-800">Menu</a>
                <a href="{{ route('admin.profile') }}" class="block px-4 py-2 rounded hover:bg-slate-800">Profile</a>
                <a href="{{ route('admin.activation.index') }}" class="block px-4 py-2 rounded hover:bg-slate-800">Activation</a>
                <a href="#" class="block px-4 py-2 rounded hover:bg-slate-800">Quizzes</a>
            </nav>

            <form action="{{ route('admin.logout') }}" method="POST" class="mt-8">
                @csrf
                <button class="w-full bg-red-600 hover:bg-red-700 px-4 py-2 rounded">
                    Logout
                </button>
            </form>
        </aside>

        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="mb-4 rounded bg-green-100 text-green-800 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>