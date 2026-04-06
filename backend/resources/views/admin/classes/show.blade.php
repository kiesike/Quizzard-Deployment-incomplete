@extends('admin.layouts.app')

@section('title', 'Class Details')

@section('content')
<div class="space-y-6">

    <!-- Back button -->
    <div>
        <a href="{{ route('admin.classes') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">
            <span>&larr;</span>
            <span>Back to Classes</span>
        </a>
    </div>

    <!-- Hero / Class Info -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-900 p-6 text-white shadow-xl sm:p-8">
        <!-- subtle colored glows only, no white circles -->
        <div class="absolute -right-16 -top-16 h-44 w-44 rounded-full bg-indigo-500/10 blur-3xl"></div>
        <div class="absolute -bottom-16 -left-16 h-44 w-44 rounded-full bg-violet-500/10 blur-3xl"></div>

        <div class="relative z-10 flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-indigo-200">Class Details</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $class->name }}</h1>

                <p class="mt-3 max-w-2xl text-sm text-slate-200 sm:text-base">
                    {{ $class->description ?: 'No class description provided.' }}
                </p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl bg-slate-800/60 border border-white/10 px-4 py-3 backdrop-blur">
                        <p class="text-xs uppercase tracking-wide text-slate-200">Teacher</p>
                        <p class="mt-1 text-base font-semibold text-white">
                            {{ $class->teacher->name ?? 'Unknown Teacher' }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-800/60 border border-white/10 px-4 py-3 backdrop-blur">
                        <p class="text-xs uppercase tracking-wide text-slate-200">Class Code</p>
                        <p class="mt-1 text-base font-semibold text-white">
                            {{ $class->class_code }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-800/60 border border-white/10 px-4 py-3 backdrop-blur">
                        <p class="text-xs uppercase tracking-wide text-slate-200">Created</p>
                        <p class="mt-1 text-base font-semibold text-white">
                            {{ $class->created_at ? $class->created_at->format('M d, Y') : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- smaller widgets -->
            <div class="flex flex-wrap gap-3 xl:max-w-xs xl:justify-end">
                <div class="min-w-[140px] rounded-2xl bg-white/10 px-4 py-3 backdrop-blur shadow-lg">
                    <p class="text-xs uppercase tracking-wide text-slate-200">Students Enrolled</p>
                    <p class="mt-1 text-2xl font-bold text-white">{{ $studentsEnrolledCount }}</p>
                </div>

                <div class="min-w-[140px] rounded-2xl bg-white/10 px-4 py-3 backdrop-blur shadow-lg">
                    <p class="text-xs uppercase tracking-wide text-slate-200">Total Quizzes</p>
                    <p class="mt-1 text-2xl font-bold text-white">{{ $quizzesCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quizzes Card -->
    <div class="rounded-3xl bg-white p-6 shadow-lg ring-1 ring-slate-200">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Class Quizzes</h2>
                <p class="mt-1 text-sm text-slate-500">
                    View all quizzes currently assigned to this class.
                </p>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Quiz
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Description
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Questions
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
    Students Taken
</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Status
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($class->quizzes as $quiz)
                            <tr
    class="quiz-row cursor-pointer transition duration-150 hover:bg-indigo-100 hover:shadow-sm"
    data-url="{{ route('admin.classes.quizzes.details', [$class->id, $quiz->id]) }}"
>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800">{{ $quiz->title }}</div>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div class="max-w-md truncate">
                                        {{ $quiz->description ?: 'No description' }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-700">
    {{ $quiz->questions_count ?? 0 }}
</td>

<td class="px-6 py-4 text-sm text-slate-700">
    {{ $quiz->attempts_count ?? 0 }}
</td>

<td class="px-6 py-4">
    @if($quiz->is_published)
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
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                                    No quizzes are currently assigned to this class.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.quiz-row').forEach(row => {
        row.addEventListener('click', function (e) {
            if (e.target.closest('button, a')) return;
            if (this.dataset.loading === 'true') return;

            this.dataset.loading = 'true';
            this.style.opacity = '0.65';

            document.querySelectorAll('.quiz-row').forEach(otherRow => {
                if (otherRow !== this) {
                    otherRow.style.pointerEvents = 'none';
                    otherRow.style.opacity = '0.55';
                }
            });

            const overlay = document.getElementById('pageLoadingOverlay');
            if (overlay) {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
            }

            window.location.href = this.dataset.url;
        });
    });
});
</script>
@endpush