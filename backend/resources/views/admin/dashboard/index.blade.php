@extends('admin.layouts.app')

@section('content')
    <h2 class="text-3xl font-bold mb-6">Menu Dashboard</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-sm text-gray-500">Teacher Accounts</p>
            <p class="text-3xl font-bold">{{ $stats['teachers_count'] }}</p>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-sm text-gray-500">Student Accounts</p>
            <p class="text-3xl font-bold">{{ $stats['students_count'] }}</p>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-sm text-gray-500">Activated Accounts</p>
            <p class="text-3xl font-bold">{{ $stats['activated_count'] }}</p>
        </div>

        <div class="bg-white rounded-xl shadow p-5">
            <p class="text-sm text-gray-500">Deactivated Accounts</p>
            <p class="text-3xl font-bold">{{ $stats['deactivated_count'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div class="flex gap-2">
                <a href="{{ route('admin.dashboard', ['type' => 'teacher', 'search' => $search]) }}"
                   class="px-4 py-2 rounded {{ $type === 'teacher' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                    Teachers
                </a>

                <a href="{{ route('admin.dashboard', ['type' => 'student', 'search' => $search]) }}"
                   class="px-4 py-2 rounded {{ $type === 'student' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                    Students
                </a>
            </div>

            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex gap-2">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Search by name or email"
                       class="border rounded px-4 py-2">
                <button class="bg-slate-900 text-white rounded px-4 py-2">Search</button>
            </form>
        </div>

        <div class="mb-4">
            <a href="{{ route('admin.users.create', ['type' => $type]) }}"
               class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Create {{ ucfirst($type) }}
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2 text-left">ID</th>
                        <th class="border px-4 py-2 text-left">Name</th>
                        <th class="border px-4 py-2 text-left">Email</th>
                        <th class="border px-4 py-2 text-left">Role</th>
                        <th class="border px-4 py-2 text-left">Status</th>
                        <th class="border px-4 py-2 text-left">Created</th>
                        <th class="border px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="border px-4 py-2">{{ $user->id }}</td>
                            <td class="border px-4 py-2">{{ $user->name }}</td>
                            <td class="border px-4 py-2">{{ $user->email }}</td>
                            <td class="border px-4 py-2 capitalize">{{ $user->role }}</td>
                            <td class="border px-4 py-2 capitalize">{{ $user->status }}</td>
                            <td class="border px-4 py-2">{{ $user->created_at->format('Y-m-d') }}</td>
                            <td class="border px-4 py-2">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600">View</a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-yellow-600">Edit</a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this account?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="border px-4 py-6 text-center text-gray-500">
                                No {{ $type }} accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
@endsection