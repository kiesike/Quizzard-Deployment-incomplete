@php $isSuperAdmin = isset($isSuperAdmin) ? $isSuperAdmin : (auth()->check() && auth()->user()->role === 'superadmin'); @endphp

@if($isSuperAdmin)
{{-- ===== SUPERADMIN TABLE ===== --}}
<div class="overflow-hidden rounded-xl" style="border:1px solid rgba(255,255,255,0.06);">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left text-xs font-bold uppercase tracking-wide" style="background:rgba(255,255,255,0.03);color:#475569;">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">First Name</th>
                    <th class="px-6 py-4">Middle Initial</th>
                    <th class="px-6 py-4">Surname</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody style="background:#0f1117;">
                @forelse($users as $user)
                    <tr class="transition"
                        style="border-bottom:1px solid rgba(255,255,255,0.04);"
                        onmouseover="this.style.background='rgba(99,102,241,0.05)';"
                        onmouseout="this.style.background='transparent';">
                        <td class="px-6 py-4 font-semibold" style="color:#a5b4fc;">#{{ $user->id }}</td>
                        <td class="px-6 py-4" style="color:#e2e8f0;">{{ $user->first_name ?? '-' }}</td>
                        <td class="px-6 py-4" style="color:#94a3b8;">{{ $user->middle_initial ?? '-' }}</td>
                        <td class="px-6 py-4" style="color:#e2e8f0;">{{ $user->surname ?? '-' }}</td>
                        <td class="px-6 py-4" style="color:#94a3b8;">{{ $user->email }}</td>

                        <td class="px-6 py-4">
                            @php
                                $roleStyle = match($user->role) {
                                    'admin'   => 'background:rgba(139,92,246,0.15);color:#c4b5fd;',
                                    'teacher' => 'background:rgba(99,102,241,0.15);color:#a5b4fc;',
                                    'student' => 'background:rgba(100,116,139,0.15);color:#94a3b8;',
                                    default   => 'background:rgba(100,116,139,0.15);color:#94a3b8;',
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize" style="{{ $roleStyle }}">
                                {{ $user->role }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            @php
                                $statusStyle = match($user->status) {
                                    'active'      => 'background:rgba(16,185,129,0.12);color:#34d399;',
                                    'deactivated' => 'background:rgba(239,68,68,0.12);color:#f87171;',
                                    default       => 'background:rgba(245,158,11,0.12);color:#fbbf24;',
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize" style="{{ $statusStyle }}">
                                {{ $user->status }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if($user->status === 'active')
                                <form method="POST" action="{{ route('admin.activation.deactivate', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="activation-btn inline-flex items-center rounded-lg px-4 py-2 text-xs font-semibold transition disabled:cursor-not-allowed disabled:opacity-70"
                                            style="background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.25);"
                                            onmouseover="this.style.background='rgba(239,68,68,0.28)';"
                                            onmouseout="this.style.background='rgba(239,68,68,0.15)';">
                                        Deactivate
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.activation.activate', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="activation-btn inline-flex items-center rounded-lg px-4 py-2 text-xs font-semibold transition disabled:cursor-not-allowed disabled:opacity-70"
                                            style="background:rgba(16,185,129,0.12);color:#34d399;border:1px solid rgba(16,185,129,0.2);"
                                            onmouseover="this.style.background='rgba(16,185,129,0.22)';"
                                            onmouseout="this.style.background='rgba(16,185,129,0.12)';">
                                        Activate
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-sm" style="color:#475569;">
                            No accounts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($users->hasPages())
    <div id="activationPagination" class="mt-4 rounded-xl px-4 py-4" style="border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.02);">
        {{ $users->links() }}
    </div>
@endif

@else
{{-- ===== ADMIN TABLE — 100% original, zero changes ===== --}}
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
                    <th class="px-6 py-4">Role</th>
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
                                $roleClasses = match($user->role) {
                                    'admin'   => 'bg-violet-100 text-violet-700',
                                    'teacher' => 'bg-blue-100 text-blue-700',
                                    'student' => 'bg-slate-100 text-slate-700',
                                    default   => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $roleClasses }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusClasses = match($user->status) {
                                    'active'      => 'bg-emerald-100 text-emerald-700',
                                    'deactivated' => 'bg-red-100 text-red-700',
                                    default       => 'bg-amber-100 text-amber-700',
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
                                    <button type="submit" class="activation-btn inline-flex items-center rounded-xl bg-red-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-70">
                                        Deactivate
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.activation.activate', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="activation-btn inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70">
                                        Activate
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-500">No accounts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($users->hasPages())
    <div id="activationPagination" class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
        {{ $users->links() }}
    </div>
@endif
@endif