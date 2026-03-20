<div class="overflow-x-auto">
    <table class="min-w-full text-sm text-slate-700">
        <thead class="bg-slate-100 text-left text-xs font-bold uppercase tracking-wide text-slate-600">
            <tr>
                <th class="px-4 py-4">ID</th>
                <th class="px-4 py-4">Name</th>
                <th class="px-4 py-4">Email</th>
                <th class="px-4 py-4">Role</th>
                <th class="px-4 py-4">Status</th>
                <th class="px-4 py-4">Action</th>
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
                        @if($user->status === 'active')
                            <form method="POST" action="{{ route('admin.activation.deactivate', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center rounded-xl bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700">
                                    Deactivate
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.activation.activate', $user) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                    Activate
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                        No accounts found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($users->hasPages())
    <div id="activationPagination" class="border-t border-slate-200 bg-slate-50 px-4 py-4">
        {{ $users->links() }}
    </div>
@endif