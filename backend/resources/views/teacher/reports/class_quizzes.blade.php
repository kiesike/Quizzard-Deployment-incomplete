@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-green-700 via-green-600 to-emerald-600 p-6 text-white shadow-xl sm:p-8">
            <div>
                <a href="{{ route('teacher.reports.classes') }}"
                    class="mb-3 inline-flex items-center gap-1 text-sm font-medium text-green-200 hover:text-white">
                    ← Back to Classes
                </a>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-green-200">
                    Teacher Reports
                </p>
                <h2 class="mt-2 text-3xl font-bold sm:text-4xl">
                    {{ $class->name }}
                </h2>
                <p class="mt-2 max-w-2xl text-sm text-green-100 sm:text-base">
                    All quizzes assigned to this class, including question count, student participation, and publish status.
                </p>
            </div>
        </div>

        <div class="rounded-3xl bg-white shadow-lg ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-800">Quiz Report</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Showing {{ $quizzes->count() }} {{ Str::plural('quiz', $quizzes->count()) }} assigned to this class.
                </p>
            </div>

            @if ($quizzes->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm text-slate-500">No quizzes assigned to this class.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    Quiz Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    Questions
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    Students Taken
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($quizzes as $quiz)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-slate-800">
                                            {{ $quiz->title }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ $quiz->questions_count }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700 whitespace-nowrap">
                                        {{ $quiz->students_taken_count }} / {{ $class->students->count() }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        @if ($quiz->is_published)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">
                                                Published
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">
                                                Unpublished
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        <a href="{{ route('teacher.reports.class.quiz.detail', [$class->id, $quiz->id]) }}"
                                            class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                            View Students
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