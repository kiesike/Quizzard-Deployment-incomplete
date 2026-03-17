@extends('admin.layouts.app')

@section('title', 'Classes')

@section('content')
<div class="space-y-6">
    <!-- Hero -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 p-6 md:p-8 text-white shadow-lg">
        <div class="relative z-10">
            <h1 class="text-2xl md:text-3xl font-bold">Classes Management</h1>
            <p class="mt-2 text-sm md:text-base text-white/90">
                View, update, and manage all classes across the platform.
            </p>
        </div>
        <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-white/10"></div>
    </div>

    <!-- Widgets -->
    <div id="classWidgets" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-200">
            <p class="text-sm font-medium text-slate-500">Active Teachers</p>
            <h2 id="activeTeachersCount" class="mt-2 text-3xl font-bold text-slate-800">{{ $activeTeachers }}</h2>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-200">
            <p class="text-sm font-medium text-slate-500">Total Students</p>
            <h2 id="studentsCount" class="mt-2 text-3xl font-bold text-slate-800">{{ $studentsCount }}</h2>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-200">
            <p class="text-sm font-medium text-slate-500">Classes</p>
            <h2 id="classesCount" class="mt-2 text-3xl font-bold text-slate-800">{{ $classesCount }}</h2>
        </div>

        <div class="rounded-2xl bg-white p-5 shadow-sm border border-slate-200">
    <p class="text-sm font-medium text-slate-500">Total Enrollments</p>
    <h2 class="mt-2 text-3xl font-bold text-slate-800">{{ $totalEnrollments }}</h2>
</div>
    </div>

    <!-- Controls -->
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="w-full lg:max-w-md">
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Search by class title or teacher..."
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div class="w-full lg:w-auto">
                <select
                    id="sortFilter"
                    class="w-full lg:w-48 rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="latest">Latest</option>
                    <option value="oldest">Oldest</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <div id="classesTableWrapper">
                @include('admin.classes.partials.table', ['classes' => $classes])
            </div>
        </div>
    </div>
</div>

@include('admin.classes.modals.view')
@include('admin.classes.modals.edit')
@include('admin.classes.modals.delete')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const sortFilter = document.getElementById('sortFilter');
    const tableWrapper = document.getElementById('classesTableWrapper');

    const viewModal = document.getElementById('viewClassModal');
    const editModal = document.getElementById('editClassModal');
    const deleteModal = document.getElementById('deleteClassModal');

    const editForm = document.getElementById('editClassForm');
    const deleteForm = document.getElementById('deleteClassForm');

    let searchTimeout = null;
    let currentDeleteId = null;

    function openModal(modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal(modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', function () {
            closeModal(viewModal);
            closeModal(editModal);
            closeModal(deleteModal);
        });
    });

    [viewModal, editModal, deleteModal].forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal(modal);
        });
    });

    function fetchClasses(pageUrl = null) {
        const search = searchInput.value;
        const sort = sortFilter.value;

        const url = pageUrl ?? `{{ route('admin.classes') }}?search=${encodeURIComponent(search)}&sort=${encodeURIComponent(sort)}`;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            tableWrapper.innerHTML = html;
            bindTableActions();
        });
    }

    function refreshCounts() {
        fetch(`{{ route('admin.classes') }}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(() => {
            const currentSearch = searchInput.value;
            const currentSort = sortFilter.value;

            fetch(`{{ route('admin.classes') }}?search=${encodeURIComponent(currentSearch)}&sort=${encodeURIComponent(currentSort)}`)
                .then(response => response.text())
                .then(() => {
                    window.location.reload();
                });
        });
    }

    function bindTableActions() {
        document.querySelectorAll('.view-class-btn').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.dataset.id;

                fetch(`/admin/classes/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('viewClassName').textContent = data.name ?? '—';
                        document.getElementById('viewClassCode').textContent = data.class_code ?? '—';
                        document.getElementById('viewClassDescription').textContent = data.description ?? '—';
                        document.getElementById('viewClassTeacher').textContent = data.teacher?.name ?? '—';
                        document.getElementById('viewClassStudentCount').textContent = data.students?.length ?? 0;

                        openModal(viewModal);
                    });
            });
        });

        document.querySelectorAll('.edit-class-btn').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.dataset.id;

                fetch(`/admin/classes/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('editClassId').value = data.id;
                        document.getElementById('editClassName').value = data.name ?? '';
                        document.getElementById('editClassDescription').value = data.description ?? '';

                        openModal(editModal);
                    });
            });
        });

        document.querySelectorAll('.delete-class-btn').forEach(button => {
            button.addEventListener('click', function () {
                currentDeleteId = this.dataset.id;
                document.getElementById('deleteClassName').textContent = this.dataset.name;
                openModal(deleteModal);
            });
        });

        document.querySelectorAll('#classesTableWrapper .pagination a').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                fetchClasses(this.href);
            });
        });
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchClasses();
        }, 300);
    });

    sortFilter.addEventListener('change', function () {
        fetchClasses();
    });

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const id = document.getElementById('editClassId').value;
        const formData = new FormData(editForm);

        fetch(`/admin/classes/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-HTTP-Method-Override': 'PUT',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal(editModal);
                fetchClasses();
                setTimeout(() => window.location.reload(), 250);
            }
        });
    });

    deleteForm.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!currentDeleteId) return;

        fetch(`/admin/classes/${currentDeleteId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-HTTP-Method-Override': 'DELETE',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal(deleteModal);
                fetchClasses();
                setTimeout(() => window.location.reload(), 250);
            }
        });
    });

    bindTableActions();
});
</script>
@endsection