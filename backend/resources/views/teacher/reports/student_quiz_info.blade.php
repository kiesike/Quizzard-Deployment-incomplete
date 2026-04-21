@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-700 via-green-600 to-emerald-600 px-6 py-8 text-white shadow-lg sm:px-10 sm:py-10">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute bottom-0 left-10 h-40 w-40 rounded-full bg-emerald-400/20 blur-2xl"></div>
            </div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <a href="{{ route('teacher.reports.students') }}"
                        class="inline-flex items-center gap-2 rounded-full border border-emerald-300/25 bg-emerald-900/25 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-100 transition hover:bg-emerald-900/40 hover:text-white">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.56l3.22 3.22a.75.75 0 11-1.06 1.06l-4.5-4.5a.75.75 0 010-1.06l4.5-4.5a.75.75 0 111.06 1.06L5.56 9.25h10.69A.75.75 0 0117 10z" clip-rule="evenodd" />
                        </svg>
                        Back to Students
                    </a>

                    <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-900/40 px-3 py-1.5 text-xs font-medium uppercase tracking-widest text-emerald-100 backdrop-blur-md">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Teacher Reports
                    </div>

                    <h2 class="mt-5 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        {{ $student->first_name }} {{ $student->surname }}
                    </h2>
                    <p class="mt-3 max-w-2xl text-base leading-relaxed text-emerald-100">
                        Quiz performance for class: <span class="font-semibold text-white">{{ $class->name }}</span>
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-3 backdrop-blur-md">
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200/80">Class</p>
                        <p class="mt-1 text-sm font-medium text-white">{{ $class->name }}</p>
                    </div>
                    <a href="{{ route('teacher.reports.student.quiz.info.export', [$student->id, $class->id]) }}"
                        class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-emerald-700 shadow-md transition-all duration-200 hover:-translate-y-0.5 hover:bg-emerald-50 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/30">
                        
                        <svg class="h-4 w-4 text-emerald-600 transition-colors group-hover:text-emerald-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 2a.75.75 0 01.75.75v7.19l2.22-2.22a.75.75 0 111.06 1.06l-3.5 3.5a.75.75 0 01-1.06 0l-3.5-3.5a.75.75 0 111.06-1.06l2.22 2.22V2.75A.75.75 0 0110 2zm-5.25 11a.75.75 0 01.75.75v.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25v-.5a.75.75 0 011.5 0v.5A2.75 2.75 0 0113.25 17h-6.5A2.75 2.75 0 014 14.25v-.5a.75.75 0 01.75-.75z" clip-rule="evenodd" />
                        </svg>
                        Export to Excel
                    </a>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-[2rem] border border-emerald-100 bg-white shadow-sm ring-1 ring-emerald-900/5">
            <div class="border-b border-emerald-100 bg-emerald-50/50 px-6 py-6 sm:px-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-md bg-emerald-100/80 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                            Quiz Report
                        </div>
                        <h3 class="mt-2 text-xl font-bold text-gray-900">Quiz Info</h3>
                        <p class="mt-1 text-sm text-gray-500">Showing all quizzes assigned in this class for this student.</p>
                    </div>

                    <div class="flex items-center">
                        <div class="rounded-2xl border border-emerald-100 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wide text-emerald-700 shadow-sm">
                            Performance Overview
                        </div>
                    </div>
                </div>
            </div>

            @if ($quizzes->isEmpty())
                <div class="px-6 py-20 sm:px-8">
                    <div class="mx-auto max-w-md text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50">
                            <svg class="h-8 w-8 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M3.25 4A2.25 2.25 0 001 6.25v7.5A2.25 2.25 0 003.25 16h13.5A2.25 2.25 0 0019 13.75v-7.5A2.25 2.25 0 0016.75 4H3.25zm0 1.5h13.5c.414 0 .75.336.75.75v.19l-6.56 3.936a1.75 1.75 0 01-1.88 0L2.5 6.44v-.19c0-.414.336-.75.75-.75zm-.75 2.69l5.79 3.475a3.25 3.25 0 003.42 0L17.5 8.19v5.56a.75.75 0 01-.75.75H3.25a.75.75 0 01-.75-.75V8.19z" />
                            </svg>
                        </div>
                        <h4 class="mt-5 text-lg font-semibold text-gray-900">No quizzes found</h4>
                        <p class="mt-2 text-sm text-gray-500">There are no quizzes available for this class yet.</p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table id="studentQuizInfoTable" class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th onclick="sortTable('studentQuizInfoTable', 0)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Quiz Name <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('studentQuizInfoTable', 1)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Score <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('studentQuizInfoTable', 2)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Total <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('studentQuizInfoTable', 3)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Status <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('studentQuizInfoTable', 4)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Date Published <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('studentQuizInfoTable', 5)" class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap hover:bg-slate-100">
                                    Date Completed <span class="sort-icon">↕</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($quizzes as $quiz)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-800 whitespace-nowrap">{{ $quiz->name }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap" data-value="{{ $quiz->score ?? -1 }}">
                                        {{ !is_null($quiz->score) ? number_format($quiz->score, 2) : '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap" data-value="{{ $quiz->total ?? -1 }}">
                                        {{ !is_null($quiz->total) ? number_format($quiz->total, 2) : '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap" data-value="{{ $quiz->status }}">
                                        @if ($quiz->status === 'Taken')
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Taken</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">Not Yet</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap" data-value="{{ $quiz->date_published ? $quiz->date_published->timestamp : '' }}">
                                        {{ $quiz->date_published?->format('M d, Y h:i A') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap" data-value="{{ $quiz->date_completed ? $quiz->date_completed->timestamp : '' }}">
                                        {{ $quiz->date_completed?->format('M d, Y h:i A') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <script>
                    const sortState = {};

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

                            const aRaw = aCell.dataset.value !== undefined ? aCell.dataset.value.trim() : '';
                            const bRaw = bCell.dataset.value !== undefined ? bCell.dataset.value.trim() : '';

                            // Always push empty/null values to the bottom
                            if (aRaw === '' && bRaw === '') return 0;
                            if (aRaw === '') return 1;
                            if (bRaw === '') return -1;

                            const aNum = parseFloat(aRaw);
                            const bNum = parseFloat(bRaw);

                            if (!isNaN(aNum) && !isNaN(bNum)) {
                                return asc ? aNum - bNum : bNum - aNum;
                            }

                            return asc ? aRaw.localeCompare(bRaw) : bRaw.localeCompare(aRaw);
                        });

                        rows.forEach(row => tbody.appendChild(row));
                    }
                </script>
            @endif
        </div>
    </div>
@endsection
