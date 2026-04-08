@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-green-700 via-green-600 to-emerald-600 p-6 text-white shadow-xl sm:p-8">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-green-200">
                    Teacher Reports
                </p>
                <h2 class="mt-2 text-3xl font-bold sm:text-4xl">
                    Quizzes
                </h2>
                <p class="mt-2 max-w-2xl text-sm text-green-100 sm:text-base">
                    Review quiz performance, participation, and publishing status across all of your quizzes.
                </p>
            </div>
        </div>

        <div class="rounded-3xl bg-white shadow-lg ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-800">Quiz Performance Report</h3>
                <p class="mt-1 text-sm text-slate-500">
                    This report only includes quizzes created by your account.
                </p>
            </div>

            @if ($quizzes->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm text-slate-500">No quizzes found.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Quiz Title
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Classes Assigned
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Attempts
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Students Attempted
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Avg Score
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($quizzes as $quiz)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-800">
                                            {{ $quiz->title }}
                                        </div>
                                        @if ($quiz->description)
                                            <div class="mt-1 max-w-md text-sm text-slate-500">
                                                {{ $quiz->description }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $quiz->classes_count }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $quiz->attempts_count }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $quiz->students_attempted_count }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        @if (!is_null($quiz->average_score))
                                            {{ number_format($quiz->average_score, 2) }}%
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($quiz->is_published)
                                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                Published
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                                Unpublished
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