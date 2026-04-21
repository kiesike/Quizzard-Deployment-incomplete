@extends('teacher.layouts.app')

@section('title', 'Add Question')

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">

    {{-- Back --}}
    <a href="{{ route('teacher.quizzes.manage', $quiz->id) }}"
       class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow">
        ← Back to Manage Quiz
    </a>

    <div class="bg-white rounded-2xl shadow p-6">
        <h1 class="text-xl font-bold text-slate-800 mb-1">Add Question</h1>
        <p class="text-sm text-slate-500 mb-6">
            Quiz: <span class="font-semibold text-slate-700">{{ $quiz->title }}</span>
            &nbsp;·&nbsp;
            Type: <span class="font-semibold text-indigo-600 capitalize">{{ str_replace('_', ' ', $type) }}</span>
        </p>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('teacher.quizzes.questions.store', $quiz->id) }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">

            {{-- Question Text --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Question <span class="text-red-500">*</span></label>
                <textarea name="question_text" rows="3" required
                          class="w-full border border-slate-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                          placeholder="Enter your question...">{{ old('question_text') }}</textarea>
            </div>

            {{-- Points --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Points <span class="text-red-500">*</span></label>
                <input type="number" name="points" value="{{ old('points', 1) }}" min="1"
                       class="w-32 border border-slate-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                       required>
            </div>

            {{-- Multiple Choice --}}
            @if($type === 'multiple_choice')
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Answer Choices <span class="text-red-500">*</span></label>
                    <p class="text-xs text-slate-400 mb-3">Select the radio button next to the correct answer.</p>
                    <div id="optionsContainer" class="space-y-2">
                        @for($i = 0; $i < 4; $i++)
                            <div class="flex items-center gap-3">
                                <input type="radio" name="correct_option" value="{{ $i }}"
                                       {{ old('correct_option') == $i ? 'checked' : ($i == 0 && old('correct_option') === null ? 'checked' : '') }}
                                       class="accent-indigo-600 w-4 h-4 shrink-0">
                                <input type="text" name="options[]" value="{{ old('options.'.$i) }}"
                                       placeholder="Option {{ chr(65 + $i) }}"
                                       class="flex-1 border border-slate-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>
                        @endfor
                    </div>
                    <button type="button" onclick="addOption()"
                            class="mt-3 text-xs text-indigo-600 hover:underline font-semibold">
                        + Add Option
                    </button>
                </div>
            @endif

            {{-- True / False --}}
            @if($type === 'true_false')
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Correct Answer <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="radio" name="correct_tf" value="true"
                                   {{ old('correct_tf') === 'true' ? 'checked' : '' }}
                                   class="accent-indigo-600 w-4 h-4">
                            True
                        </label>
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="radio" name="correct_tf" value="false"
                                   {{ old('correct_tf') === 'false' ? 'checked' : '' }}
                                   class="accent-indigo-600 w-4 h-4">
                            False
                        </label>
                    </div>
                </div>
            @endif

            {{-- Identification --}}
            @if($type === 'identification')
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Correct Answer <span class="text-red-500">*</span></label>
                    <input type="text" name="answer" value="{{ old('answer') }}"
                           class="w-full border border-slate-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                           placeholder="Enter the expected answer..." required>
                </div>
            @endif

            {{-- Matching --}}
            @if($type === 'matching')
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Matching Pairs <span class="text-red-500">*</span></label>
                    <p class="text-xs text-slate-400 mb-3">Each premise on the left matches the answer on the right.</p>
                    <div class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-500 uppercase mb-1 px-1">
                        <span>Premise</span>
                        <span>Match</span>
                    </div>
                    <div id="matchingContainer" class="space-y-2">
                        @for($i = 0; $i < 3; $i++)
                            <div class="grid grid-cols-2 gap-2">
                                <input type="text" name="premises[]" value="{{ old('premises.'.$i) }}"
                                       placeholder="Premise {{ $i + 1 }}"
                                       class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <input type="text" name="matches[]" value="{{ old('matches.'.$i) }}"
                                       placeholder="Match {{ $i + 1 }}"
                                       class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            </div>
                        @endfor
                    </div>
                    <button type="button" onclick="addPair()"
                            class="mt-3 text-xs text-indigo-600 hover:underline font-semibold">
                        + Add Pair
                    </button>
                </div>
            @endif

            {{-- Submit --}}
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('teacher.quizzes.manage', $quiz->id) }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                    Cancel
                </a>
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition shadow">
                    Add Question
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let optionCount = 4;

    function addOption() {
        const container = document.getElementById('optionsContainer');
        const i = optionCount++;
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3';
        div.innerHTML = `
            <input type="radio" name="correct_option" value="${i}" class="accent-indigo-600 w-4 h-4 shrink-0">
            <input type="text" name="options[]" placeholder="Option ${String.fromCharCode(65 + i)}"
                   class="flex-1 border border-slate-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        `;
        container.appendChild(div);
    }

    let pairCount = 3;

    function addPair() {
        const container = document.getElementById('matchingContainer');
        const i = pairCount++;
        const div = document.createElement('div');
        div.className = 'grid grid-cols-2 gap-2';
        div.innerHTML = `
            <input type="text" name="premises[]" placeholder="Premise ${i + 1}"
                   class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <input type="text" name="matches[]" placeholder="Match ${i + 1}"
                   class="border border-slate-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        `;
        container.appendChild(div);
    }
</script>
@endsection
