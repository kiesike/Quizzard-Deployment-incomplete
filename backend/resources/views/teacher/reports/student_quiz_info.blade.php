@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-green-700 via-green-600 to-emerald-600 p-6 text-white shadow-xl sm:p-8">
            <div class="flex items-start justify-between">
                <div>
                    <a href="{{ route('teacher.reports.students') }}"
                        class="mb-3 inline-flex items-center gap-1 text-sm font-medium text-green-200 hover:text-white">
                        ← Back to Students
                    </a>
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-green-200">
                        Teacher Reports
                    </p>
                    <h2 class="mt-2 text-3xl font-bold sm:text-4xl">
                        {{ $student->first_name }} {{ $student->surname }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm text-green-100 sm:text-base">
                        Quiz performance for class: <strong>{{ $class->name }}</strong>
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl bg-white shadow-lg ring-1 ring-slate-200">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Quiz Info</h3>
                    <p class="mt-1 text-sm text-slate-500">Showing all quizzes assigned in this class for this student.</p>
                </div>
                <a href="{{ route('teacher.reports.student.quiz.info.export', [$student->id, $class->id]) }}"
                    class="items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 mr-6 text-xs font-semibold text-white hover:bg-green-700 transition-colors">
                    ↓ Export to Excel
                </a>
            </div>

            @if ($quizzes->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm text-slate-500">No quizzes found for this class.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Quiz Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Score</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Date Published</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">Date Completed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($quizzes as $quiz)
                                <tr class="">
                                    <td class="px-6 py-4 text-sm font-semibold text-slate-800 whitespace-nowrap">{{ $quiz->name }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ !is_null($quiz->score) ? number_format($quiz->score, ) : '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ !is_null($quiz->total) ? number_format($quiz->total, ) : '—' }}</td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">{{$quiz->status}}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $quiz->date_published?->format('M d, Y h:i A') ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">{{ $quiz->date_completed?->format('M d, Y h:i A') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection