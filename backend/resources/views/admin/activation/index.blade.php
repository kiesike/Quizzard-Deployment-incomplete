@extends('admin.layouts.app')

@section('content')
    <h2 class="text-3xl font-bold mb-6">Activation</h2>

    <div class="bg-white rounded-xl shadow p-6">
        <form method="GET" action="{{ route('admin.activation.index') }}" class="mb-6 flex gap-2">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Search pending accounts"
                   class="border rounded px-4 py-2">
            <button class="bg-slate-900 text-white rounded px-4 py-2">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border px-4 py-2 text-left">ID</th>
                        <th class="border px-4 py-2 text-left">Name</th>
                        <th class="border px-4 py-2 text-left">Email</th>
                        <th class="border px-4 py-2 text-left">Role</th>
                        <th class="border px-4 py-2 text-left">Status</th>
                        <th class="border px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingUsers as $user)
                        <tr>
                            <td class="border px-4 py-2">{{ $user->id }}</td>
                            <td class="border px-4 py-2">{{ $user->name }}</td>
                            <td class="border px-4 py-2">{{ $user->email }}</td>
                            <td class="border px-4 py-2 capitalize">{{ $user->role }}</td>
                            <td class="border px-4 py-2 capitalize">{{ $user->status }}</td>
                            <td class="border px-4 py-2">
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('admin.activation.approve', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="bg-green-600 text-white px-3 py-1 rounded">Approve</button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.activation.deactivate', $user) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="bg-red-600 text-white px-3 py-1 rounded">Deactivate</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border px-4 py-6 text-center text-gray-500">
                                No pending accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $pendingUsers->links() }}
        </div>
    </div>
@endsection