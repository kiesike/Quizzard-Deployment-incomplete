@extends('teacher.layouts.app')

@section('content')
    @php
        $teacher = auth()->user();
        $teacherName = trim((string) ($teacher?->first_name ?: $teacher?->name ?: 'Teacher'));

        $teacherQuizzes = \App\Models\Quiz::query()
            ->where('teacher_id', $teacher->id)
            ->withCount(['questions', 'classes'])
            ->with([
                'attempts' => function ($query) {
                    $query->where('status', 'completed')
                        ->with('student:id,first_name,surname,name')
                        ->latest('completed_at');
                },
            ])
            ->latest()
            ->get();

        $classes = \App\Models\ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->withCount(['students', 'quizzes'])
            ->with([
                'students:id',
                'quizzes' => function ($query) {
                    $query->with([
                        'attempts' => function ($attemptsQuery) {
                            $attemptsQuery->where('status', 'completed');
                        },
                    ]);
                },
            ])
            ->latest()
            ->get();

        $allAttempts = $teacherQuizzes->flatMap(fn($q) => $q->attempts);

        $totalClasses       = $classes->count();
        $totalStudents      = $classes->flatMap(fn($c) => $c->students->pluck('id'))->unique()->count();
        $totalQuizzes       = $teacherQuizzes->count();
        $publishedQuizzes   = $teacherQuizzes->where('is_published', true)->count();
        $completedAttempts  = $allAttempts->count();

        $scorePct = fn($a) => (float) $a->total_points > 0 ? ($a->score / $a->total_points) * 100 : 0;

        $overallAverageScore = $completedAttempts > 0
            ? round($allAttempts->avg($scorePct), 1)
            : null;

        if ($totalQuizzes === 0) {
            $heroInsight = 'Start by creating quizzes and assigning them to classes so this dashboard can surface progress trends.';
        } elseif ($completedAttempts === 0) {
            $heroInsight = 'Your quiz content is in place. The next milestone is getting the first completed student attempts.';
        } else {
            $heroInsight = 'Your quizzes have produced ' . number_format($completedAttempts) . ' completed attempts so far, giving you a strong read on class progress.';
        }

        $recentQuizzes = $teacherQuizzes->take(5)->map(function ($quiz) use ($scorePct) {
            $cnt = $quiz->attempts->count();
            return (object) [
                'id'                      => $quiz->id,
                'title'                   => $quiz->title,
                'is_published'            => $quiz->is_published,
                'classes_count'           => $quiz->classes_count,
                'questions_count'         => $quiz->questions_count,
                'attempts_count'          => $cnt,
                'students_attempted_count'=> $quiz->attempts->pluck('student_id')->unique()->count(),
                'average_score'           => $cnt > 0 ? round($quiz->attempts->avg($scorePct), 1) : null,
                'created_at'              => $quiz->created_at,
            ];
        });

        $classSnapshots = $classes->map(function ($class) use ($scorePct) {
            $attempts = $class->quizzes->flatMap(fn($q) => $q->attempts);
            $completedPairs = $class->quizzes->sum(fn($q) => $q->attempts->pluck('student_id')->unique()->count());
            $possible = $class->students_count * $class->quizzes_count;

            return (object) [
                'id'                => $class->id,
                'name'              => $class->name,
                'class_code'        => $class->class_code,
                'students_count'    => $class->students_count,
                'quizzes_count'     => $class->quizzes_count,
                'attempts_count'    => $attempts->count(),
                'participation_rate'=> $possible > 0 ? round(($completedPairs / $possible) * 100, 1) : null,
                'average_score'     => $attempts->count() > 0 ? round($attempts->avg($scorePct), 1) : null,
            ];
        });

        $spotlightClasses = $classSnapshots
            ->sortByDesc(fn($c) => (($c->participation_rate ?? 0) * 1000) + ($c->average_score ?? 0))
            ->take(4)->values();

        $quizzesWithoutAttempts = $teacherQuizzes->filter(fn($q) => $q->attempts->isEmpty())->count();
        $lowParticipationClasses = $classSnapshots->filter(fn($c) => !is_null($c->participation_rate) && $c->participation_rate < 50)->count();
        $classesNeedingSetup = $classSnapshots->filter(fn($c) => $c->quizzes_count === 0 || $c->students_count === 0)->count();

        $needsAttention = [
            [
                'label'       => 'Unpublished quizzes',
                'count'       => $totalQuizzes - $publishedQuizzes,
                'description' => 'Review these before students can access them.',
                'route'       => route('teacher.reports.quizzes'),
                'color'       => 'amber',
            ],
            [
                'label'       => 'Quizzes with no attempts',
                'count'       => $quizzesWithoutAttempts,
                'description' => 'These may need a reminder or a class assignment check.',
                'route'       => route('teacher.reports.quizzes'),
                'color'       => 'rose',
            ],
            [
                'label'       => 'Classes below 50% participation',
                'count'       => $lowParticipationClasses,
                'description' => 'A quick follow-up could improve completion rates.',
                'route'       => route('teacher.reports.classes'),
                'color'       => 'sky',
            ],
            [
                'label'       => 'Classes needing setup',
                'count'       => $classesNeedingSetup,
                'description' => 'These classes still need students, quizzes, or both.',
                'route'       => route('teacher.reports.classes'),
                'color'       => 'slate',
            ],
        ];

        $recentActivity = $teacherQuizzes->flatMap(function ($quiz) use ($scorePct) {
            return $quiz->attempts->map(fn($a) => (object) [
                'quiz_id'      => $quiz->id,
                'quiz_title'   => $quiz->title,
                'student_name' => $a->student?->name ?? 'Student',
                'completed_at' => $a->completed_at,
                'percentage'   => (float) $a->total_points > 0 ? round(($a->score / $a->total_points) * 100, 1) : null,
            ]);
        })->sortByDesc('completed_at')->take(5)->values();

        $attentionColors = [
            'amber' => ['panel' => 'border-amber-200 bg-amber-50',  'badge' => 'bg-amber-100 text-amber-700 ring-amber-400/30', 'dot' => 'bg-amber-500'],
            'rose'  => ['panel' => 'border-rose-200 bg-rose-50',    'badge' => 'bg-rose-100 text-rose-700 ring-rose-400/30',   'dot' => 'bg-rose-500'],
            'sky'   => ['panel' => 'border-sky-200 bg-sky-50',      'badge' => 'bg-sky-100 text-sky-700 ring-sky-400/30',     'dot' => 'bg-sky-500'],
            'slate' => ['panel' => 'border-slate-200 bg-slate-50',  'badge' => 'bg-slate-100 text-slate-600 ring-slate-400/30','dot' => 'bg-slate-400'],
        ];
    @endphp

    <div class="space-y-6">

        {{-- ── Hero ────────────────────────────────────────────────────────── --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-green-700 via-green-600 to-emerald-500 px-6 py-8 text-white shadow-lg sm:px-10 sm:py-10">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -right-16 -top-16 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute bottom-0 left-8 h-48 w-48 rounded-full bg-emerald-300/20 blur-2xl"></div>
            </div>

            <div class="relative flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/30 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-emerald-100 backdrop-blur-md">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                        Teacher Dashboard
                    </span>
                    <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">
                        Welcome back, {{ $teacherName }} 👋
                    </h2>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-emerald-100">
                        You manage <strong class="text-white">{{ number_format($totalClasses) }} classes</strong>,
                        <strong class="text-white">{{ number_format($totalQuizzes) }} quizzes</strong>, and
                        <strong class="text-white">{{ number_format($totalStudents) }} students</strong>.
                        {{ $heroInsight }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                    <a href="{{ route('teacher.reports.classes') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-xs font-bold text-emerald-700 shadow transition hover:-translate-y-0.5 hover:bg-emerald-50">
                        View Classes
                    </a>
                    <a href="{{ route('teacher.reports.quizzes') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-white/10 px-4 py-2.5 text-xs font-bold text-white ring-1 ring-white/20 backdrop-blur transition hover:bg-white/20">
                        View Quizzes
                    </a>
                    <a href="{{ route('teacher.reports.students') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-white/10 px-4 py-2.5 text-xs font-bold text-white ring-1 ring-white/20 backdrop-blur transition hover:bg-white/20">
                        View Students
                    </a>
                    <a href="{{ route('teacher.reports.students.export') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-xs font-bold text-emerald-700 shadow transition hover:-translate-y-0.5 hover:bg-emerald-50">
                        Export Students
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Stat cards ──────────────────────────────────────────────────── --}}
        @php
            $stats = [
                ['label' => 'Total Classes',       'value' => number_format($totalClasses),      'sub' => 'Classes under your account'],
                ['label' => 'Active Students',     'value' => number_format($totalStudents),     'sub' => 'Unique learners enrolled'],
                ['label' => 'Total Quizzes',       'value' => number_format($totalQuizzes),      'sub' => 'Quizzes created so far'],
                ['label' => 'Published',           'value' => number_format($publishedQuizzes),  'sub' => 'Available to students'],
                ['label' => 'Completed Attempts',  'value' => number_format($completedAttempts), 'sub' => 'Finished submissions'],
                ['label' => 'Overall Avg Score',   'value' => !is_null($overallAverageScore) ? number_format($overallAverageScore, 1).'%' : '—', 'sub' => 'Across all attempts'],
            ];
        @endphp
        <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-6">
            @foreach ($stats as $stat)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $stat['value'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $stat['sub'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ── Main two-column row ─────────────────────────────────────────── --}}
        {{-- <div class="grid gap-5 xl:grid-cols-[1.5fr_1fr]">

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">Fresh Overview</p>
                        <h3 class="mt-0.5 text-base font-bold text-slate-900">Quizzes</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Publishing status, reach, and performance at a glance.</p>
                    </div>
                    <a href="{{ route('teacher.reports.quizzes') }}"
                        class="shrink-0 rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                        Open Quiz Report
                    </a>
                </div>

                @if ($recentQuizzes->isEmpty())
                    <div class="px-6 py-14 text-center">
                        <p class="text-sm font-semibold text-slate-700">No quizzes yet</p>
                        <p class="mt-1 text-xs text-slate-400">Create quizzes to see them here.</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($recentQuizzes as $quiz)
                            <div class="px-6 py-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="truncate text-sm font-bold text-slate-900">{{ $quiz->title }}</h4>
                                            @if ($quiz->is_published)
                                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-500/20">Published</span>
                                            @else
                                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-400/20">Unpublished</span>
                                            @endif
                                        </div>

                                        <div class="mt-2 flex flex-wrap gap-1.5 text-[11px] text-slate-500">
                                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5">{{ $quiz->questions_count }} Qs</span>
                                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5">{{ $quiz->classes_count }} classes</span>
                                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5">{{ $quiz->students_attempted_count }} attempted</span>
                                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5">{{ $quiz->created_at?->format('M d, Y') ?? 'Recently' }}</span>
                                        </div>

                                        <div class="mt-3 flex gap-2">
                                            <a href="{{ route('teacher.reports.quiz.questions', $quiz->id) }}"
                                                class="rounded-lg bg-blue-50 px-3 py-1 text-[11px] font-semibold text-blue-700 ring-1 ring-blue-200 transition hover:bg-blue-100">
                                                Questions
                                            </a>
                                            <a href="{{ route('teacher.reports.quiz.answers', $quiz->id) }}"
                                                class="rounded-lg bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200 transition hover:bg-emerald-100">
                                                Answers
                                            </a>
                                        </div>
                                    </div>

                                    <div class="flex gap-2 sm:flex-col sm:items-end">
                                        <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-2 text-center">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600">Attempts</p>
                                            <p class="mt-0.5 text-lg font-extrabold text-slate-900">{{ number_format($quiz->attempts_count) }}</p>
                                        </div>
                                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-2 text-center">
                                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Avg Score</p>
                                            <p class="mt-0.5 text-lg font-extrabold text-slate-900">
                                                {{ !is_null($quiz->average_score) ? number_format($quiz->average_score, 1).'%' : '—' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="space-y-5">

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-5">
                        <h3 class="text-base font-bold text-slate-900">Needs Attention</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Quick checklist of areas that may need your next action.</p>
                    </div>
                    <div class="space-y-2.5 p-5">
                        @foreach ($needsAttention as $item)
                            @php $c = $attentionColors[$item['color']]; @endphp
                            <a href="{{ $item['route'] }}"
                                class="flex items-center justify-between gap-3 rounded-xl border p-3.5 transition hover:-translate-y-0.5 hover:shadow-sm {{ $c['panel'] }}">
                                <div class="flex min-w-0 items-start gap-2.5">
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $c['dot'] }}"></span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-800">{{ $item['label'] }}</p>
                                        <p class="mt-0.5 text-[11px] text-slate-500">{{ $item['description'] }}</p>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 ring-inset {{ $c['badge'] }}">
                                    {{ number_format($item['count']) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50 px-6 py-5">
                        <h3 class="text-base font-bold text-slate-900">Recent Activity</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Latest completed submissions across your quizzes.</p>
                    </div>

                    @if ($recentActivity->isEmpty())
                        <div class="px-6 py-10">
                            <p class="text-xs text-slate-400">No completed attempts yet. Activity will appear here once submissions come in.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach ($recentActivity as $activity)
                                <div class="flex items-center justify-between gap-3 px-6 py-3.5">
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-semibold text-slate-900">{{ $activity->student_name }}</p>
                                        <p class="mt-0.5 truncate text-[11px] text-slate-500">{{ $activity->quiz_title }}</p>
                                        <p class="mt-0.5 text-[10px] text-slate-400">{{ $activity->completed_at?->diffForHumans() ?? 'Recently' }}</p>
                                    </div>
                                    @php
                                        $pct = $activity->percentage;
                                        $scoreColor = is_null($pct) ? 'bg-slate-100 text-slate-500'
                                            : ($pct >= 75 ? 'bg-emerald-100 text-emerald-700'
                                            : ($pct >= 50 ? 'bg-amber-100 text-amber-700'
                                            : 'bg-rose-100 text-rose-700'));
                                    @endphp
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold {{ $scoreColor }}">
                                        {{ !is_null($pct) ? number_format($pct, 1).'%' : '—' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div> --}}

        {{-- ── Class Performance Spotlight ─────────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">Class Snapshot</p>
                    <h3 class="mt-0.5 text-base font-bold text-slate-900">Class Performance Spotlight</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Top classes ranked by participation and average score.</p>
                </div>
                <a href="{{ route('teacher.reports.classes') }}"
                    class="shrink-0 rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700">
                    Open Class Report
                </a>
            </div>

            @if ($spotlightClasses->isEmpty())
                <div class="px-6 py-14 text-center">
                    <p class="text-sm font-semibold text-slate-700">No classes to summarize yet</p>
                    <p class="mt-1 text-xs text-slate-400">Set up classes and assign quizzes to see performance here.</p>
                </div>
            @else
                <div class="grid gap-4 p-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($spotlightClasses as $class)
                        <div class="flex flex-col rounded-2xl border border-slate-200 bg-slate-50/60 p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h4 class="truncate text-sm font-bold text-slate-900">{{ $class->name }}</h4>
                                    <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wide text-emerald-600">{{ $class->class_code }}</p>
                                </div>
                                @php
                                    $pr = $class->participation_rate;
                                    $prColor = is_null($pr) ? 'bg-slate-100 text-slate-500'
                                        : ($pr >= 75 ? 'bg-emerald-100 text-emerald-700'
                                        : ($pr >= 50 ? 'bg-amber-100 text-amber-700'
                                        : 'bg-rose-100 text-rose-700'));
                                @endphp
                                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $prColor }}">
                                    {{ !is_null($pr) ? number_format($pr, 1).'%' : '—' }}
                                </span>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-2">
                                @php
                                    $classStats = [
                                        ['label' => 'Students', 'value' => number_format($class->students_count)],
                                        ['label' => 'Quizzes',  'value' => number_format($class->quizzes_count)],
                                        ['label' => 'Attempts', 'value' => number_format($class->attempts_count)],
                                        ['label' => 'Avg Score','value' => !is_null($class->average_score) ? number_format($class->average_score, 1).'%' : '—'],
                                    ];
                                @endphp
                                @foreach ($classStats as $cs)
                                    <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $cs['label'] }}</p>
                                        <p class="mt-0.5 text-base font-extrabold text-slate-900">{{ $cs['value'] }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-3 flex gap-2">
                                <a href="{{ route('teacher.reports.class.detail', $class->id) }}"
                                    class="flex-1 rounded-lg bg-emerald-600 py-1.5 text-center text-[11px] font-semibold text-white transition hover:bg-emerald-700">
                                    View Report
                                </a>
                                <a href="{{ route('teacher.reports.class.quizzes', $class->id) }}"
                                    class="flex-1 rounded-lg bg-blue-600 py-1.5 text-center text-[11px] font-semibold text-white transition hover:bg-blue-700">
                                    Quizzes
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
@endsection