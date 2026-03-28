@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-green-700 via-green-600 to-emerald-600 p-6 text-white shadow-xl sm:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-medium uppercase tracking-[0.2em] text-green-200">
                        Teacher Panel
                    </p>
                    <h2 class="mt-2 text-3xl font-bold sm:text-4xl">
                        Reporting Dashboard
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm text-green-100 sm:text-base">
                        View detailed reports of your quizzes, analyze student performance,
                        and export results for further use.
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200">
            <h3 class="text-lg font-semibold text-slate-800 mb-2">
                Welcome, Teacher 👋
            </h3>

            <p class="text-sm text-slate-600">
                This is your reporting dashboard. In the next steps, you will be able to:
            </p>

            <ul class="mt-4 list-disc pl-5 text-sm text-slate-600 space-y-1">
                <li>View quiz results in table format</li>
                <li>Filter students and scores</li>
                <li>Analyze performance</li>
                <li>Export reports (CSV / PDF)</li>
            </ul>
        </div>
    </div>
@endsection