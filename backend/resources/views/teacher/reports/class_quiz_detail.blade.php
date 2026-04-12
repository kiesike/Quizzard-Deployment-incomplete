@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-green-700 via-green-600 to-emerald-600 p-6 text-white shadow-xl sm:p-8">
            <div>
                <a href="{{ route('teacher.reports.class.quizzes', $class->id) }}"
                    class="mb-3 inline-flex items-center gap-1 text-sm font-medium text-green-200 hover:text-white">
                    ← Back to Class Quizzes
                </a>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-green-200">
                    Teacher Reports
                </p>
                <h2 class="mt-2 text-3xl font-bold sm:text-4xl">
                    {{ $quiz->title }}
                </h2>
                <p class="mt-2 max-w-2xl text-sm text-green-100 sm:text-base">
                    Student results for this quiz in {{ $class->name }}. Only students enrolled in this class are shown.
                </p>
            </div>
        </div>

        <div class="rounded-3xl bg-white shadow-lg ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-800">Student Results</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Total points for this quiz: <span class="font-semibold text-slate-700">{{ $totalPoints }}</span>
                </p>
            </div>

            @if ($students->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm text-slate-500">No students enrolled in this class.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    Student ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    First Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    Last Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    Score
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    Total Points
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    Percentage
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    Status
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
                                        <div class="font-semibold text-slate-800">
                                            {{ $student->first_name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-slate-800">
                                            {{ $student->surname }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ !is_null($student->quiz_score) ? $student->quiz_score : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ $totalPoints }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        @if (!is_null($student->quiz_percentage))
                                            <span @class([
                                                'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                                'bg-green-100 text-green-800' => $student->quiz_percentage >= 75,
                                                'bg-yellow-100 text-yellow-800' => $student->quiz_percentage >= 50 && $student->quiz_percentage < 75,
                                                'bg-red-100 text-red-800' => $student->quiz_percentage < 50,
                                            ])>
                                                {{ number_format($student->quiz_percentage, 2) }}%
                                            </span>
                                        @else
                                            <span class="text-slate-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        @if ($student->quiz_status === 'Taken')
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
                                                Taken
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                                                Not Taken
                                            </span>
                                        @endif
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