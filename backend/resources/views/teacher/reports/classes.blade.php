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
                    This page will contain class-based reporting tools for teachers.
                </p>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">Coming Next</h3>
            <p class="mt-2 text-sm text-slate-600">
                The class reports feature will be implemented in DOP-57.
            </p>
        </div>
    </div>
@endsection