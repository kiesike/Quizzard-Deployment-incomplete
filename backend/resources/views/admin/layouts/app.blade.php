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
            @if(session('success'))
                <div
                    id="globalSuccessToast"
                    class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-emerald-800 shadow-sm"
                >
                    <div class="mt-0.5">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.172 7.707 8.879a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>

                    <div class="flex-1">
                        <p class="text-sm font-semibold">Login Successful</p>
                        <p class="text-sm">{{ session('success') }}</p>
                    </div>

                    <button
                        type="button"
                        onclick="document.getElementById('globalSuccessToast').remove()"
                        class="text-emerald-700 transition hover:text-emerald-900"
                    >
                        ×
                    </button>
                </div>

                <script>
                    setTimeout(() => {
                        const toast = document.getElementById('globalSuccessToast');
                        if (toast) {
                            toast.remove();
                        }
                    }, 3500);
                </script>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>