@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-8">

        {{-- Header --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-700 via-green-600 to-emerald-600 px-6 py-8 text-white shadow-lg sm:px-10 sm:py-10">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute bottom-0 left-10 h-40 w-40 rounded-full bg-emerald-400/20 blur-2xl"></div>
            </div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <a href="{{ route('teacher.reports.classes') }}"
                        class="inline-flex items-center gap-2 rounded-full border border-emerald-300/25 bg-emerald-900/25 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-100 transition hover:bg-emerald-900/40 hover:text-white">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.56l3.22 3.22a.75.75 0 11-1.06 1.06l-4.5-4.5a.75.75 0 010-1.06l4.5-4.5a.75.75 0 111.06 1.06L5.56 9.25h10.69A.75.75 0 0117 10z" clip-rule="evenodd" />
                        </svg>
                        Back to Classes
                    </a>
                    <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-900/40 px-3 py-1.5 text-xs font-medium uppercase tracking-widest text-emerald-100 backdrop-blur-md">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Teacher Reports
                    </div>
                    <h2 class="mt-5 text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $class->name }}</h2>
                    <p class="mt-3 max-w-2xl text-base leading-relaxed text-emerald-100">
                        Student performance report for this class. Overall grade is computed based on
                        {{ $totalQuizzes }} assigned {{ Str::plural('quiz', $totalQuizzes) }}.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-3 backdrop-blur-md">
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200/80">Class</p>
                        <p class="mt-1 text-sm font-medium text-white">{{ $class->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="grid gap-4 md:grid-cols-3">
            @php
                $passing = $students->filter(fn($s) => !is_null($s->overall_grade) && $s->overall_grade >= 75)->count();
                $avg = $students->whereNotNull('overall_grade')->avg('overall_grade');
            @endphp
            <div class="rounded-3xl border border-emerald-100 bg-white px-5 py-5 shadow-sm ring-1 ring-emerald-900/5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Students</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $students->count() }}</p>
                <p class="mt-1 text-sm text-gray-500">enrolled</p>
            </div>
            <div class="rounded-3xl border border-emerald-100 bg-white px-5 py-5 shadow-sm ring-1 ring-emerald-900/5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Class Avg</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">
                    {{ $avg ? number_format($avg, 1) . '%' : 'N/A' }}
                </p>
                <p class="mt-1 text-sm text-gray-500">overall grade</p>
            </div>
            {{-- <div class="rounded-3xl border border-emerald-100 bg-white px-5 py-5 shadow-sm ring-1 ring-emerald-900/5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Passing</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ $passing }}</p>
                <p class="mt-1 text-sm text-gray-500">above 75%</p>
            </div> --}}
        </div>

        {{-- Table Card --}}
        <div class="overflow-hidden rounded-[2rem] border border-emerald-100 bg-white shadow-sm ring-1 ring-emerald-900/5">
            <div class="border-b border-emerald-100 bg-emerald-50/50 px-6 py-6 sm:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-md bg-emerald-100/80 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            Student Performance
                        </div>
                        <h3 class="mt-2 text-xl font-bold text-gray-900">Student performance</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Overall grade = sum of scores / total quizzes assigned
                        </p>
                    </div>
                    <a href="{{ route('teacher.reports.class.export', $class->id) }}"
                        class="inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm ring-1 ring-emerald-100 transition hover:bg-emerald-50">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 2a.75.75 0 01.75.75v7.19l2.22-2.22a.75.75 0 111.06 1.06l-3.5 3.5a.75.75 0 01-1.06 0l-3.5-3.5a.75.75 0 111.06-1.06l2.22 2.22V2.75A.75.75 0 0110 2zm-5.25 11a.75.75 0 01.75.75v.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25v-.5a.75.75 0 011.5 0v.5A2.75 2.75 0 0113.25 17h-6.5A2.75 2.75 0 014 14.25v-.5a.75.75 0 01.75-.75z" clip-rule="evenodd" />
                        </svg>
                        Export to Excel
                    </a>
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
                        <h4 class="mt-5 text-lg font-semibold text-gray-900">No students enrolled</h4>
                        <p class="mt-2 text-sm text-gray-500">No students are currently enrolled in this class.</p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table id="classDetailTable" class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th onclick="sortTable('classDetailTable', 0)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Student ID <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('classDetailTable', 1)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    First Name <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('classDetailTable', 2)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Last Name <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('classDetailTable', 3)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Quizzes Taken <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('classDetailTable', 4)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Overall Grade <span class="sort-icon">↕</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($students as $student)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ $student->studentProfile?->student_id ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-slate-800">{{ $student->first_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-slate-800">{{ $student->surname }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ $student->quizzes_taken }} / {{ $totalQuizzes }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap" data-value="{{ $student->overall_grade ?? -1 }}">
                                        @if (!is_null($student->overall_grade))
                                            <span @class([
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                                'bg-green-100 text-green-800' => $student->overall_grade >= 75,
                                                'bg-yellow-100 text-yellow-800' => $student->overall_grade >= 50 && $student->overall_grade < 75,
                                                'bg-red-100 text-red-800' => $student->overall_grade < 50,
                                            ])>
                                                {{ number_format($student->overall_grade, 2) }}%
                                            </span>
                                        @else
                                            <span class="text-slate-400">N/A</span>
                                        @endif
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
                            onclick="currentPage--; paginateTable('classDetailTable')"
                            class="rounded-lg border border-slate-200 px-3 py-1 text-sm font-medium text-slate-600 transition hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed">
                            ← Prev
                        </button>
                        <div id="page-numbers" class="flex items-center gap-1"></div>
                        <button id="btn-next"
                            onclick="currentPage++; paginateTable('classDetailTable')"
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

                        // Update pagination info
                        const info = document.getElementById('pagination-info');
                        const total = rows.length;
                        const from = Math.min((currentPage - 1) * rowsPerPage + 1, total);
                        const to = Math.min(currentPage * rowsPerPage, total);
                        if (info) info.textContent = `Showing ${from}–${to} of ${total} students`;

                        // Update buttons
                        document.getElementById('btn-prev').disabled = currentPage === 1;
                        document.getElementById('btn-next').disabled = currentPage === totalPages || totalPages === 0;

                        // Render page numbers
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

                        icons.forEach((icon, i) => {
                            icon.textContent = '↕';
                        });
                        icons[colIndex].textContent = asc ? '↑' : '↓';

                        rows.sort((a, b) => {
                            const aCell = a.querySelectorAll('td')[colIndex];
                            const bCell = b.querySelectorAll('td')[colIndex];

                            const aVal = aCell.dataset.value !== undefined ? aCell.dataset.value : aCell.innerText.trim();
                            const bVal = bCell.dataset.value !== undefined ? bCell.dataset.value : bCell.innerText.trim();

                            const aNum = parseFloat(aVal);
                            const bNum = parseFloat(bVal);

                            if (!isNaN(aNum) && !isNaN(bNum)) {
                                return asc ? aNum - bNum : bNum - aNum;
                            }

                            return asc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                        });

                        rows.forEach(row => tbody.appendChild(row));
                        currentPage = 1;
                        paginateTable(tableId);
                    }

                    document.addEventListener('DOMContentLoaded', () => paginateTable('classDetailTable'));
                </script>
            @endif
        </div>

    </div>
@endsection
