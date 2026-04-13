@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="rounded-3xl bg-green-700 p-6 text-white shadow-xl sm:p-8">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-emerald-200">Quiz Report</p>
            <h2 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $quiz->title }}</h2>
            <p class="mt-2 text-sm text-emerald-100">Answer Key — Correct answers for all questions.</p>
        </div>

        {{-- Export Buttons --}}
        <div class="flex gap-3">
            <a href="{{ route('teacher.reports.quizzes') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 shadow hover:bg-slate-300 transition">
                ← Back
            </a>
            <a href="{{ route('teacher.reports.quiz.answers.export.docx', $quiz->id) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                ⬇ Export DOCX
            </a>
            <a href="{{ route('teacher.reports.quiz.answers.export.pdf', $quiz->id) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-700 transition">
                ⬇ Export PDF
            </a>
        </div>

        {{-- Answer Key --}}
        <div class="rounded-3xl bg-white shadow-lg ring-1 ring-slate-200 px-8 py-10 max-w-4xl mx-auto">

            {{-- Doc-style Header --}}
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-slate-800">{{ $quiz->title }}</h1>
                <p class="text-sm text-slate-500 italic mt-1">Answer Key</p>
                @if ($quiz->description)
                    <p class="text-sm text-slate-600 mt-2">{{ $quiz->description }}</p>
                @endif
                <div class="mt-4 border-t border-slate-300"></div>
            </div>

            {{-- Questions + Answers --}}
            @forelse ($quiz->questions->sortBy('order') as $index => $question)
                <div class="mb-8" style="padding-left: 3rem;">

                    {{-- Question Text --}}
                    <p class="font-semibold text-slate-800 mb-2">
                        {{ $index + 1 }}.
                        <span class="text-xs font-normal text-slate-500">({{ $question->points }} {{ $question->points == 1 ? 'pt' : 'pts' }})</span>
                        {{ $question->question_text }}
                    </p>

                    {{-- Media --}}
                    @if ($question->image_path)
                        <img src="{{ asset('storage/' . $question->image_path) }}"
                            class="mb-3 max-h-48 rounded-lg border border-slate-200" alt="Question Image">
                    @endif
                    @if ($question->audio_path)
                        <audio controls class="mb-3 w-full">
                            <source src="{{ asset('storage/' . $question->audio_path) }}">
                        </audio>
                    @endif
                    @if ($question->video_path)
                        <video controls class="mb-3 max-h-48 w-full rounded-lg border border-slate-200">
                            <source src="{{ asset('storage/' . $question->video_path) }}">
                        </video>
                    @endif

                    {{-- Answer by Type --}}
                    @if ($question->question_type === 'multiple_choice')
                        @php $correct = $question->answerOptions->firstWhere('is_correct', true); @endphp
                        <div class="ml-6 inline-flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-800 font-medium">
                            ✓ {{ $correct?->option_text ?? '—' }}
                        </div>

                    @elseif ($question->question_type === 'true_false')
                        @php $correct = $question->answerOptions->firstWhere('is_correct', true); @endphp
                        <div class="ml-6 inline-flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-800 font-medium">
                            ✓ {{ $correct?->option_text ?? '—' }}
                        </div>

                    @elseif ($question->question_type === 'identification')
                        @php $correct = $question->answerOptions->first(); @endphp
                        <div class="ml-6 inline-flex items-center gap-2 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-2 text-sm text-emerald-800 font-medium">
                            ✓ {{ $correct?->option_text ?? '—' }}
                        </div>

                    @elseif ($question->question_type === 'matching')
                        <div class="ml-6 mt-2 overflow-x-auto">
                            <table class="w-full text-sm border border-slate-200 rounded-lg overflow-hidden">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                            Premise
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                            Correct Match
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($question->answerOptions->sortBy('order') as $pair)
                                        <tr class="hover:bg-emerald-50">
                                            <td class="px-4 py-2 text-slate-700">{{ $pair->option_text }}</td>
                                            <td class="px-4 py-2 text-emerald-700 font-medium">{{ $pair->match_pair }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            @empty
                <p class="text-center text-slate-500 text-sm">No questions found for this quiz.</p>
            @endforelse

        </div>
    </div>
@endsection