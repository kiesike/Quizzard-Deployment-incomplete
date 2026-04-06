@extends('admin.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 p-8 text-white shadow-2xl">

    <!-- soft glow background -->
    <div class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-emerald-500/20 blur-3xl"></div>
    <div class="absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-indigo-500/20 blur-3xl"></div>

    <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-emerald-300">
    {{ $isSuperAdmin ? 'SuperAdmin Control' : 'Admin Control' }}
</p>
            <h2 class="mt-2 text-3xl font-bold">Account Activation</h2>
            <p class="mt-2 text-sm text-slate-300">
                Manage teacher, student, and admin account access across the Quizzard platform.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl bg-white/10 backdrop-blur-xl border border-white/10 px-4 py-3">
                <p class="text-xs text-slate-300">Total</p>
                <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
            </div>

            <div class="rounded-2xl bg-amber-400/10 backdrop-blur-xl border border-amber-400/20 px-4 py-3">
                <p class="text-xs text-amber-300">Pending</p>
                <p class="text-2xl font-bold text-amber-300">{{ $stats['pending'] }}</p>
            </div>

            <div class="rounded-2xl bg-emerald-400/10 backdrop-blur-xl border border-emerald-400/20 px-4 py-3">
                <p class="text-xs text-emerald-300">Active</p>
                <p class="text-2xl font-bold text-emerald-300">{{ $stats['active'] }}</p>
            </div>

            <div class="rounded-2xl bg-red-400/10 backdrop-blur-xl border border-red-400/20 px-4 py-3">
                <p class="text-xs text-red-300">Deactivated</p>
                <p class="text-2xl font-bold text-red-300">{{ $stats['deactivated'] }}</p>
            </div>
        </div>

    </div>
</div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        ✓
                    </div>
                    <div>
                        <p class="font-semibold text-emerald-800">Success</p>
                        <p class="text-sm text-emerald-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200">
            <div class="flex flex-wrap items-center gap-3">

    <!-- STATUS BUTTONS -->
    <button type="button"
        class="status-filter rounded-xl border px-5 py-2.5 text-sm font-semibold transition {{ $status === 'all' ? 'bg-slate-900 text-white shadow-md border-slate-900' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100' }}"
        data-status="all">
        All Status
    </button>

    <button type="button"
        class="status-filter rounded-xl border px-5 py-2.5 text-sm font-semibold transition {{ $status === 'pending' ? 'bg-amber-500 text-white shadow-md border-amber-500' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100' }}"
        data-status="pending">
        Pending
    </button>

    <button type="button"
        class="status-filter rounded-xl border px-5 py-2.5 text-sm font-semibold transition {{ $status === 'active' ? 'bg-emerald-600 text-white shadow-md border-emerald-600' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100' }}"
        data-status="active">
        Active
    </button>

    <button type="button"
        class="status-filter rounded-xl border px-5 py-2.5 text-sm font-semibold transition {{ $status === 'deactivated' ? 'bg-red-600 text-white shadow-md border-red-600' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100' }}"
        data-status="deactivated">
        Deactivated
    </button>

    <!-- ROLE DROPDOWN -->
    <select id="roleTypeSelect"
        class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100">
        <option value="all" {{ $roleType === 'all' ? 'selected' : '' }}>All Roles</option>
        <option value="teacher" {{ $roleType === 'teacher' ? 'selected' : '' }}>Teacher</option>
        <option value="student" {{ $roleType === 'student' ? 'selected' : '' }}>Student</option>
        @if($isSuperAdmin)
            <option value="admin" {{ $roleType === 'admin' ? 'selected' : '' }}>Admin</option>
        @endif
    </select>

    <!-- FILTER DROPDOWN -->
    <select id="filterBy"
        class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
        <option value="all">All Fields</option>
        <option value="first_name">First Name</option>
        <option value="middle_initial">Middle Initial</option>
        <option value="surname">Surname</option>
    </select>

    <!-- SEARCH -->
    <input type="text"
           id="searchInput"
           value="{{ $search }}"
           placeholder="Search..."
           class="min-w-[220px] flex-1 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm shadow-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">

</div>

            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
                <div id="activationTableContainer" class="bg-white">
                    @include('admin.activation.partials.users_table', ['users' => $users, 'isSuperAdmin' => $isSuperAdmin])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const activationBaseUrl = "{{ route('admin.activation.index') }}";
const searchInput = document.getElementById('searchInput');
const filterBySelect = document.getElementById('filterBy');
const roleTypeSelect = document.getElementById('roleTypeSelect');
const activationTableContainer = document.getElementById('activationTableContainer');
const statusButtons = document.querySelectorAll('.status-filter');

let currentStatus = "{{ $status }}";
let currentFilterBy = "{{ $filterBy ?? 'all' }}";
let currentRoleType = "{{ $roleType ?? 'all' }}";
let searchTimeout;

    filterBySelect.value = currentFilterBy;

    function setActiveStatusButton(status) {
        statusButtons.forEach(button => {
            const buttonStatus = button.dataset.status;

            button.classList.remove(
    'bg-slate-900', 'bg-amber-500', 'bg-emerald-600', 'bg-red-600',
    'bg-white',
    'text-white', 'text-slate-700',
    'shadow-md',
    'border-slate-900', 'border-slate-300', 'border-amber-500', 'border-emerald-600', 'border-red-600'
);

            if (buttonStatus === status) {
    if (status === 'all') {
        button.classList.add('bg-slate-900', 'text-white', 'shadow-md', 'border-slate-900');
    } else if (status === 'pending') {
        button.classList.add('bg-amber-500', 'text-white', 'shadow-md', 'border-amber-500');
    } else if (status === 'active') {
        button.classList.add('bg-emerald-600', 'text-white', 'shadow-md', 'border-emerald-600');
    } else if (status === 'deactivated') {
        button.classList.add('bg-red-600', 'text-white', 'shadow-md', 'border-red-600');
    }
} else {
    button.classList.add('bg-white', 'text-slate-700', 'border-slate-300');
}
        });
    }

    

    function activationSkeleton() {
        return `
            <div class="px-4 py-6">
                <div class="space-y-3">
                    ${Array.from({ length: 5 }).map(() => `
                        <div class="h-6 w-full rounded-lg bg-slate-200 animate-pulse"></div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    async function loadActivationUsers(url) {
        try {
            activationTableContainer.innerHTML = activationSkeleton();

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            activationTableContainer.innerHTML = data.html;
        } catch (error) {
            activationTableContainer.innerHTML = `
                <div class="py-8 text-center text-red-600">
                    Failed to load accounts.
                </div>
            `;
            console.error(error);
        }
    }

    function buildUrl() {
    const search = encodeURIComponent(searchInput.value || '');
    const status = encodeURIComponent(currentStatus || 'all');
    const filterBy = encodeURIComponent(currentFilterBy || 'all');
    const roleType = encodeURIComponent(currentRoleType || 'all');
    return `${activationBaseUrl}?search=${search}&status=${status}&filter_by=${filterBy}&role_type=${roleType}`;
}
    if (roleTypeSelect) {
    roleTypeSelect.addEventListener('change', function () {
        currentRoleType = this.value;
        loadActivationUsers(buildUrl());
    });
}

    statusButtons.forEach(button => {
        button.addEventListener('click', function () {
            currentStatus = this.dataset.status;
            setActiveStatusButton(currentStatus);
            loadActivationUsers(buildUrl());
        });
    });

    filterBySelect.addEventListener('change', function() {
        currentFilterBy = this.value;
        loadActivationUsers(buildUrl());
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(function () {
                loadActivationUsers(buildUrl());
            }, 250);
        });
    }

    document.addEventListener('click', function (e) {
        const paginationLink = e.target.closest('#activationPagination a');

        if (paginationLink) {
            e.preventDefault();
            const url = paginationLink.getAttribute('href');
            if (url) {
                loadActivationUsers(url);
            }
        }
    });

    setActiveStatusButton(currentStatus);

if (roleTypeSelect) {
    roleTypeSelect.value = currentRoleType;
}
</script>
@endpush