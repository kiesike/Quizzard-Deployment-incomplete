@extends('teacher.layouts.app')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="rounded-3xl bg-green-700 p-6 text-white shadow-xl sm:p-8">
            <p class="text-sm font-medium uppercase tracking-[0.2em] text-blue-200">Quiz Report</p>
            <h2 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $quiz->title }}</h2>
            <p class="mt-2 text-sm text-blue-100">Test Questionnaire — Questions only, no answers shown.</p>
        </div>

        {{-- Export Buttons --}}
        <div class="flex gap-3">
            <a href="{{ route('teacher.reports.quizzes') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 shadow hover:bg-slate-400 transition">
                ← Back
            </a>
            <a href="{{ route('teacher.reports.quiz.questions.export.docx', $quiz->id) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                ⬇ Export DOCX
            </a>
            <a href="{{ route('teacher.reports.quiz.questions.export.pdf', $quiz->id) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-700 transition">
                ⬇ Export PDF
            </a>
        </div>

        {{-- Questionnaire --}}
        <div class="rounded-3xl shadow-lg ring-1 ring-slate-200 px-8 py-10 max-w-4xl mx-auto">

            {{-- Doc-style Header --}}
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-slate-800">{{ $quiz->title }}</h1>
                <p class="text-sm text-slate-500 italic mt-1">Test Questionnaire</p>
                @if ($quiz->description)
                    <p class="text-sm text-slate-600 mt-2">{{ $quiz->description }}</p>
                @endif
                <div class="mt-4 border-t border-slate-300"></div>
            </div>

            {{-- Questions --}}
            @forelse ($quiz->questions->sortBy('order') as $index => $question)
                <div class="mb-8 pl-20 pr-4" style="padding-left: 3rem;">

                    {{-- Question Text --}}
                    <p class="font-semibold text-slate-800 mb-2">
                        {{ $index + 1 }}.
                        {{ $question->question_text }}
                        <span class="text-xs font-normal text-slate-500">({{ $question->points }} {{ $question->points == 1 ? 'pt' : 'pts' }})</span>
                    </p>

                    {{-- Media --}}
                    @if ($question->image_path)
                        <img src="{{ asset('storage/' . $question->image_path) }}"
                            class="mb-3 rounded-lg border border-slate-200"
                            style="max-height: 160px; max-width: 320px; object-fit: contain;"
                            alt="Question Image">
                    @endif
                    @if ($question->audio_path)
                        <audio controls class="mb-3" style="width: 320px;">
                            <source src="{{ asset('storage/' . $question->audio_path) }}">
                        </audio>
                    @endif
                    @if ($question->video_path)
                        <video controls class="mb-3 rounded-lg border border-slate-200"
                            style="max-height: 200px; max-width: 320px;">
                            <source src="{{ asset('storage/' . $question->video_path) }}">
                        </video>
                    @endif

                    {{-- Answer Area by Type --}}
                    @if ($question->question_type === 'multiple_choice')
                        @php $letters = ['A', 'B', 'C', 'D']; @endphp
                        <ul class="ml-6 space-y-1">
                            @foreach ($question->answerOptions->sortBy('order') as $i => $option)
                                <li class="text-slate-700 text-sm">
                                    {{ $letters[$i] ?? chr(65 + $i) }}. {{ $option->option_text }}
                                    @if ($option->image_path)
                                        <img src="{{ asset('storage/' . $option->image_path) }}"
                                            class="mt-1 max-h-24 rounded border border-slate-200"
                                            style="max-height: 160px; max-width: 320px; object-fit: contain;
                                            alt="Option Image" >
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                    @elseif ($question->question_type === 'true_false')
                        <ul class="ml-6 space-y-1">
                            <li class="text-slate-700 text-sm">A. True</li>
                            <li class="text-slate-700 text-sm">B. False</li>
                        </ul>

                    @elseif ($question->question_type === 'identification')
                        <p class="ml-6 text-slate-500 text-sm">Answer: ___________________________</p>

                    @elseif ($question->question_type === 'matching')
                        <div class="ml-6 mt-2 text-black">
                            <div class="grid grid-cols-2 gap-12">
                                <!-- Column A -->
                                <div>
                                    <h3 class="font-semibold mb-2">Column A</h3>
                                    <ol class="list-decimal ml-5 space-y-1">
                                        @foreach ($question->answerOptions->sortBy('order') as $pair)
                                            <li>{{ $pair->option_text }}</li>
                                        @endforeach
                                    </ol>
                                </div>

                                <!-- Column B -->
                                <div>
                                    <h3 class="font-semibold mb-2">Column B</h3>
                                    <ol class="list-[upper-alpha] ml-5 space-y-1">
                                        @foreach ($question->answerOptions->shuffle() as $pair)
                                            <li>{{ $pair->match_pair }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            </div>

                            <!-- Answer Section -->
                            <div class="mt-6">
                                <p class="font-medium">Answer:</p>
                                <div class="flex flex-wrap gap-6 mt-2">
                                    @foreach ($question->answerOptions->sortBy('order') as $index => $pair)
                                        <span>{{ $index + 1 }}. ______</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            @empty
                <p class="text-center text-slate-500 text-sm">No questions found for this quiz.</p>
            @endforelse

        </div>
    </div>
@endsection