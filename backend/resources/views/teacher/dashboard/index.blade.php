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
                        Dashboard
                    </h2>
                    <p class="mt-2 max-w-2xl text-sm text-green-100 sm:text-base">
                        Welcome to the teacher reporting panel. Use the menu on the left
                        to navigate through dashboard and report-related pages.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">Dashboard</h3>
                <p class="mt-2 text-sm text-slate-600">
                    This page will serve as the main overview for your reporting panel.
                </p>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">Reports</h3>
                <p class="mt-2 text-sm text-slate-600">
                    A dedicated reports section will be added next so you can view and export
                    quiz performance data in a desktop-friendly interface.
                </p>
            </div>
        </div>
    </div>
@endsection