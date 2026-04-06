@extends('admin.layouts.app')

@section('title', 'Quiz Details')

@section('content')
<div class="space-y-6">

    <!-- Back -->
    <div>
        <a href="{{ route('admin.classes.details', $class->id) }}"
           class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow">
            ← Back to Class
        </a>
    </div>

    <!-- QUIZ HEADER -->
    <div class="rounded-3xl p-6 text-white shadow-xl"
         style="background: linear-gradient(135deg,#020617,#0f172a,#1e1b4b);">

        <h1 class="text-3xl font-bold">{{ $quiz->title }}</h1>
        <p class="mt-2 text-slate-300">
            {{ $quiz->description ?? 'No description' }}
        </p>

        <div class="mt-6 flex flex-wrap gap-4">

            <div class="bg-slate-800/60 px-4 py-2 rounded-xl">
                <p class="text-xs text-slate-400">Attempts</p>
                <p class="text-xl font-bold">{{ $totalAttempts }}</p>
            </div>

            <div class="bg-slate-800/60 px-4 py-2 rounded-xl">
                <p class="text-xs text-slate-400">Avg Score</p>
                <p class="text-xl font-bold">{{ $averageScore }}</p>
            </div>

            <div class="bg-slate-800/60 px-4 py-2 rounded-xl">
                <p class="text-xs text-slate-400">Pass Rate</p>
                <p class="text-xl font-bold">{{ $passRate }}%</p>
            </div>

            <div class="bg-slate-800/60 px-4 py-2 rounded-xl">
                <p class="text-xs text-slate-400">Questions</p>
                <p class="text-xl font-bold">{{ $totalQuestions }}</p>
            </div>

        </div>
    </div>

    <!-- TAB BUTTONS -->
    <div class="flex gap-2">
        <a href="?tab=results"
           class="px-4 py-2 rounded-xl text-sm font-semibold {{ $tab=='results' ? 'bg-indigo-600 text-white' : 'bg-slate-200' }}">
            Results
        </a>

        <a href="?tab=analytics"
           class="px-4 py-2 rounded-xl text-sm font-semibold {{ $tab=='analytics' ? 'bg-indigo-600 text-white' : 'bg-slate-200' }}">
            Analytics
        </a>
    </div>

    <!-- RESULTS TABLE -->
    @if($tab == 'results')
    <div class="bg-white rounded-2xl shadow p-6">

        <div class="flex justify-between mb-4">
            <h2 class="font-bold text-lg">Student Results</h2>

            <a href="?tab=results&export=excel"
               class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                Export Excel
            </a>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-3 text-left">Student</th>
                    <th class="p-3 text-left">Score</th>
                    <th class="p-3 text-left">Total</th>
                    <th class="p-3 text-left">%</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($resultsRows as $row)
                <tr class="border-b">
                    <td class="p-3">{{ $row['student_name'] }}</td>
                    <td class="p-3">{{ $row['score'] }}</td>
                    <td class="p-3">{{ $row['total_points'] }}</td>
                    <td class="p-3">{{ $row['percentage'] }}%</td>
                    <td class="p-3">
                        <span class="{{ $row['status']=='Passed' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $row['status'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- ANALYTICS -->
    @if($tab == 'analytics')
    <div class="bg-white rounded-2xl shadow p-6">

        <h2 class="font-bold text-lg mb-4">Question Analytics</h2>

        <table class="w-full text-sm">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-3 text-left">#</th>
                    <th class="p-3 text-left">Question</th>
                    <th class="p-3 text-left">Correct %</th>
                    <th class="p-3 text-left">Difficulty</th>
                </tr>
            </thead>

            <tbody>
                @foreach($questionAnalytics as $q)
                <tr class="border-b">
                    <td class="p-3">{{ $q['order'] }}</td>
                    <td class="p-3">{{ $q['question_text'] }}</td>
                    <td class="p-3">{{ $q['correct_rate'] }}%</td>
                    <td class="p-3">{{ $q['difficulty'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</div>
@endsection