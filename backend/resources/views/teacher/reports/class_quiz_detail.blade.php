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
                    <a href="{{ route('teacher.reports.class.quizzes', $class->id) }}"
                        class="inline-flex items-center gap-2 rounded-full border border-emerald-300/25 bg-emerald-900/25 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald-100 transition hover:bg-emerald-900/40 hover:text-white">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.56l3.22 3.22a.75.75 0 11-1.06 1.06l-4.5-4.5a.75.75 0 010-1.06l4.5-4.5a.75.75 0 111.06 1.06L5.56 9.25h10.69A.75.75 0 0117 10z" clip-rule="evenodd" />
                        </svg>
                        Back to Class Quizzes
                    </a>
                    <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-900/40 px-3 py-1.5 text-xs font-medium uppercase tracking-widest text-emerald-100 backdrop-blur-md">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Teacher Reports
                    </div>
                    <h2 class="mt-5 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                        {{ $quiz->title }}
                    </h2>
                    <p class="mt-3 max-w-2xl text-base leading-relaxed text-emerald-100">
                        Student results for this quiz in {{ $class->name }}. Only students enrolled in this class are shown.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-3 backdrop-blur-md">
                        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-200/80">Class</p>
                        <p class="mt-1 text-sm font-medium text-white">{{ $class->name }}</p>
                    </div>
                    <a href="{{ route('teacher.reports.class.quiz.detail.export', [$class->id, $quiz->id]) }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-green-700 shadow hover:bg-green-50">
                        ⬇ Export to Excel
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
                            Student Results
                        </div>
                        <h3 class="mt-2 text-xl font-bold text-gray-900">Student Results</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Total points for this quiz: <span class="font-semibold text-gray-700">{{ $totalPoints }}</span>
                        </p>
                    </div>

                    <div class="flex items-center">
                        <div class="rounded-2xl border border-emerald-100 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wide text-emerald-700 shadow-sm">
                            Quiz Performance Overview
                        </div>
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
                        <h4 class="mt-5 text-lg font-semibold text-gray-900">No students enrolled</h4>
                        <p class="mt-2 text-sm text-gray-500">No students are currently enrolled in this class.</p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table id="classQuizDetailTable" class="min-w-full divide-y divide-emerald-100">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th onclick="sortTable('classQuizDetailTable', 0)" class="cursor-pointer whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 hover:bg-slate-100 sm:px-8">
                                    Student ID <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('classQuizDetailTable', 1)" class="cursor-pointer whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 hover:bg-slate-100">
                                    First Name <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('classQuizDetailTable', 2)" class="cursor-pointer whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 hover:bg-slate-100">
                                    Last Name <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('classQuizDetailTable', 3)" class="cursor-pointer whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 hover:bg-slate-100">
                                    Score <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('classQuizDetailTable', 4)" class="cursor-pointer whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 hover:bg-slate-100">
                                    Percentage <span class="sort-icon">↕</span>
                                </th>
                                <th onclick="sortTable('classQuizDetailTable', 5)" class="cursor-pointer whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 hover:bg-slate-100">
                                    Status <span class="sort-icon">↕</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-emerald-50 bg-white">
                            @foreach ($students as $student)
                                <tr class="transition-colors duration-150 hover:bg-emerald-50/50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 sm:px-8">
                                        {{ $student->studentProfile?->student_id ?? 'N/A' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="font-semibold text-gray-900">
                                            {{ $student->first_name }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="font-semibold text-gray-900">
                                            {{ $student->surname }}
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600" data-value="{{ $student->quiz_score ?? -1 }}">
                                        {{ !is_null($student->quiz_score) ? $student->quiz_score : '0' }}/ {{$totalPoints}}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm" data-value="{{ $student->quiz_percentage ?? -1 }}">
                                        @if (!is_null($student->quiz_percentage))
                                            <span @class([
                                                'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset',
                                                'bg-emerald-100 text-emerald-800 ring-emerald-600/20' => $student->quiz_percentage >= 75,
                                                'bg-yellow-100 text-yellow-800 ring-yellow-500/20' => $student->quiz_percentage >= 50 && $student->quiz_percentage < 75,
                                                'bg-red-100 text-red-800 ring-red-500/20' => $student->quiz_percentage < 50,
                                            ])>
                                                {{ number_format($student->quiz_percentage, 2) }}%
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset bg-red-100 text-red-800 ring-red-500/20">0%</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm" data-value="{{ $student->quiz_status }}">
                                        @if ($student->quiz_status === 'Taken')
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-inset ring-emerald-600/20">
                                                Taken
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-300/80">
                                                Not Taken
                                            </span>
                                        @endif
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

                            const aRaw = aCell.dataset.value !== undefined && aCell.dataset.value !== '' ? aCell.dataset.value.trim() : aCell.innerText.trim();
                            const bRaw = bCell.dataset.value !== undefined && bCell.dataset.value !== '' ? bCell.dataset.value.trim() : bCell.innerText.trim();

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
