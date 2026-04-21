@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-8">

        {{-- Header --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-700 via-green-600 to-emerald-600 px-6 py-8 text-white shadow-lg sm:px-10 sm:py-10">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute bottom-0 left-10 h-40 w-40 rounded-full bg-emerald-400/20 blur-2xl"></div>
            </div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-400/30 bg-emerald-900/40 px-3 py-1.5 text-xs font-medium uppercase tracking-widest text-emerald-100 backdrop-blur-md">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Quiz Report
                    </div>
                    <h2 class="mt-5 text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $quiz->title }}</h2>
                    <p class="mt-3 text-base leading-relaxed text-emerald-100">Test Questionnaire - Questions only, no answers shown.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('teacher.reports.quizzes') }}"
                        class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-md transition hover:bg-white/20 focus:outline-none focus:ring-4 focus:ring-white/20">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.56l3.22 3.22a.75.75 0 11-1.06 1.06l-4.5-4.5a.75.75 0 010-1.06l4.5-4.5a.75.75 0 111.06 1.06L5.56 9.25h10.69A.75.75 0 0117 10z" clip-rule="evenodd" />
                        </svg>
                        Back
                    </a>
                    <a href="{{ route('teacher.reports.quiz.questions.export.docx', $quiz->id) }}"
                        class=" inline-flex items-center gap-2 rounded-2xl bg-white px-4 py-2 text-sm font-bold text-blue-700 shadow-md transition hover:-translate-y-0.5 hover:bg-blue-200 focus:outline-none focus:ring-4 focus:ring-white/20">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 2a.75.75 0 01.75.75v7.19l2.22-2.22a.75.75 0 111.06 1.06l-3.5 3.5a.75.75 0 01-1.06 0l-3.5-3.5a.75.75 0 111.06-1.06l2.22 2.22V2.75A.75.75 0 0110 2zm-5.25 11a.75.75 0 01.75.75v.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25v-.5a.75.75 0 011.5 0v.5A2.75 2.75 0 0113.25 17h-6.5A2.75 2.75 0 014 14.25v-.5a.75.75 0 01.75-.75z" clip-rule="evenodd" />
                        </svg>
                        Export DOCX
                    </a>
                    <a href="{{ route('teacher.reports.quiz.questions.export.pdf', $quiz->id) }}"
                        class="inline-flex items-center gap-2 rounded-2xl bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300/40">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 2a.75.75 0 01.75.75v7.19l2.22-2.22a.75.75 0 111.06 1.06l-3.5 3.5a.75.75 0 01-1.06 0l-3.5-3.5a.75.75 0 111.06-1.06l2.22 2.22V2.75A.75.75 0 0110 2zm-5.25 11a.75.75 0 01.75.75v.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25v-.5a.75.75 0 011.5 0v.5A2.75 2.75 0 0113.25 17h-6.5A2.75 2.75 0 014 14.25v-.5a.75.75 0 01.75-.75z" clip-rule="evenodd" />
                        </svg>
                        Export PDF
                    </a>
                </div>
            </div>
        </div>

        {{-- Questionnaire --}}
        <div class="mx-auto max-w-5xl overflow-hidden rounded-[2rem] border border-emerald-100 bg-white shadow-sm ring-1 ring-emerald-900/5">

            {{-- Doc-style Header --}}
            <div class="border-b border-emerald-100 bg-emerald-50/50 px-8 py-8 text-center sm:px-10">
                <div class="inline-flex items-center gap-2 rounded-md bg-emerald-100/80 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                    Questionnaire View
                </div>
                <h1 class="mt-4 text-2xl font-bold text-slate-900 sm:text-3xl">{{ $quiz->title }}</h1>
                <p class="mt-2 text-sm italic text-slate-500">Test Questionnaire</p>
                @if ($quiz->description)
                    <p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-slate-600">{{ $quiz->description }}</p>
                @endif
            </div>

            <div class="px-6 py-8 sm:px-8 sm:py-10">
                <div class="space-y-6">
                @forelse ($quiz->questions->sortBy('order') as $index => $question)
                    <div class="rounded-3xl border border-slate-200 bg-white px-5 py-6 shadow-sm shadow-slate-200/40 transition hover:border-emerald-200 hover:shadow-md sm:px-6">

                        {{-- Question Text --}}
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="max-w-3xl">
                                <div class="flex flex-wrap items-start gap-3">
                                    <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-full bg-emerald-100 px-2 text-sm font-bold text-emerald-700">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="space-y-2">
                                        <p class="text-base font-semibold leading-7 text-slate-800">
                                            {{ $question->question_text }}
                                        </p>
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                            {{ $question->points }} {{ $question->points == 1 ? 'pt' : 'pts' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Media --}}
                        <div class="mt-5 space-y-3">
                            @if ($question->image_path)
                                <img src="{{ asset('storage/' . $question->image_path) }}"
                                    class="rounded-2xl border border-slate-200 bg-slate-50"
                                    style="max-height: 220px; max-width: 360px; object-fit: contain;"
                                    alt="Question Image">
                            @endif
                            @if ($question->audio_path)
                                <audio controls style="width: min(360px, 100%);">
                                    <source src="{{ asset('storage/' . $question->audio_path) }}">
                                </audio>
                            @endif
                            @if ($question->video_path)
                                <video controls class="rounded-2xl border border-slate-200 bg-slate-50"
                                    style="max-height: 240px; max-width: 360px;">
                                    <source src="{{ asset('storage/' . $question->video_path) }}">
                                </video>
                            @endif
                        </div>

                        {{-- Answer Area by Type --}}
                        <div class="mt-6">
                            @if ($question->question_type === 'multiple_choice')
                                @php $letters = ['A', 'B', 'C', 'D']; @endphp
                                <ul class="space-y-3">
                                    @foreach ($question->answerOptions->sortBy('order') as $i => $option)
                                        <li class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm text-slate-700">
                                            <div class="flex items-start gap-3">
                                                <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-white text-xs font-bold text-emerald-700 ring-1 ring-slate-200">
                                                    {{ $letters[$i] ?? chr(65 + $i) }}
                                                </span>
                                                <div class="space-y-2">
                                                    <span>{{ $option->option_text }}</span>
                                                    @if ($option->image_path)
                                                        <img src="{{ asset('storage/' . $option->image_path) }}"
                                                            class="max-h-28 rounded-xl border border-slate-200 bg-white"
                                                            style="max-width: 320px; object-fit: contain;"
                                                            alt="Option Image">
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>

                            @elseif ($question->question_type === 'true_false')
                                <ul class="space-y-3">
                                    <li class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm text-slate-700">A. True</li>
                                    <li class="rounded-2xl border border-slate-200 bg-slate-50/70 px-4 py-3 text-sm text-slate-700">B. False</li>
                                </ul>

                            @elseif ($question->question_type === 'identification')
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-5 text-sm text-slate-500">
                                    Answer: ___________________________
                                </div>

                            @elseif ($question->question_type === 'matching')
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5 text-sm text-slate-800">
                                    <div class="grid gap-6 md:grid-cols-2 md:gap-10">
                                        <div>
                                            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Column A</h3>
                                            <ol class="list-decimal space-y-2 pl-5">
                                                @foreach ($question->answerOptions->sortBy('order') as $pair)
                                                    <li>{{ $pair->option_text }}</li>
                                                @endforeach
                                            </ol>
                                        </div>

                                        <div>
                                            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Column B</h3>
                                            <ol class="list-[upper-alpha] space-y-2 pl-5">
                                                @foreach ($question->answerOptions->shuffle() as $pair)
                                                    <li>{{ $pair->match_pair }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    </div>

                                    <div class="mt-6 border-t border-slate-200 pt-4">
                                        <p class="text-sm font-semibold text-slate-700">Answer:</p>
                                        <div class="mt-3 flex flex-wrap gap-5 text-sm text-slate-600">
                                            @foreach ($question->answerOptions->sortBy('order') as $index => $pair)
                                                <span>{{ $index + 1 }}. ______</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto max-w-md">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50">
                                <svg class="h-8 w-8 text-emerald-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M4.75 3A2.75 2.75 0 002 5.75v8.5A2.75 2.75 0 004.75 17h10.5A2.75 2.75 0 0018 14.25v-8.5A2.75 2.75 0 0015.25 3H4.75zm0 1.5h10.5c.69 0 1.25.56 1.25 1.25v8.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-8.5c0-.69.56-1.25 1.25-1.25z" />
                                </svg>
                            </div>
                            <p class="mt-5 text-sm text-slate-500">No questions found for this quiz.</p>
                        </div>
                    </div>
                @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
