<div class="overflow-x-auto">
    <table class="min-w-full text-sm text-slate-700">
        <thead class="bg-slate-100 text-left text-xs font-bold uppercase tracking-wide text-slate-600">
            <tr>
                <th class="px-4 py-4">ID</th>
                <th class="px-4 py-4">Name</th>
                <th class="px-4 py-4">Email</th>
                <th class="px-4 py-4">Role</th>
                <th class="px-4 py-4">Status</th>
                <th class="px-4 py-4">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
            @forelse($users as $user)
                <tr class="transition hover:bg-slate-50">
                    <td class="px-4 py-4 font-medium text-slate-800">{{ $user->id }}</td>
                    <td class="px-4 py-4">{{ $user->name }}</td>
                    <td class="px-4 py-4">{{ $user->email }}</td>
                    <td class="px-4 py-4">
                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold capitalize text-blue-700">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        @php
                            $statusClasses = match($user->status) {
                                'active' => 'bg-emerald-100 text-emerald-700',
                                'deactivated' => 'bg-red-100 text-red-700',
                                default => 'bg-amber-100 text-amber-700',
                            };
                        @endphp

                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $statusClasses }}">
                            {{ $user->status }}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="btn-view-user inline-flex items-center rounded-xl bg-blue-700 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-800"
                                data-id="{{ $user->id }}">
                                View
                            </button>

                            <button
                                type="button"
                                class="btn-edit-user inline-flex items-center rounded-xl bg-amber-500 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-600"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}"
                                data-status="{{ $user->status }}"
                                data-update-url="{{ route('admin.users.update', $user) }}">
                                Update
                            </button>

                            <button
                                type="button"
                                class="btn-delete-user inline-flex items-center rounded-xl bg-red-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700"
                                data-name="{{ $user->name }}"
                                data-delete-url="{{ route('admin.users.destroy', $user) }}">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                        No users found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($users->hasPages())
    <div id="usersPagination" class="border-t border-slate-200 bg-slate-50 px-4 py-4">
        {{ $users->links() }}
    </div>
@endif