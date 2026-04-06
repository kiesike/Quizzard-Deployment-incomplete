@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-green-700 via-green-600 to-emerald-600 p-6 text-white shadow-xl sm:p-8">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-green-200">
                    Teacher Reports
                </p>
                <h2 class="mt-2 text-3xl font-bold sm:text-4xl">
                    Students
                </h2>
                <p class="mt-2 max-w-2xl text-sm text-green-100 sm:text-base">
                    Review student participation and performance across your classes and quizzes.
                </p>
            </div>
        </div>

        <div class="rounded-3xl bg-white shadow-lg ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-800">Student Performance Report</h3>
                <p class="mt-1 text-sm text-slate-500">
                    This report only includes students enrolled in your classes.
                </p>
            </div>

            @if ($students->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm text-slate-500">No students found.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    First Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Last Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Student ID
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Gender
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Date of Birth
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Contact
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Grade Level
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Section
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Classes Joined
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Quizzes Taken
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Avg Score
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Last Activity
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($students as $student)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-800">
                                            {{ $student->first_name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-800">
                                            {{ $student->surname }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $student->email }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $student->studentProfile?->student_id ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $student->studentProfile?->gender ? ucfirst($student->studentProfile->gender) : '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $student->studentProfile?->date_of_birth?->format('M d, Y') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $student->studentProfile?->contact_number ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $student->studentProfile?->grade_level ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $student->studentProfile?->section ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $student->classes_joined_count }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        {{ $student->quizzes_taken_count }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        @if (!is_null($student->average_score))
                                            {{ number_format($student->average_score, 2) }}%
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">
                                        @if ($student->last_activity)
                                            {{ $student->last_activity->format('M d, Y h:i A') }}
                                        @else
                                            —
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