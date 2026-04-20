@extends('teacher.layouts.app')

@section('title', 'Manage Quiz')

@section('content')
<div class="space-y-6">

    {{-- Back --}}
    <a href="{{ route('teacher.quizzes.index') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow">
        ← Back to My Quizzes
    </a>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Quiz Header --}}
    <div class="rounded-3xl p-6 text-white shadow-xl"
         style="background: linear-gradient(135deg,#020617,#0f172a,#1e1b4b);">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">{{ $quiz->title }}</h1>
                <p class="mt-1 text-slate-300 text-sm">{{ $quiz->description ?? 'No description' }}</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Publish Toggle --}}
                <form action="{{ route('teacher.quizzes.toggle-publish', $quiz->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-sm font-semibold shadow transition
                                {{ $quiz->is_published
                                    ? 'bg-yellow-400 hover:bg-yellow-500 text-slate-900'
                                    : 'bg-green-500 hover:bg-green-600 text-white' }}">
                        {{ $quiz->is_published ? 'Unpublish' : 'Publish' }}
                    </button>
                </form>

                {{-- Status Badge --}}
                @if($quiz->is_published)
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-300">Published</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-500/20 text-slate-400">Draft</span>
                @endif
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
            <div class="bg-slate-800/60 px-4 py-2 rounded-xl">
                <p class="text-xs text-slate-400">Questions</p>
                <p class="text-xl font-bold">{{ $quiz->questions->count() }}</p>
            </div>
            <div class="bg-slate-800/60 px-4 py-2 rounded-xl">
                <p class="text-xs text-slate-400">Total Points</p>
                <p class="text-xl font-bold">{{ $quiz->questions->sum('points') }}</p>
            </div>
            <div class="bg-slate-800/60 px-4 py-2 rounded-xl">
                <p class="text-xs text-slate-400">Attempts</p>
                <p class="text-xl font-bold">{{ $quiz->attempts->count() }}</p>
            </div>
        </div>
    </div>

    {{-- Edit Quiz Details --}}
    <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Edit Quiz Details</h2>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('teacher.quizzes.update', $quiz->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $quiz->title) }}"
                       class="w-full border border-slate-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                          class="w-full border border-slate-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">{{ old('description', $quiz->description) }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Questions --}}
    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-800">Questions</h2>
            @if(!$quiz->has_attempts)
                <div class="flex items-center gap-2">
                    <select id="questionTypeSelect"
                            class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True / False</option>
                        <option value="identification">Identification</option>
                        <option value="matching">Matching</option>
                    </select>
                    <a id="addQuestionBtn"
                       href="{{ route('teacher.quizzes.questions.create', ['quizId' => $quiz->id, 'type' => 'multiple_choice']) }}"
                       class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow transition">
                        + Add Question
                    </a>
                </div>
            @else
                <span class="text-xs text-slate-400 italic">Questions are locked — this quiz has attempts.</span>
            @endif
        </div>

        @if($quiz->questions->isEmpty())
            <p class="text-slate-500 text-sm text-center py-8">No questions yet. Add your first question above.</p>
        @else
            <div class="space-y-4">
                @foreach($quiz->questions as $index => $question)
                <div class="border border-slate-200 rounded-2xl p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold text-slate-400 uppercase">Q{{ $index + 1 }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 font-semibold capitalize">
                                    {{ str_replace('_', ' ', $question->type) }}
                                </span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-semibold">
                                    {{ $question->points }} {{ $question->points == 1 ? 'pt' : 'pts' }}
                                </span>
                            </div>
                            <p class="text-sm font-semibold text-slate-800">{{ $question->question_text }}</p>

                            {{-- Answer preview --}}
                            <div class="mt-2 space-y-1">
                                @if($question->type === 'multiple_choice')
                                    @foreach($question->answerOptions->sortBy('order') as $i => $opt)
                                        <p class="text-xs text-slate-600 {{ $opt->is_correct ? 'text-green-600 font-semibold' : '' }}">
                                            {{ chr(65 + $i) }}. {{ $opt->option_text }}
                                            @if($opt->is_correct) ✓ @endif
                                        </p>
                                    @endforeach
                                @elseif($question->type === 'true_false')
                                    @php $correct = $question->answerOptions->firstWhere('is_correct', true); @endphp
                                    <p class="text-xs text-green-600 font-semibold">Answer: {{ $correct?->option_text ?? '—' }}</p>
                                @elseif($question->type === 'identification')
                                    <p class="text-xs text-green-600 font-semibold">Answer: {{ $question->answerOptions->first()?->option_text ?? '—' }}</p>
                                @elseif($question->type === 'matching')
                                    @foreach($question->answerOptions->sortBy('order') as $opt)
                                        <p class="text-xs text-slate-600">{{ $opt->option_text }} → {{ $opt->match_pair }}</p>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        @if(!$quiz->has_attempts)
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('teacher.quizzes.questions.edit', ['quizId' => $quiz->id, 'questionId' => $question->id]) }}"
                                   class="text-xs bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold px-3 py-1.5 rounded-lg transition">
                                    Edit
                                </a>
                                <form action="{{ route('teacher.quizzes.questions.destroy', ['quizId' => $quiz->id, 'questionId' => $question->id]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this question?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs bg-red-50 hover:bg-red-100 text-red-600 font-semibold px-3 py-1.5 rounded-lg transition">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<script>
    const select = document.getElementById('questionTypeSelect');
    const btn = document.getElementById('addQuestionBtn');
    const base = "{{ route('teacher.quizzes.questions.create', ['quizId' => $quiz->id, 'type' => '__TYPE__']) }}";

    if (select && btn) {
        select.addEventListener('change', function () {
            btn.href = base.replace('__TYPE__', this.value);
        });
    }
</script>
@endsection
