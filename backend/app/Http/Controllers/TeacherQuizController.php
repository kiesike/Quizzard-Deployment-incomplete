<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\AnswerOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeacherQuizController extends Controller
{
    // ─── Quizzes ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $teacher = $request->user();

        $quizzes = Quiz::where('teacher_id', $teacher->id)
            ->withCount('questions')
            ->with(['attempts' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($quiz) {
                $quiz->attempts_count = $quiz->attempts->count();
                $quiz->has_attempts   = $quiz->attempts_count > 0;
                return $quiz;
            });

        return view('teacher.quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        return view('teacher.quizzes.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Quiz::create([
            'teacher_id'   => $request->user()->id,
            'title'        => $request->title,
            'description'  => $request->description,
            'is_published' => false,
        ]);

        return redirect()->route('teacher.quizzes.index')
            ->with('success', 'Quiz created successfully.');
    }

    public function manage(Request $request, $quizId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->with(['questions' => function ($q) {
                $q->orderBy('order')->with('answerOptions');
            }, 'attempts' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->firstOrFail();

        $quiz->has_attempts = $quiz->attempts->count() > 0;

        return view('teacher.quizzes.manage', compact('quiz'));
    }

    public function update(Request $request, $quizId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $quiz->update([
            'title'       => $request->title,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Quiz details updated.');
    }

    public function togglePublish(Request $request, $quizId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->firstOrFail();

        $quiz->update(['is_published' => !$quiz->is_published]);

        return back()->with('success', $quiz->is_published ? 'Quiz published.' : 'Quiz unpublished.');
    }

    // ─── Questions ───────────────────────────────────────────────────────────────

    public function createQuestion(Request $request, $quizId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->with(['attempts' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->firstOrFail();

        if ($quiz->attempts->count() > 0) {
            return redirect()->route('teacher.quizzes.manage', $quizId)
                ->with('error', 'Cannot add questions — this quiz already has attempts.');
        }

        $type = $request->query('type', 'multiple_choice');

        return view('teacher.quizzes.questions.create', compact('quiz', 'type'));
    }

    public function storeQuestion(Request $request, $quizId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->with(['attempts' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->firstOrFail();

        if ($quiz->attempts->count() > 0) {
            return redirect()->route('teacher.quizzes.manage', $quizId)
                ->with('error', 'Cannot add questions — this quiz already has attempts.');
        }

        $type = $request->input('type');

        $baseRules = [
            'type'          => ['required', 'in:multiple_choice,true_false,identification,matching'],
            'question_text' => ['required', 'string'],
            'points'        => ['required', 'integer', 'min:1'],
        ];

        $extraRules = match ($type) {
            'multiple_choice' => [
                'options'          => ['required', 'array', 'min:2'],
                'options.*'        => ['required', 'string'],
                'correct_option'   => ['required', 'integer'],
            ],
            'true_false' => [
                'correct_tf' => ['required', 'in:true,false'],
            ],
            'identification' => [
                'answer' => ['required', 'string'],
            ],
            'matching' => [
                'premises'  => ['required', 'array', 'min:2'],
                'premises.*' => ['required', 'string'],
                'matches'   => ['required', 'array', 'min:2'],
                'matches.*' => ['required', 'string'],
            ],
            default => [],
        };

        $validator = Validator::make($request->all(), array_merge($baseRules, $extraRules));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $nextOrder = $quiz->questions()->max('order') + 1;

        $question = Question::create([
            'quiz_id'       => $quiz->id,
            'type'          => $type,
            'question_text' => $request->question_text,
            'points'        => $request->points,
            'order'         => $nextOrder,
        ]);

        switch ($type) {
            case 'multiple_choice':
                foreach ($request->options as $i => $optionText) {
                    AnswerOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'is_correct'  => ($i == $request->correct_option),
                        'order'       => $i + 1,
                    ]);
                }
                break;

            case 'true_false':
                foreach (['True', 'False'] as $i => $val) {
                    AnswerOption::create([
                        'question_id' => $question->id,
                        'option_text' => $val,
                        'is_correct'  => (strtolower($val) === $request->correct_tf),
                        'order'       => $i + 1,
                    ]);
                }
                break;

            case 'identification':
                AnswerOption::create([
                    'question_id' => $question->id,
                    'option_text' => $request->answer,
                    'is_correct'  => true,
                    'order'       => 1,
                ]);
                break;

            case 'matching':
                foreach ($request->premises as $i => $premise) {
                    AnswerOption::create([
                        'question_id' => $question->id,
                        'option_text' => $premise,
                        'match_pair'  => $request->matches[$i],
                        'is_correct'  => true,
                        'order'       => $i + 1,
                    ]);
                }
                break;
        }

        return redirect()->route('teacher.quizzes.manage', $quizId)
            ->with('success', 'Question added successfully.');
    }

    public function editQuestion(Request $request, $quizId, $questionId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->with(['attempts' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->firstOrFail();

        if ($quiz->attempts->count() > 0) {
            return redirect()->route('teacher.quizzes.manage', $quizId)
                ->with('error', 'Cannot edit questions — this quiz already has attempts.');
        }

        $question = Question::where('quiz_id', $quizId)
            ->where('id', $questionId)
            ->with('answerOptions')
            ->firstOrFail();

        return view('teacher.quizzes.questions.edit', compact('quiz', 'question'));
    }

    public function updateQuestion(Request $request, $quizId, $questionId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->with(['attempts' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->firstOrFail();

        if ($quiz->attempts->count() > 0) {
            return redirect()->route('teacher.quizzes.manage', $quizId)
                ->with('error', 'Cannot edit questions — this quiz already has attempts.');
        }

        $question = Question::where('quiz_id', $quizId)
            ->where('id', $questionId)
            ->firstOrFail();

        $type = $question->type;

        $baseRules = [
            'question_text' => ['required', 'string'],
            'points'        => ['required', 'integer', 'min:1'],
        ];

        $extraRules = match ($type) {
            'multiple_choice' => [
                'options'        => ['required', 'array', 'min:2'],
                'options.*'      => ['required', 'string'],
                'correct_option' => ['required', 'integer'],
            ],
            'true_false' => [
                'correct_tf' => ['required', 'in:true,false'],
            ],
            'identification' => [
                'answer' => ['required', 'string'],
            ],
            'matching' => [
                'premises'   => ['required', 'array', 'min:2'],
                'premises.*' => ['required', 'string'],
                'matches'    => ['required', 'array', 'min:2'],
                'matches.*'  => ['required', 'string'],
            ],
            default => [],
        };

        $validator = Validator::make($request->all(), array_merge($baseRules, $extraRules));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $question->update([
            'question_text' => $request->question_text,
            'points'        => $request->points,
        ]);

        // Replace all answer options
        $question->answerOptions()->delete();

        switch ($type) {
            case 'multiple_choice':
                foreach ($request->options as $i => $optionText) {
                    AnswerOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'is_correct'  => ($i == $request->correct_option),
                        'order'       => $i + 1,
                    ]);
                }
                break;

            case 'true_false':
                foreach (['True', 'False'] as $i => $val) {
                    AnswerOption::create([
                        'question_id' => $question->id,
                        'option_text' => $val,
                        'is_correct'  => (strtolower($val) === $request->correct_tf),
                        'order'       => $i + 1,
                    ]);
                }
                break;

            case 'identification':
                AnswerOption::create([
                    'question_id' => $question->id,
                    'option_text' => $request->answer,
                    'is_correct'  => true,
                    'order'       => 1,
                ]);
                break;

            case 'matching':
                foreach ($request->premises as $i => $premise) {
                    AnswerOption::create([
                        'question_id' => $question->id,
                        'option_text' => $premise,
                        'match_pair'  => $request->matches[$i],
                        'is_correct'  => true,
                        'order'       => $i + 1,
                    ]);
                }
                break;
        }

        return redirect()->route('teacher.quizzes.manage', $quizId)
            ->with('success', 'Question updated successfully.');
    }

    public function destroyQuestion(Request $request, $quizId, $questionId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->with(['attempts' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->firstOrFail();

        if ($quiz->attempts->count() > 0) {
            return back()->with('error', 'Cannot delete questions — this quiz already has attempts.');
        }

        $question = Question::where('quiz_id', $quizId)
            ->where('id', $questionId)
            ->firstOrFail();

        $question->answerOptions()->delete();
        $question->delete();

        return back()->with('success', 'Question deleted.');
    }
}
