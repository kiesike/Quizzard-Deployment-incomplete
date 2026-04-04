<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ auth()->check() && auth()->user()->role === 'superadmin' ? 'Quizzard SuperAdmin' : 'Quizzard Admin' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
        <aside class="bg-slate-900 text-white shadow-2xl">
            <div class="flex h-full flex-col">
                <div class="border-b border-slate-800 px-6 py-6">
                    <h1 class="text-2xl font-bold tracking-wide">
    {{ auth()->check() && auth()->user()->role === 'superadmin' ? 'Quizzard SuperAdmin' : 'Quizzard Admin' }}
</h1>
<p class="mt-1 text-sm text-slate-400">
    {{ auth()->check() && auth()->user()->role === 'superadmin' ? 'SuperAdmin Management Panel' : 'Admin Management Panel' }}
</p>
                </div>

                <nav class="flex-1 space-y-2 px-4 py-6">
                    <a href="{{ route('admin.dashboard') }}"
                       class="block rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('admin.dashboard*') ? 'bg-blue-600 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                        Menu Dashboard
                    </a>

                    <a href="{{ route('admin.profile') }}"
                       class="block rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('admin.profile*') ? 'bg-blue-600 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                        Profile
                    </a>

                    <a href="{{ route('admin.activation.index') }}"
                       class="block rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('admin.activation*') ? 'bg-blue-600 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                        Activation
                    </a>

                    <a href="{{ route('admin.classes') }}"
                       class="block rounded-xl px-4 py-3 text-sm font-medium transition {{ request()->routeIs('admin.classes*') ? 'bg-blue-600 text-white' : 'text-slate-200 hover:bg-slate-800' }}">
                        Classes
                    </a>
                </nav>

                <div class="border-t border-slate-800 p-4">
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-700">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="min-w-0 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>