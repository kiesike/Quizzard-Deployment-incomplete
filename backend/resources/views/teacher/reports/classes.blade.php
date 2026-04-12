@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-green-700 via-green-600 to-emerald-600 p-6 text-white shadow-xl sm:p-8">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-green-200">
                    Teacher Reports
                </p>
                <h2 class="mt-2 text-3xl font-bold sm:text-4xl">
                    Classes
                </h2>
                <p class="mt-2 max-w-2xl text-sm text-green-100 sm:text-base">
                    View a summary of your classes, including student count, quiz assignments,
                    attempts, and average score.
                </p>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-slate-800">Class Reports</h3>
                <p class="mt-1 text-sm text-slate-600">
                    This table shows all classes created by you.
                </p>
            </div>

            @if($classes->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                    <p class="text-sm text-slate-600">No classes found.</p>
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Class Name
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Class Code
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Students
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Quizzes
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Attempts
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Actions
                                    </th>
                                    {{-- <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Avg Score
                                    </th> --}}
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($classes as $class)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 text-sm font-medium text-slate-800">
                                            {{ $class->name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $class->class_code }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $class->students_count }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $class->quizzes_count }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $class->attempts_count }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-600">
                                            <a href="{{ route('teacher.reports.class.detail', $class->id) }}"
                                                class="inline-flex items-center rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">
                                                View Report
                                            </a>
                                            <a href="{{ route('teacher.reports.class.quizzes', $class->id) }}"
                                                class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                                View Quizzes
                                            </a>
                                        </td>
                                        {{-- <td class="px-6 py-4 text-sm text-slate-600">
                                            {{ $class->average_score !== null ? $class->average_score . '%' : 'N/A' }}
                                        </td> --}}
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection