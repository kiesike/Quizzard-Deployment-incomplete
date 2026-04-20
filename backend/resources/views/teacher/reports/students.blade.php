@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-8">

        {{-- Page Header --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-700 via-green-600 to-emerald-600 px-6 py-8 text-white shadow-lg sm:px-10 sm:py-10">
            {{-- Decorative Background Elements --}}
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute bottom-0 left-10 h-40 w-40 rounded-full bg-emerald-400/20 blur-2xl"></div>
            </div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-900/40 px-3 py-1.5 text-xs font-medium uppercase tracking-widest text-emerald-100 backdrop-blur-md">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Teacher Reports
                    </div>
                    <h2 class="mt-5 text-3xl font-bold tracking-tight text-white sm:text-4xl">Students</h2>
                    <p class="mt-3 text-base leading-relaxed text-emerald-100">
                        Review student participation and performance across your classes and quizzes in a clearer, easier-to-scan reporting workspace.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-3 backdrop-blur-md">
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200/80">Overview</p>
                        <p class="mt-1 text-sm font-medium text-white">Student Performance</p>
                    </div>
                    <a href="{{ route('teacher.reports.students.export') }}"
                        class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-emerald-700 shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:bg-emerald-50 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/30">
                        <svg class="h-5 w-5 text-emerald-600 transition-colors group-hover:text-emerald-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 2a.75.75 0 01.75.75v7.19l2.22-2.22a.75.75 0 111.06 1.06l-3.5 3.5a.75.75 0 01-1.06 0l-3.5-3.5a.75.75 0 111.06-1.06l2.22 2.22V2.75A.75.75 0 0110 2zm-5.25 11a.75.75 0 01.75.75v.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25v-.5a.75.75 0 011.5 0v.5A2.75 2.75 0 0113.25 17h-6.5A2.75 2.75 0 014 14.25v-.5a.75.75 0 01.75-.75z" clip-rule="evenodd" />
                        </svg>
                        Export to Excel
                    </a>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="overflow-hidden rounded-[2rem] border border-emerald-100 bg-white shadow-sm ring-1 ring-emerald-900/5">
            <div class="border-b border-emerald-100 bg-emerald-50/50 px-6 py-6 sm:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-md bg-emerald-100/80 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            Active Report
                        </div>
                        <h3 class="mt-2 text-xl font-bold text-gray-900">Student Roster</h3>
                        <p class="mt-1 text-sm text-gray-500">Only students currently enrolled in your classes are shown.</p>
                    </div>
                </div>
            </div>

            @if ($students->isEmpty())
                <div class="px-6 py-20 sm:px-8">
                    <div class="mx-auto max-w-md text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50">
                            <svg class="h-8 w-8 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M2 5.75A2.75 2.75 0 014.75 3h10.5A2.75 2.75 0 0118 5.75v8.5A2.75 2.75 0 0115.25 17H4.75A2.75 2.75 0 012 14.25v-8.5zm2.75-1.25A1.25 1.25 0 003.5 5.75v8.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-8.5c0-.69-.56-1.25-1.25-1.25H4.75z" />
                            </svg>
                        </div>
                        <h4 class="mt-5 text-lg font-semibold text-gray-900">No students found</h4>
                        <p class="mt-2 text-sm text-gray-500">There are currently no student records available for this report.</p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table id="studentsTable" class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th onclick="sortTable('studentsTable', 0)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    First Name <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('studentsTable', 1)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Last Name <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('studentsTable', 2)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Student ID <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('studentsTable', 3)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Gender <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('studentsTable', 4)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Date of Birth <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('studentsTable', 5)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Contact <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('studentsTable', 6)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Grade Level <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('studentsTable', 7)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Section <span class="sort-icon">↕</span>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($students as $student)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-800 whitespace-nowrap">{{ $student->first_name }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-800 whitespace-nowrap">{{ $student->surname }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $student->studentProfile?->student_id ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $student->studentProfile?->gender ? ucfirst($student->studentProfile->gender) : '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap" data-value="{{ $student->studentProfile?->date_of_birth?->format('Y-m-d') ?? '' }}">
                                        {{ $student->studentProfile?->date_of_birth?->format('M d, Y') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $student->studentProfile?->contact_number ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $student->studentProfile?->grade_level ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $student->studentProfile?->section ?? '—' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button
                                            onclick="openClassModal({{ $student->id }}, {{ json_encode($student->enrolled_classes) }})"
                                            class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">
                                            View Quiz Info
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <p id="pagination-info" class="text-sm text-slate-500"></p>
                    <div class="flex items-center gap-2">
                        <button id="btn-prev"
                            onclick="currentPage--; paginateTable('studentsTable')"
                            class="rounded-lg border border-slate-200 px-3 py-1 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed">
                            ← Prev
                        </button>
                        <div id="page-numbers" class="flex items-center gap-1"></div>
                        <button id="btn-next"
                            onclick="currentPage++; paginateTable('studentsTable')"
                            class="rounded-lg border border-slate-200 px-3 py-1 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed">
                            Next →
                        </button>
                    </div>
                </div>

                <script>
                    const sortState = {};
                    let currentPage = 1;
                    const rowsPerPage = 10;

                    function paginateTable(tableId) {
                        const table = document.getElementById(tableId);
                        const tbody = table.querySelector('tbody');
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        const totalPages = Math.ceil(rows.length / rowsPerPage);

                        if (currentPage > totalPages) currentPage = totalPages;
                        if (currentPage < 1) currentPage = 1;

                        rows.forEach((row, i) => {
                            const start = (currentPage - 1) * rowsPerPage;
                            const end = start + rowsPerPage;
                            row.style.display = i >= start && i < end ? '' : 'none';
                        });

                        const info = document.getElementById('pagination-info');
                        const total = rows.length;
                        const from = Math.min((currentPage - 1) * rowsPerPage + 1, total);
                        const to = Math.min(currentPage * rowsPerPage, total);
                        if (info) info.textContent = `Showing ${from}–${to} of ${total} students`;

                        document.getElementById('btn-prev').disabled = currentPage === 1;
                        document.getElementById('btn-next').disabled = currentPage === totalPages || totalPages === 0;

                        const pageNumbers = document.getElementById('page-numbers');
                        pageNumbers.innerHTML = '';
                        for (let p = 1; p <= totalPages; p++) {
                            const btn = document.createElement('button');
                            btn.textContent = p;
                            btn.className = p === currentPage
                                ? 'px-3 py-1 rounded-lg text-sm font-semibold bg-green-600 text-white'
                                : 'px-3 py-1 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100';
                            btn.onclick = () => { currentPage = p; paginateTable(tableId); };
                            pageNumbers.appendChild(btn);
                        }
                    }

                    function sortTable(tableId, colIndex) {
                        const table = document.getElementById(tableId);
                        const tbody = table.querySelector('tbody');
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        const icons = table.querySelectorAll('.sort-icon');

                        if (!sortState[tableId]) sortState[tableId] = {};
                        const asc = !sortState[tableId][colIndex];
                        sortState[tableId][colIndex] = asc;

                        icons.forEach((icon) => {
                            icon.textContent = '↕';
                        });
                        icons[colIndex].textContent = asc ? '↑' : '↓';

                        rows.sort((a, b) => {
                            const aCell = a.querySelectorAll('td')[colIndex];
                            const bCell = b.querySelectorAll('td')[colIndex];

                            const aRaw = aCell.dataset.value !== undefined && aCell.dataset.value !== '' ? aCell.dataset.value : aCell.innerText.trim();
                            const bRaw = bCell.dataset.value !== undefined && bCell.dataset.value !== '' ? bCell.dataset.value : bCell.innerText.trim();

                            // Extract trailing number for "Grade X" style values
                            const gradeMatch = (v) => v.match(/^[a-zA-Z\s]+(\d+)$/);
                            const aGrade = gradeMatch(aRaw);
                            const bGrade = gradeMatch(bRaw);
                            if (aGrade && bGrade) {
                                return asc ? parseInt(aGrade[1]) - parseInt(bGrade[1]) : parseInt(bGrade[1]) - parseInt(aGrade[1]);
                            }

                            // Numeric sort
                            // Date sort (must come BEFORE numeric sort to avoid parseFloat eating Y-m-d)
                            const aDate = Date.parse(aRaw);
                            const bDate = Date.parse(bRaw);
                            if (!isNaN(aDate) && !isNaN(bDate) && /\d{4}-\d{2}-\d{2}/.test(aRaw)) {
                                return asc ? aDate - bDate : bDate - aDate;
                            }

                            // Numeric sort
                            const aNum = parseFloat(aRaw);
                            const bNum = parseFloat(bRaw);
                            if (!isNaN(aNum) && !isNaN(bNum)) {
                                return asc ? aNum - bNum : bNum - aNum;
                            }

                            return asc ? aRaw.localeCompare(bRaw) : bRaw.localeCompare(aRaw);
                        });

                        rows.forEach(row => tbody.appendChild(row));
                        currentPage = 1;
                        paginateTable(tableId);
                    }

                    document.addEventListener('DOMContentLoaded', () => paginateTable('studentsTable'));
                </script>
            @endif
        </div>
    </div>

    {{-- Class Picker Modal --}}
    <div id="classModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/40 px-4 py-6 backdrop-blur-sm transition-opacity">
        <div class="relative w-fit min-w-[22rem] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-black/5">
            <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-3.5 sm:px-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-md bg-emerald-100/80 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            Class Selection
                        </div>
                        <h3 class="mt-2 text-base font-bold text-gray-900">Select a Class</h3>
                        <p class="mt-1 text-xs leading-5 text-gray-500">Choose which class to view quiz info for this student.</p>
                    </div>
                    <button onclick="closeClassModal()"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 011.06 0L10 8.94l4.72-4.72a.75.75 0 111.06 1.06L11.06 10l4.72 4.72a.75.75 0 11-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 11-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 010-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="px-4 py-4 sm:px-5">
                <div id="classModalList" class="max-h-64 space-y-2 overflow-y-auto pr-1"></div>

                <button onclick="closeClassModal()"
                    class="mt-4 w-full rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <script>
        function openClassModal(studentId, classes) {
            const list = document.getElementById('classModalList');
            list.innerHTML = '';

            if (!classes.length) {
                list.innerHTML = '<div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-center text-sm text-gray-500">No classes found for this student.</div>';
            } else {
                classes.forEach(cls => {
                    const btn = document.createElement('button');
                    // Updated the JS generated classes to match the cleaner theme
                    btn.className = 'flex w-full items-center justify-between rounded-lg border border-emerald-100 bg-white px-3.5 py-2.5 text-left text-sm font-semibold text-gray-700 shadow-sm transition-all hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent';

                    btn.innerHTML = `
                        <span>${cls.name}</span>
                        <svg class="h-3.5 w-3.5 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                        </svg>
                    `;
                    btn.onclick = () => window.location.href = `/teacher/reports/students/${studentId}/classes/${cls.id}`;
                    list.appendChild(btn);
                });
            }

            document.getElementById('classModal').classList.replace('hidden', 'flex');
        }

        function closeClassModal() {
            document.getElementById('classModal').classList.replace('flex', 'hidden');
        }
    </script>
@endsection
