<div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-slate-700">
            <thead class="bg-slate-100 text-left text-xs font-bold uppercase tracking-wide text-slate-600">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">First Name</th>
                    <th class="px-6 py-4">Middle Initial</th>
                    <th class="px-6 py-4">Surname</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-center">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($users as $user)
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 font-semibold text-slate-800">#{{ $user->id }}</td>
                        <td class="px-6 py-4">{{ $user->first_name ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $user->middle_initial ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $user->surname ?? '-' }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>

                        <td class="px-6 py-4">
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

                        <td class="px-6 py-4 text-center">
                            @if($user->status === 'active')
                                <form method="POST" action="{{ route('admin.activation.deactivate', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="activation-btn inline-flex items-center rounded-xl bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-70"
                                    >
                                        Deactivate
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.activation.activate', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="activation-btn inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70"
                                    >
                                        Activate
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-500">
                            No accounts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($users->hasPages())
    <div id="activationPagination" class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
        {{ $users->links() }}
    </div>
@endif