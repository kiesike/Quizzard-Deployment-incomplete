@extends('admin.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-blue-900 p-6 text-white shadow-xl sm:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-blue-200">Admin Panel</p>
                    <h2 class="mt-2 text-3xl font-bold sm:text-4xl">Menu Dashboard</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-200 sm:text-base">
                        Manage teacher and student accounts, review account details, and maintain the Quizzard admin system.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                        <p class="text-xs uppercase tracking-wide text-slate-200">Teachers</p>
                        <p class="mt-1 text-2xl font-bold">{{ $stats['teachers_count'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                        <p class="text-xs uppercase tracking-wide text-slate-200">Students</p>
                        <p class="mt-1 text-2xl font-bold">{{ $stats['students_count'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                        <p class="text-xs uppercase tracking-wide text-slate-200">Activated</p>
                        <p class="mt-1 text-2xl font-bold">{{ $stats['activated_count'] }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-4 py-3 backdrop-blur">
                        <p class="text-xs uppercase tracking-wide text-slate-200">Deactivated</p>
                        <p class="mt-1 text-2xl font-bold">{{ $stats['deactivated_count'] }}</p>
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

        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 shadow-sm">
                <p class="mb-2 font-semibold text-red-800">Please fix the following:</p>
                <ul class="ml-5 list-disc text-sm text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.dashboard', ['type' => 'teacher', 'search' => $search]) }}"
                       class="rounded-xl px-5 py-2.5 text-sm font-semibold transition {{ $type === 'teacher' ? 'bg-blue-700 text-white shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        Teachers
                    </a>

                    <a href="{{ route('admin.dashboard', ['type' => 'student', 'search' => $search]) }}"
                       class="rounded-xl px-5 py-2.5 text-sm font-semibold transition {{ $type === 'student' ? 'bg-blue-700 text-white shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        Students
                    </a>
                </div>

                <div class="flex w-full flex-col gap-3 sm:flex-row xl:w-auto">
                    <select id="filterBy" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        <option value="all">All Fields</option>
                        <option value="first_name">First Name</option>
                        <option value="middle_initial">Middle Initial</option>
                        <option value="surname">Surname</option>
                    </select>
                    
                    <input type="text"
                           id="searchInput"
                           value="{{ $search }}"
                           placeholder="Search by name or email"
                           class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 xl:w-80">

                    @if($type === 'teacher')
                        <button type="button"
                                id="btnCreateTeacher"
                                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                            Create Teacher
                        </button>
                    @endif

                    @if($type === 'student')
                        <button type="button"
                                id="btnCreateStudent"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            Create Student
                        </button>
                    @endif
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
                <div id="usersTableContainer" class="bg-white">
                    @include('admin.dashboard.partials.users_table', ['users' => $users, 'type' => $type])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    {{-- CREATE MODAL --}}
    <div id="createModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
        <div class="relative w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
            <button type="button" class="close-modal absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-500 hover:bg-slate-200 hover:text-slate-700">&times;</button>

            <div class="mb-6">
                <h3 class="text-2xl font-bold text-slate-900" id="createModalTitle">Create Account</h3>
                <p class="mt-1 text-sm text-slate-500">Fill in the account details below.</p>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">First Name</label>
                        <input type="text" name="first_name" id="createFirstName" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Middle Initial</label>
                        <input type="text" name="middle_initial" id="createMiddleInitial" maxlength="1" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Optional">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Surname</label>
                        <input type="text" name="surname" id="createSurname" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Role</label>
                        <select name="role" id="createRole" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                            <option value="teacher">Teacher</option>
                            <option value="student">Student</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
                        <select name="status" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="deactivated">Deactivated</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                    <input type="password" name="password" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="close-modal rounded-xl bg-slate-100 px-5 py-2.5 font-semibold text-slate-700 hover:bg-slate-200">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 font-semibold text-white hover:bg-emerald-700">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- VIEW MODAL --}}
    <div id="viewModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/60 py-8 px-4 backdrop-blur-sm">
        <div class="relative w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl max-h-[calc(100vh-4rem)] overflow-y-auto">
            <div id="viewModalSkeleton" class="hidden absolute inset-0 z-10 flex flex-col items-center justify-center bg-white/80 rounded-3xl">
                <div class="h-10 w-10 animate-spin rounded-full border-4 border-blue-400 border-t-transparent mb-4"></div>
                <div class="h-4 w-40 bg-slate-200 rounded mb-2 animate-pulse"></div>
                <div class="h-4 w-32 bg-slate-200 rounded mb-2 animate-pulse"></div>
                <div class="h-4 w-48 bg-slate-200 rounded mb-2 animate-pulse"></div>
            </div>

            <button type="button" class="close-modal absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-500 hover:bg-slate-200 hover:text-slate-700">&times;</button>

            <div class="mb-6">
                <h3 class="text-2xl font-bold text-slate-900">Account Details</h3>
                <p class="mt-1 text-sm text-slate-500">View account information.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">ID</p>
                    <p id="viewId" class="mt-1 text-base font-semibold text-slate-800"></p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Full Name</p>
                    <p id="viewName" class="mt-1 text-base font-semibold text-slate-800"></p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Email</p>
                    <p id="viewEmail" class="mt-1 text-base font-semibold text-slate-800 break-all"></p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Role</p>
                    <p id="viewRole" class="mt-1 text-base font-semibold capitalize text-slate-800"></p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</p>
                    <p id="viewStatus" class="mt-1 text-base font-semibold capitalize text-slate-800"></p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Password</p>
                    <p id="viewPassword" class="mt-1 text-base font-semibold text-slate-800"></p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Created At</p>
                    <p id="viewCreatedAt" class="mt-1 text-base font-semibold text-slate-800"></p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Updated At</p>
                    <p id="viewUpdatedAt" class="mt-1 text-base font-semibold text-slate-800"></p>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" class="close-modal rounded-xl bg-slate-100 px-5 py-2.5 font-semibold text-slate-700 hover:bg-slate-200">
                    Close
                </button>
            </div>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div id="editModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/60 py-8 px-4 backdrop-blur-sm">
        <div class="relative w-full max-w-xl max-h-[calc(100vh-4rem)] overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
            <div id="editModalSpinner" class="hidden absolute inset-0 z-10 flex items-center justify-center bg-white/80 rounded-3xl">
                <div class="h-10 w-10 animate-spin rounded-full border-4 border-blue-400 border-t-transparent"></div>
            </div>

            <button type="button" class="close-modal absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-500 hover:bg-slate-200 hover:text-slate-700">&times;</button>

            <div class="mb-6">
                <h3 class="text-2xl font-bold text-slate-900">Update Account</h3>
                <p class="mt-1 text-sm text-slate-500">Edit user details and save changes.</p>
            </div>

            <form method="POST" id="editForm" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">First Name</label>
                        <input type="text" name="first_name" id="editFirstName" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Middle Initial</label>
                        <input type="text" name="middle_initial" id="editMiddleInitial" maxlength="1" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Optional">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Surname</label>
                        <input type="text" name="surname" id="editSurname" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" id="editEmail" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Role</label>
                        <select name="role" id="editRole" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                            <option value="teacher">Teacher</option>
                            <option value="student">Student</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
                        <select name="status" id="editStatus" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="deactivated">Deactivated</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">New Password</label>
                    <input type="password" name="password" id="editPassword" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="editPasswordConfirmation" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="close-modal rounded-xl bg-slate-100 px-5 py-2.5 font-semibold text-slate-700 hover:bg-slate-200">
                        Cancel
                    </button>
                    <button id="updateUserBtn" type="submit" class="rounded-xl bg-amber-500 px-5 py-2.5 font-semibold text-white hover:bg-amber-600 transition disabled:opacity-60 disabled:cursor-not-allowed">
                        <span class="flex items-center justify-center gap-2">
                            <span class="spinner hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            <span>Update</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- DELETE MODAL --}}
    <div id="deleteModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
        <div class="relative w-full max-w-lg rounded-3xl border border-red-200 bg-white p-6 shadow-2xl">
            <button type="button" class="close-modal absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-xl text-slate-500 hover:bg-slate-200 hover:text-slate-700">&times;</button>

            <div class="mb-5">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-xl text-red-600">
                    !
                </div>
                <h3 class="text-2xl font-bold text-red-600">Delete Account</h3>
                <p class="mt-2 text-sm text-slate-600">
                    Are you sure you want to delete <strong id="deleteUserName" class="text-slate-900"></strong>?
                </p>
                <p class="mt-2 text-sm text-red-600">This action cannot be undone.</p>
            </div>

            <form method="POST" id="deleteForm" class="flex justify-end gap-3">
                @csrf
                @method('DELETE')

                <button type="button" class="close-modal rounded-xl bg-slate-100 px-5 py-2.5 font-semibold text-slate-700 hover:bg-slate-200">
                    Cancel
                </button>
                <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 font-semibold text-white hover:bg-red-700">
                    Delete
                </button>
            </form>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        const dashboardUrl = "{{ route('admin.dashboard') }}";
        const currentType = "{{ $type }}";

        const searchInput = document.getElementById('searchInput');
        const filterBySelect = document.getElementById('filterBy');
        const usersTableContainer = document.getElementById('usersTableContainer');

        // @ts-ignore
        let currentFilterBy = @json($filterBy ?? 'all');
        if (filterBySelect) {
            filterBySelect.value = currentFilterBy;
        }

        const createModal = document.getElementById('createModal');
        const viewModal = document.getElementById('viewModal');
        const editModal = document.getElementById('editModal');
        const deleteModal = document.getElementById('deleteModal');

        const allModals = [createModal, viewModal, editModal, deleteModal];

        function openModal(modal) {
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal(modal) {
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            const hasVisibleModal = allModals.some(m => m && !m.classList.contains('hidden'));
            if (!hasVisibleModal) {
                document.body.classList.remove('overflow-hidden');
            }
        }

        function closeAllModals() {
            allModals.forEach(modal => closeModal(modal));
        }

        allModals.forEach(modal => {
            if (!modal) return;

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal(modal);
                }
            });
        });

        document.querySelectorAll('.close-modal').forEach(button => {
            button.addEventListener('click', function () {
                const modal = button.closest('#createModal, #viewModal, #editModal, #deleteModal');
                closeModal(modal);
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAllModals();
            }
        });

        const btnCreateTeacher = document.getElementById('btnCreateTeacher');
        const btnCreateStudent = document.getElementById('btnCreateStudent');

        if (btnCreateTeacher) {
            btnCreateTeacher.addEventListener('click', function () {
                document.getElementById('createRole').value = 'teacher';
                document.getElementById('createModalTitle').textContent = 'Create Teacher Account';
                openModal(createModal);
            });
        }

        if (btnCreateStudent) {
            btnCreateStudent.addEventListener('click', function () {
                document.getElementById('createRole').value = 'student';
                document.getElementById('createModalTitle').textContent = 'Create Student Account';
                openModal(createModal);
            });
        }

        // Filter by name parts
        if (filterBySelect) {
            filterBySelect.addEventListener('change', function() {
                currentFilterBy = this.value;
                performSearch();
            });
        }

        // Search input with filter
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);

                searchTimeout = setTimeout(function () {
                    performSearch();
                }, 250);
            });
        }

        function performSearch() {
            const search = searchInput.value;
            const url = `${dashboardUrl}?type=${encodeURIComponent(currentType)}&search=${encodeURIComponent(search)}&filter_by=${encodeURIComponent(currentFilterBy)}`;
            loadUsers(url);
        }

        document.addEventListener('click', async function (e) {
            const viewBtn = e.target.closest('.btn-view-user');
            const editBtn = e.target.closest('.btn-edit-user');
            const deleteBtn = e.target.closest('.btn-delete-user');
            const paginationLink = e.target.closest('#usersPagination a');

            if (viewBtn) {
                openModal(viewModal);

                document.getElementById('viewId').textContent = 'Loading...';
                document.getElementById('viewName').textContent = 'Loading...';
                document.getElementById('viewEmail').textContent = 'Loading...';
                document.getElementById('viewRole').textContent = 'Loading...';
                document.getElementById('viewStatus').textContent = 'Loading...';
                document.getElementById('viewCreatedAt').textContent = 'Loading...';
                document.getElementById('viewUpdatedAt').textContent = 'Loading...';
                document.getElementById('viewPassword').textContent = 'Loading...';

                try {
                    const id = viewBtn.dataset.id;
                    const response = await fetch(`/admin/users/${id}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const user = await response.json();

                    document.getElementById('viewId').textContent = user.id ?? '';
                    document.getElementById('viewName').textContent = user.name ?? '';
                    document.getElementById('viewEmail').textContent = user.email ?? '';
                    document.getElementById('viewRole').textContent = user.role ?? '';
                    document.getElementById('viewStatus').textContent = user.status ?? '';
                    document.getElementById('viewCreatedAt').textContent = user.created_at ?? '';
                    document.getElementById('viewUpdatedAt').textContent = user.updated_at ?? '';
                    document.getElementById('viewPassword').textContent = user.password ?? '';
                } catch (error) {
                    document.getElementById('viewId').textContent = '-';
                    document.getElementById('viewName').textContent = 'Failed to load user';
                    document.getElementById('viewEmail').textContent = '-';
                    document.getElementById('viewRole').textContent = '-';
                    document.getElementById('viewStatus').textContent = '-';
                    document.getElementById('viewCreatedAt').textContent = '-';
                    document.getElementById('viewUpdatedAt').textContent = '-';
                    document.getElementById('viewPassword').textContent = '-';
                    console.error(error);
                }

                return;
            }

            if (editBtn) {
                document.getElementById('editFirstName').value = editBtn.dataset.firstName ?? '';
                document.getElementById('editMiddleInitial').value = editBtn.dataset.middleInitial ?? '';
                document.getElementById('editSurname').value = editBtn.dataset.surname ?? '';
                document.getElementById('editEmail').value = editBtn.dataset.email ?? '';
                document.getElementById('editRole').value = editBtn.dataset.role ?? '';
                document.getElementById('editStatus').value = editBtn.dataset.status ?? '';
                document.getElementById('editPassword').value = '';
                document.getElementById('editPasswordConfirmation').value = '';
                document.getElementById('editForm').action = editBtn.dataset.updateUrl ?? '';

                openModal(editModal);
                return;
            }

            if (deleteBtn) {
                document.getElementById('deleteUserName').textContent = deleteBtn.dataset.name ?? '';
                document.getElementById('deleteForm').action = deleteBtn.dataset.deleteUrl ?? '';

                openModal(deleteModal);
                return;
            }

            if (paginationLink) {
                e.preventDefault();
                const url = paginationLink.getAttribute('href');
                if (url) {
                    loadUsers(url);
                }
            }
        });

        function userSkeleton() {
            return `
                <div class="py-12 text-center">
                    <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-slate-300 border-t-slate-600"></div>
                    <p class="mt-3 text-sm text-slate-600">Loading users...</p>
                </div>
            `;
        }

        async function loadUsers(url) {
            try {
                usersTableContainer.innerHTML = userSkeleton();

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                usersTableContainer.innerHTML = data.html;
            } catch (error) {
                usersTableContainer.innerHTML = `
                    <div class="py-8 text-center text-red-600">
                        Failed to load users.
                    </div>
                `;
                console.error(error);
            }
        }

        // Handle update form submission
        document.getElementById('editForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = this.querySelector('button[type="submit"]');
            setButtonLoading(submitBtn);

            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    loadUsers(dashboardUrl + window.location.search);
                    closeModal(editModal);
                } else {
                    alert(data.message || 'Error updating user');
                }
            } catch (error) {
                alert('Error updating user');
            } finally {
                resetButtonLoading(submitBtn);
            }
        });
    </script>

    <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('close-modal') || e.target.closest('.close-modal')) {
                resetButtonLoading(document.getElementById('createTeacherBtn'));
                resetButtonLoading(document.getElementById('createStudentBtn'));
                resetButtonLoading(document.querySelector('#editForm button[type="submit"]'));
            }
        });
    </script>
@endpush