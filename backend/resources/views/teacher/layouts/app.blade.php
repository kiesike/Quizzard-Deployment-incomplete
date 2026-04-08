<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzard Teacher</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <div class="min-h-screen lg:grid lg:grid-cols-[260px_1fr]">
        <aside class="bg-green-700 text-white shadow-2xl">
            <div class="flex h-full flex-col">
                <div class="border-b border-green-600 px-6 py-6">
                    <h1 class="text-2xl font-bold tracking-wide">Quizzard Teacher</h1>
                    <p class="mt-1 text-sm text-green-100">Reporting Panel</p>
                </div>

                <nav class="flex-1 space-y-2 px-4 py-6">
                    <a href="{{ route('teacher.dashboard') }}"
                       class="block rounded-xl px-4 py-3 text-sm transition
                       {{ request()->routeIs('teacher.dashboard')
                            ? 'text-green-700 font-bold border border-gray-500'
                            : 'text-green-100 hover:bg-green-800 hover:text-white' }}">
                        Dashboard
                    </a>

                    <a href="{{ route('teacher.reports.classes') }}"
                       class="block rounded-xl px-4 py-3 text-sm transition
                       {{ request()->routeIs('teacher.reports.classes')
                            ? 'text-green-700 font-bold border border-gray-500'
                            : 'text-green-100 hover:bg-green-800 hover:text-white' }}">
                        Classes
                    </a>

                    <a href="{{ route('teacher.reports.quizzes') }}"
                       class="block rounded-xl px-4 py-3 text-sm transition
                       {{ request()->routeIs('teacher.reports.quizzes')
                            ? 'text-green-700 font-bold border border-gray-500'
                            : 'text-green-100 hover:bg-green-800 hover:text-white' }}">
                        Quizzes
                    </a>

                    <a href="{{ route('teacher.reports.students') }}"
                       class="block rounded-xl px-4 py-3 text-sm transition
                       {{ request()->routeIs('teacher.reports.students')
                            ? 'text-green-700 font-bold border border-gray-500'
                            : 'text-green-100 hover:bg-green-800 hover:text-white' }}">
                        Students
                    </a>
                </nav>

                <div class="border-t border-green-600 p-4">
                    <form action="{{ route('teacher.logout') }}" method="POST">
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