<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-8">
        <h1 class="text-2xl font-bold mb-6 text-center">Teacher Login</h1>

        @if($errors->any())
            <div class="mb-4 rounded bg-red-100 text-red-700 px-4 py-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('teacher.login.submit') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block mb-1 font-medium">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="w-full border rounded px-4 py-2"
                >
            </div>

            <div>
                <label class="block mb-1 font-medium">Password</label>
                <input
                    type="password"
                    name="password"
                    class="w-full border rounded px-4 py-2"
                >
            </div>

            <button class="w-full bg-green-600 text-white rounded px-4 py-2 hover:bg-green-700">
                Login
            </button>
        </form>
    </div>
</body>
</html>