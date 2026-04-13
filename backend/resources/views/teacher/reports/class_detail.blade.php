@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="rounded-3xl bg-green-700 p-6 text-white shadow-xl sm:p-8">
            <a href="{{ route('teacher.reports.classes') }}"
                class="mb-4 inline-flex items-center gap-1 text-xs font-medium text-green-300 hover:text-white">
                ← Back to Classes
            </a>
            <p class="text-xs font-medium uppercase tracking-widest text-green-300">Teacher Reports</p>
            <h2 class="mt-2 text-3xl font-semibold sm:text-4xl">{{ $class->name }}</h2>
            <p class="mt-2 max-w-2xl text-sm text-green-100">
                Student performance report for this class. Overall grade is computed based on
                {{ $totalQuizzes }} assigned {{ Str::plural('quiz', $totalQuizzes) }}.
            </p>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-3 gap-4">
            @php
                $passing = $students->filter(fn($s) => !is_null($s->overall_grade) && $s->overall_grade >= 75)->count();
                $avg = $students->whereNotNull('overall_grade')->avg('overall_grade');
            @endphp
            <div class="rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-100">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Students</p>
                <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $students->count() }}</p>
                <p class="text-xs text-slate-400">enrolled</p>
            </div>
            <div class="rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-100">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Class avg</p>
                <p class="mt-1 text-2xl font-semibold text-slate-800">
                    {{ $avg ? number_format($avg, 1) . '%' : '—' }}
                </p>
                <p class="text-xs text-slate-400">overall grade</p>
            </div>
            <div class="rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-100">
                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Passing</p>
                <p class="mt-1 text-2xl font-semibold text-slate-800">{{ $passing }}</p>
                <p class="text-xs text-slate-400">above 75%</p>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="rounded-3xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Student performance</h3>
                    <p class="mt-0.5 text-xs text-slate-400">
                        Overall grade = sum of scores ÷ total quizzes assigned
                    </p>
                </div>
                <a href="{{ route('teacher.reports.class.export', $class->id) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-green-50 px-4 py-2 text-xs font-semibold text-green-800 hover:bg-green-100">
                    ↓ Export to Excel
                </a>
            </div>

            @if ($students->isEmpty())
                <div class="px-6 py-12 text-center text-sm text-slate-400">
                    No students enrolled in this class.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 whitespace-nowrap">Student ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 whitespace-nowrap">First name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 whitespace-nowrap">Last name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 whitespace-nowrap">Quizzes taken</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 whitespace-nowrap">Overall grade</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400 whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 bg-white">
                            @foreach ($students as $student)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 text-xs font-mono text-slate-400 whitespace-nowrap">
                                        {{ $student->studentProfile?->student_id ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-800 whitespace-nowrap">
                                        {{ $student->first_name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-800 whitespace-nowrap">
                                        {{ $student->surname }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php $pct = $totalQuizzes > 0 ? ($student->quizzes_taken / $totalQuizzes) * 100 : 0; @endphp
                                        <div class="flex items-center gap-3">
                                            <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-green-500" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-xs text-slate-400">
                                                {{ $student->quizzes_taken }} / {{ $totalQuizzes }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if (!is_null($student->overall_grade))
                                            <span @class([
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                                'bg-green-100 text-green-800' => $student->overall_grade >= 75,
                                                'bg-amber-100 text-amber-800' => $student->overall_grade >= 50 && $student->overall_grade < 75,
                                                'bg-red-100 text-red-800' => $student->overall_grade < 50,
                                            ])>
                                                {{ number_format($student->overall_grade, 2) }}%
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-300">N/A</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('teacher.reports.student.quiz.info', [$student->id, $class->id]) }}"
                                            class="inline-flex items-center justify-center rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-green-700 transition">
                                            View Quiz Info
                                        </a>
                                    </td>


                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
@endsection