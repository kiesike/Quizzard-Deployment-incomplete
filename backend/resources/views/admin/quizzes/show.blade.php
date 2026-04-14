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

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="font-bold text-lg text-slate-800">Student Results</h2>
        </div>

        <div class="flex items-center gap-3">
            <select
                id="resultsSortDropdown"
                class="rounded-xl border border-slate-300 px-4 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
            >
                <option value="newest" selected>Newest to Oldest</option>
                <option value="ranking">Ranking</option>
                <option value="surname">Surname</option>
            </select>

            <a href="?tab=results&export=excel"
               class="bg-green-600 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow hover:bg-green-700 transition">
                Export Excel
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wider">
                <tr>
                    <th class="p-3 text-left">Rank</th>
                    <th class="p-3 text-left">Student ID</th>
                    <th class="p-3 text-left">Surname</th>
                    <th class="p-3 text-left">First Name</th>
                    <th class="p-3 text-left">M.I.</th>
                    <th class="p-3 text-left">Gender</th>
                    <th class="p-3 text-left">Grade</th>
                    <th class="p-3 text-left">Section</th>
                    <th class="p-3 text-left">Score</th>
                    <th class="p-3 text-left">Total</th>
                    <th class="p-3 text-left">%</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>

            <tbody id="resultsTableBody">
                @foreach($resultsRows as $index => $row)
                @php
                    $rowClass = 'hover:bg-slate-50 transition';

                    if ($index === 0) {
    $rowClass .= ' bg-yellow-50 ring-1 ring-yellow-200 shadow-sm';
} elseif ($index === 1) {
    $rowClass .= ' bg-slate-50 ring-1 ring-slate-200 shadow-sm';
} elseif ($index === 2) {
    $rowClass .= ' bg-amber-50 ring-1 ring-amber-200 shadow-sm';
}
                @endphp

                <tr class="{{ $rowClass }}">
                    <td class="p-3 font-bold text-slate-700">{{ $index + 1 }}</td>
                    <td class="p-3">{{ $row['student_id'] }}</td>
                    <td class="p-3">{{ $row['surname'] }}</td>
                    <td class="p-3">{{ $row['first_name'] }}</td>
                    <td class="p-3">{{ $row['middle_initial'] }}</td>
                    <td class="p-3 capitalize">{{ $row['gender'] }}</td>
                    <td class="p-3">{{ $row['grade_level'] }}</td>
                    <td class="p-3">{{ $row['section'] }}</td>
                    <td class="p-3 font-semibold">{{ $row['score'] }}</td>
                    <td class="p-3">{{ $row['total_points'] }}</td>
                    <td class="p-3">{{ $row['percentage'] }}%</td>
                    <td class="p-3">
                        <span class="{{ $row['status']=='Passed' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold' }}">
                            {{ $row['status'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

    <!-- ANALYTICS -->
@if($tab == 'analytics')
<div class="space-y-6">

    

    <!-- Detailed Table -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">

        <div class="mb-4">
            <h2 class="font-bold text-lg text-slate-800">Question Analytics</h2>
            <p class="text-sm text-slate-500">
                Performance breakdown per question
            </p>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-100 shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wider">
            <tr>
                <th class="px-4 py-3 text-left">#</th>
                <th class="px-4 py-3 text-left">Question</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-left">Points</th>
                <th class="px-4 py-3 text-left">Correct %</th>
                <th class="px-4 py-3 text-left">Correct</th>
                <th class="px-4 py-3 text-left">Wrong</th>
                <th class="px-4 py-3 text-left">Avg Points</th>
                <th class="px-4 py-3 text-left">Status</th>
            </tr>
        </thead>

        <tbody>
            @foreach($questionAnalytics as $q)
            @php
                $wrongCount = $q['attempted_count'] - $q['correct_count'];

                if ($q['correct_rate'] >= 80) {
                    $statusClass = 'bg-green-100 text-green-700';
                    $statusText = 'Excellent';
                } elseif ($q['correct_rate'] >= 50) {
                    $statusClass = 'bg-yellow-100 text-yellow-700';
                    $statusText = 'Average';
                } else {
                    $statusClass = 'bg-red-100 text-red-700';
                    $statusText = 'Needs Review';
                }
            @endphp

            <tr class="hover:bg-slate-50 transition">
                <td class="px-4 py-3 font-semibold text-slate-700">
                    {{ $q['order'] }}
                </td>

                <td class="px-4 py-3 text-slate-700">
                    {{ $q['question_text'] }}
                </td>

                <td class="px-4 py-3 capitalize">
                    {{ $q['question_type'] }}
                </td>

                <td class="px-4 py-3 font-semibold">
                    {{ $q['points'] }}
                </td>

                <td class="px-4 py-3 font-semibold">
                    {{ $q['correct_rate'] }}%
                </td>

                <td class="px-4 py-3 text-green-600 font-semibold">
                    {{ $q['correct_count'] }}
                </td>

                <td class="px-4 py-3 text-red-600 font-semibold">
                    {{ $wrongCount }}
                </td>

                <td class="px-4 py-3">
                    {{ $q['average_points'] }}
                </td>

                <td class="px-4 py-3">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                        {{ $statusText }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

    </div>
</div>
@endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdown = document.getElementById('resultsSortDropdown');
    const tableBody = document.getElementById('resultsTableBody');

    if (!dropdown || !tableBody) return;

    const originalRows = Array.from(tableBody.querySelectorAll('tr'));

    function updateRankAndHighlights(rows) {
        rows.forEach((row, index) => {
            // Update rank column (first td)
            row.children[0].textContent = index + 1;

            // Reset row classes
            row.className = 'hover:bg-slate-50 transition';

            // Top 3 highlights
            if (index === 0) {
                row.classList.add('bg-yellow-50', 'shadow-sm');
            } else if (index === 1) {
                row.classList.add('bg-slate-100', 'shadow-sm');
            } else if (index === 2) {
                row.classList.add('bg-amber-50', 'shadow-sm');
            }
        });
    }

    function sortTable(mode) {
        let rows = [...originalRows];

        if (mode === 'ranking') {
            rows.sort((a, b) => {
                const scoreA = parseFloat(a.children[8].textContent) || 0;
                const scoreB = parseFloat(b.children[8].textContent) || 0;
                return scoreB - scoreA;
            });
        }

        if (mode === 'surname') {
            rows.sort((a, b) => {
                const surnameA = a.children[2].textContent.trim().toLowerCase();
                const surnameB = b.children[2].textContent.trim().toLowerCase();
                return surnameA.localeCompare(surnameB);
            });
        }

        if (mode === 'newest') {
            rows = [...originalRows];
        }

        tableBody.innerHTML = '';
        rows.forEach(row => tableBody.appendChild(row));

        updateRankAndHighlights(rows);
    }

    dropdown.addEventListener('change', function () {
        sortTable(this.value);
    });

    // Default initial formatting
    updateRankAndHighlights(originalRows);
});
</script>
@endsection