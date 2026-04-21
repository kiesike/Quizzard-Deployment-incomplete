{{-- AI Generate Modal --}}
<div id="aiModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" style="display:none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl flex flex-col" style="max-height:90vh; height:90vh;">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 flex-shrink-0">
            <div>
                <h2 class="text-lg font-bold text-slate-800">✨ Generate Questions with AI</h2>
                <p class="text-xs text-slate-500 mt-0.5">Powered by Groq AI</p>
            </div>
            <button onclick="closeAiModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold leading-none">&times;</button>
        </div>

        {{-- Modal Body --}}
        <div class="overflow-y-auto flex-1 px-6 py-4">

            {{-- STEP 1: Input Form --}}
            <div id="aiStep1">
                <div class="space-y-4">

                    {{-- Topic --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Topic / Keyword</label>
                        <input type="text" id="aiTopic" placeholder="e.g. Photosynthesis, World War 2..."
                               class="w-full border border-slate-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </div>

                    {{-- Passage --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Passage / Text <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea id="aiPassage" rows="4" placeholder="Paste a text or reading passage here..."
                                  class="w-full border border-slate-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        {{-- Number of Questions --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Number of Questions</label>
                            <input type="number" id="aiNumQuestions" value="15" min="1" max="30"
                                   class="w-full border border-slate-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>

                        {{-- Difficulty --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Difficulty</label>
                            <select id="aiDifficulty"
                                    class="w-full border border-slate-300 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <option value="easy">Easy</option>
                                <option value="medium" selected>Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </div>

                    {{-- Question Types --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Question Types</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach([
                                'multiple_choice' => 'Multiple Choice',
                                'true_false'      => 'True / False',
                                'identification'  => 'Identification',
                                'matching'        => 'Matching',
                            ] as $value => $label)
                                <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                    <input type="checkbox" name="aiQuestionTypes" value="{{ $value }}" checked
                                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Error Message --}}
                    <div id="aiError" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm"></div>
                </div>
            </div>

            {{-- STEP 2: Preview --}}
            <div id="aiStep2" class="hidden">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-semibold text-slate-700">Review and edit questions before saving:</p>
                    <button onclick="backToStep1()"
                            class="text-xs text-indigo-600 hover:underline font-semibold">← Regenerate</button>
                </div>
                <div id="aiPreviewList" class="space-y-4"></div>
            </div>

            {{-- Loading --}}
            <div id="aiLoading" class="hidden flex flex-col items-center justify-center py-16 gap-4">
                <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                <p class="text-sm text-slate-500 font-medium">AI is generating your questions...</p>
            </div>

        </div>

        {{-- Modal Footer --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-200 flex-shrink-0">
            <button onclick="closeAiModal()"
                    class="px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                Cancel
            </button>
            <button id="aiBtnGenerate" onclick="generateQuestions()"
                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                ✨ Generate
            </button>
            <button id="aiBtnSave" onclick="saveQuestions()" class="hidden px-4 py-2 rounded-xl text-sm font-semibold text-white bg-green-600 hover:bg-green-700 transition shadow">
                💾 Save to Quiz
            </button>
        </div>
    </div>
</div>

<script>
const QUIZ_ID    = {{ $quiz->id }};
const CSRF_TOKEN = '{{ csrf_token() }}';
let generatedQuestions = [];

function openAiModal()  { const m = document.getElementById('aiModal'); m.style.display = 'flex'; }
function closeAiModal() { const m = document.getElementById('aiModal'); m.style.display = 'none'; resetAiModal(); }

function resetAiModal() {
    showStep(1);
    document.getElementById('aiError').classList.add('hidden');
    document.getElementById('aiPreviewList').innerHTML = '';
    generatedQuestions = [];
}

function backToStep1() { showStep(1); }

function showStep(step) {
    document.getElementById('aiStep1').classList.toggle('hidden', step !== 1);
    document.getElementById('aiStep2').classList.toggle('hidden', step !== 2);
    document.getElementById('aiLoading').classList.add('hidden');
    document.getElementById('aiBtnGenerate').classList.toggle('hidden', step !== 1);
    document.getElementById('aiBtnSave').classList.toggle('hidden', step !== 2);
}

async function generateQuestions() {
    const topic    = document.getElementById('aiTopic').value.trim();
    const passage  = document.getElementById('aiPassage').value.trim();
    const num      = document.getElementById('aiNumQuestions').value;
    const diff     = document.getElementById('aiDifficulty').value;
    const types    = [...document.querySelectorAll('input[name="aiQuestionTypes"]:checked')].map(c => c.value);
    const errBox   = document.getElementById('aiError');

    errBox.classList.add('hidden');

    if (!topic && !passage) {
        errBox.textContent = 'Please provide a topic or a passage.';
        errBox.classList.remove('hidden');
        return;
    }
    if (types.length === 0) {
        errBox.textContent = 'Please select at least one question type.';
        errBox.classList.remove('hidden');
        return;
    }

    // Show loading
    document.getElementById('aiStep1').classList.add('hidden');
    document.getElementById('aiLoading').classList.remove('hidden');
    document.getElementById('aiBtnGenerate').classList.add('hidden');

    try {
        const res = await fetch('{{ route("teacher.quizzes.ai.generate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({
                topic, passage,
                num_questions: parseInt(num),
                difficulty: diff,
                question_types: types,
            }),
            credentials: 'same-origin',
        });

        const data = await res.json();

        if (!data.success) {
            throw new Error(data.message || 'AI generation failed.');
        }

        generatedQuestions = data.questions;
        renderPreview(generatedQuestions);
        showStep(2);

    } catch (err) {
        document.getElementById('aiLoading').classList.add('hidden');
        document.getElementById('aiStep1').classList.remove('hidden');
        document.getElementById('aiBtnGenerate').classList.remove('hidden');
        errBox.textContent = err.message;
        errBox.classList.remove('hidden');
    }
}

function renderPreview(questions) {
    const container = document.getElementById('aiPreviewList');
    container.innerHTML = '';

    questions.forEach((q, idx) => {
        const typeBadgeColor = {
            multiple_choice: 'bg-indigo-50 text-indigo-600',
            true_false:      'bg-yellow-50 text-yellow-600',
            identification:  'bg-green-50 text-green-600',
            matching:        'bg-purple-50 text-purple-600',
        }[q.type] || 'bg-slate-100 text-slate-600';

        const typeLabel = {
            multiple_choice: 'Multiple Choice',
            true_false:      'True / False',
            identification:  'Identification',
            matching:        'Matching',
        }[q.type] || q.type;

        let answersHtml = '';

        if (q.type === 'multiple_choice') {
            answersHtml = q.options.map((opt, oi) => `
                <div class="flex items-center gap-2 mt-1">
                    <input type="radio" name="mc_correct_${idx}" value="${oi}" ${opt.is_correct ? 'checked' : ''}
                           onchange="updateCorrectOption(${idx}, ${oi})" class="text-indigo-600">
                    <input type="text" value="${escHtml(opt.option_text)}"
                           oninput="updateOption(${idx}, ${oi}, this.value)"
                           class="flex-1 border border-slate-200 rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                </div>`).join('');
        } else if (q.type === 'true_false') {
            answersHtml = `
                <div class="flex items-center gap-4 mt-1">
                    <label class="flex items-center gap-1 text-sm">
                        <input type="radio" name="tf_${idx}" value="true" ${q.correct_answer === true ? 'checked' : ''}
                               onchange="updateTrueFalse(${idx}, true)"> True
                    </label>
                    <label class="flex items-center gap-1 text-sm">
                        <input type="radio" name="tf_${idx}" value="false" ${q.correct_answer === false ? 'checked' : ''}
                               onchange="updateTrueFalse(${idx}, false)"> False
                    </label>
                </div>`;
        } else if (q.type === 'identification') {
            answersHtml = `
                <div class="mt-1">
                    <label class="text-xs text-slate-500 font-semibold">Answer:</label>
                    <input type="text" value="${escHtml(q.answer)}"
                           oninput="updateIdentification(${idx}, this.value)"
                           class="w-full border border-slate-200 rounded-lg px-3 py-1 text-sm mt-1 focus:outline-none focus:ring-1 focus:ring-indigo-300">
                </div>`;
        } else if (q.type === 'matching') {
            answersHtml = q.pairs.map((pair, pi) => `
                <div class="flex items-center gap-2 mt-1">
                    <input type="text" value="${escHtml(pair.left)}"
                           oninput="updatePair(${idx}, ${pi}, 'left', this.value)"
                           class="flex-1 border border-slate-200 rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                    <span class="text-slate-400 text-sm">→</span>
                    <input type="text" value="${escHtml(pair.right)}"
                           oninput="updatePair(${idx}, ${pi}, 'right', this.value)"
                           class="flex-1 border border-slate-200 rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-300">
                </div>`).join('');
        }

        const card = document.createElement('div');
        card.id = `q-card-${idx}`;
        card.className = 'border border-slate-200 rounded-2xl p-4';
        card.innerHTML = `
            <div class="flex items-start justify-between gap-4 mb-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-semibold text-slate-400 uppercase">Q${idx + 1}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold ${typeBadgeColor}">${typeLabel}</span>
                </div>
                <button onclick="removeQuestion(${idx})"
                        class="text-xs bg-red-50 hover:bg-red-100 text-red-600 font-semibold px-3 py-1 rounded-lg transition flex-shrink-0">
                    Remove
                </button>
            </div>
            <div class="flex items-center gap-2 mb-2">
                <input type="text" value="${escHtml(q.question_text)}"
                       oninput="updateQuestionText(${idx}, this.value)"
                       class="flex-1 border border-slate-300 rounded-xl px-3 py-1.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                <div class="flex items-center gap-1 flex-shrink-0">
                    <input type="number" value="${q.points}" min="1"
                           oninput="updatePoints(${idx}, this.value)"
                           class="w-16 border border-slate-300 rounded-xl px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-2 focus:ring-indigo-300">
                    <span class="text-xs text-slate-400">pts</span>
                </div>
            </div>
            <div class="pl-1">${answersHtml}</div>`;
        container.appendChild(card);
    });
}

function escHtml(str) {
    return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function updateQuestionText(idx, val) { generatedQuestions[idx].question_text = val; }
function updatePoints(idx, val)       { generatedQuestions[idx].points = parseInt(val) || 1; }
function updateTrueFalse(idx, val)    { generatedQuestions[idx].correct_answer = val; }
function updateIdentification(idx, val) { generatedQuestions[idx].answer = val; }
function updateOption(idx, oi, val)   { generatedQuestions[idx].options[oi].option_text = val; }
function updatePair(idx, pi, side, val) { generatedQuestions[idx].pairs[pi][side] = val; }
function updateCorrectOption(idx, oi) {
    generatedQuestions[idx].options.forEach((o, i) => o.is_correct = i === oi);
}
function removeQuestion(idx) {
    generatedQuestions.splice(idx, 1);
    if (generatedQuestions.length === 0) { backToStep1(); return; }
    renderPreview(generatedQuestions);
}

async function saveQuestions() {
    const btn    = document.getElementById('aiBtnSave');
    const errBox = document.getElementById('aiError');
    errBox.classList.add('hidden');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    try {
        const res = await fetch('{{ route("teacher.quizzes.ai.save", ["quizId" => $quiz->id]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({ questions: generatedQuestions }),
            credentials: 'same-origin',
        });

        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Failed to save questions.');

        closeAiModal();
        window.location.reload();

    } catch (err) {
        errBox.textContent = err.message;
        errBox.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = '💾 Save to Quiz';
    }
}
</script>
