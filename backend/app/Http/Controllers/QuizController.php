<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    // ─── GET /api/quizzes ─────────────────────────────────────────
    public function index()
    {
        $quizzes = Quiz::where('teacher_id', Auth::id())
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $quizzes,
        ]);
    }

    // ─── POST /api/quizzes ────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $quiz = Quiz::create([
            'teacher_id'   => Auth::id(),
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'is_published' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quiz created successfully.',
            'data'    => $quiz,
        ], 201);
    }

    // ─── GET /api/quizzes/{quizId} ────────────────────────────────
    public function show($quizId)
    {
        $quiz = Quiz::with(['questions' => function ($q) {
            $q->orderBy('order')->with(['answerOptions' => function ($ao) {
                $ao->orderBy('order');
            }]);
        }])->findOrFail($quizId);

        $user = Auth::user();
        if ($user->role === 'student') {
            $quiz->questions->each(function ($question) {
                $question->answerOptions->each(function ($option) {
                    unset($option->is_correct);
                });
            });
        }

        return response()->json([
            'success' => true,
            'data'    => $quiz,
        ]);
    }

    // ─── PUT /api/quizzes/{quizId} ────────────────────────────────
    public function update(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        if ($quiz->teacher_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // Block edit if quiz has attempts
        $hasAttempts = QuizAttempt::where('quiz_id', $quizId)->exists();
        if ($hasAttempts) {
            return response()->json([
                'message' => 'This quiz cannot be edited because students have already taken it.',
            ], 403);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $quiz->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? $quiz->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quiz updated successfully.',
            'data'    => $quiz,
        ]);
    }

    // ─── DELETE /api/quizzes/{quizId} ─────────────────────────────
    public function destroy($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        if ($quiz->teacher_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        // Block delete if quiz has attempts
        $hasAttempts = QuizAttempt::where('quiz_id', $quizId)->exists();
        if ($hasAttempts) {
            return response()->json([
                'message' => 'This quiz cannot be deleted because students have already taken it.',
            ], 403);
        }

        $quiz->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quiz deleted successfully.',
        ]);
    }

    // ─── PATCH /api/quizzes/{quizId}/publish-toggle ───────────────
    public function publishToggle($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        if ($quiz->teacher_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $quiz->is_published = !$quiz->is_published;
        $quiz->save();

        return response()->json([
            'success' => true,
            'message' => $quiz->is_published ? 'Quiz published.' : 'Quiz unpublished.',
            'data'    => $quiz,
        ]);
    }

    // ─── POST /api/quizzes/{quizId}/start ─────────────────────────
    public function startAttempt(Request $request, $quizId)
    {
        $quiz = Quiz::where('id', $quizId)
            ->where('is_published', true)
            ->firstOrFail();

        $existingAttempt = QuizAttempt::where('student_id', $request->user()->id)
            ->where('quiz_id', $quizId)
            ->where('status', 'completed')
            ->first();

        if ($existingAttempt) {
            return response()->json([
                'message' => 'You have already completed this quiz.',
                'attempt' => $existingAttempt,
            ], 409);
        }

        $inProgressAttempt = QuizAttempt::where('student_id', $request->user()->id)
            ->where('quiz_id', $quizId)
            ->where('status', 'in_progress')
            ->first();

        if ($inProgressAttempt) {
            return response()->json([
                'message' => 'Resuming existing attempt.',
                'attempt' => $inProgressAttempt,
            ]);
        }

        $attempt = QuizAttempt::create([
            'student_id'   => $request->user()->id,
            'quiz_id'      => $quizId,
            'score'        => 0,
            'total_points' => $quiz->questions()->sum('points'),
            'status'       => 'in_progress',
            'started_at'   => now(),
        ]);

        return response()->json([
            'message' => 'Quiz started successfully.',
            'attempt' => $attempt,
        ], 201);
    }

    // ─── POST /api/quizzes/{quizId}/submit ────────────────────────
    public function submitQuiz(Request $request, $quizId)
    {
        $request->validate([
            'attempt_id' => 'required|integer',
            'answers'    => 'required|array',
        ]);

        $attempt = QuizAttempt::where('id', $request->attempt_id)
            ->where('student_id', $request->user()->id)
            ->where('quiz_id', $quizId)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $totalScore    = 0;
        $answersToSave = [];

        foreach ($request->answers as $answerData) {
            $questionId = $answerData['question_id'];
            $answerType = $answerData['answer_type'];

            $question = Question::with('answerOptions')->findOrFail($questionId);

            $isCorrect    = false;
            $pointsEarned = 0;
            $answerGiven  = '';

            if ($answerType === 'multiple_choice' || $answerType === 'true_false') {
                $selectedOptionId = $answerData['selected_option_id'] ?? null;
                $answerGiven      = (string) $selectedOptionId;

                if ($selectedOptionId) {
                    $correctOption = $question->answerOptions->where('is_correct', true)->first();
                    if ($correctOption && $correctOption->id == $selectedOptionId) {
                        $isCorrect    = true;
                        $pointsEarned = $question->points;
                    }
                }
            } elseif ($answerType === 'identification') {
                $answerText  = trim($answerData['answer_text'] ?? '');
                $answerGiven = $answerText;

                $correctOption = $question->answerOptions->where('is_correct', true)->first();
                if ($correctOption && strtolower($answerText) === strtolower(trim($correctOption->option_text))) {
                    $isCorrect    = true;
                    $pointsEarned = $question->points;
                }
            } elseif ($answerType === 'matching') {
                $matches     = $answerData['matches'] ?? [];
                $answerGiven = json_encode($matches);

                $correctPairs  = $question->answerOptions;
                $totalPairs    = $correctPairs->count();
                $correctCount  = 0;
                $pointsPerPair = $totalPairs > 0 ? $question->points / $totalPairs : 0;

                foreach ($correctPairs as $pair) {
                    $studentB = $matches[$pair->option_text] ?? '';
                    if (strtolower(trim($studentB)) === strtolower(trim($pair->match_pair))) {
                        $correctCount++;
                    }
                }

                $pointsEarned = round($correctCount * $pointsPerPair);
                $isCorrect    = $correctCount === $totalPairs;
            }

            $totalScore += $pointsEarned;

            $answersToSave[] = [
                'attempt_id'    => $attempt->id,
                'question_id'   => $questionId,
                'answer_given'  => $answerGiven,
                'is_correct'    => $isCorrect,
                'points_earned' => $pointsEarned,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        StudentAnswer::insert($answersToSave);

        $quiz = Quiz::with(['questions' => function ($q) {
            $q->orderBy('order')->with('answerOptions');
        }])->findOrFail($quizId);

        // Recalculate total_points from actual questions at submit time
        $actualTotalPoints = $quiz->questions()->sum('points');

        $attempt->update([
            'score'        => $totalScore,
            'total_points' => $actualTotalPoints,
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        $questionResults = [];
        foreach ($quiz->questions as $question) {
            $savedAnswer = StudentAnswer::where('attempt_id', $attempt->id)
                ->where('question_id', $question->id)
                ->first();

            $questionResults[] = [
                'id'             => $question->id,
                'question_text'  => $question->question_text,
                'question_type'  => $question->question_type,
                'points'         => $question->points,
                'points_earned'  => $savedAnswer?->points_earned ?? 0,
                'is_correct'     => $savedAnswer?->is_correct ?? false,
                'answer_given'   => $savedAnswer?->answer_given ?? '',
                'answer_options' => $question->answerOptions->map(function ($opt) {
                    return [
                        'id'          => $opt->id,
                        'option_text' => $opt->option_text,
                        'is_correct'  => $opt->is_correct,
                        'match_pair'  => $opt->match_pair,
                        'order'       => $opt->order,
                    ];
                }),
            ];
        }

        $percentage = $actualTotalPoints > 0
            ? round(($totalScore / $actualTotalPoints) * 100)
            : 0;

        return response()->json([
            'message'          => 'Quiz submitted successfully!',
            'attempt_id'       => $attempt->id,
            'score'            => $totalScore,
            'total_points'     => $actualTotalPoints,
            'percentage'       => $percentage,
            'quiz_title'       => $quiz->title,
            'question_results' => $questionResults,
        ]);
    }
}