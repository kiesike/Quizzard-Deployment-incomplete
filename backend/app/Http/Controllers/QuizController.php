<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\StudentAnswer;
use App\Models\Question;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    // Get a single quiz with all questions for taking
    public function show($quizId)
    {
        $quiz = Quiz::where('id', $quizId)
            ->where('is_published', true)
            ->with(['questions' => function ($q) {
                $q->orderBy('order')->with('answerOptions');
            }])
            ->firstOrFail();

        return response()->json([
            'quiz' => [
                'id'          => $quiz->id,
                'title'       => $quiz->title,
                'description' => $quiz->description,
                'questions'   => $quiz->questions->map(function ($question) {
                    return [
                        'id'             => $question->id,
                        'question_text'  => $question->question_text,
                        'question_type'  => $question->question_type,
                        'points'         => $question->points,
                        'order'          => $question->order,
                        'media_path'     => $question->media_path,
                        'media_type'     => $question->media_type,
                        'answer_options' => $question->answerOptions
                            ->map(function ($option) {
                                return [
                                    'id'          => $option->id,
                                    'option_text' => $option->option_text,
                                    'match_pair'  => $option->match_pair,
                                    'order'       => $option->order,
                                    // NOTE: is_correct is NOT sent to student
                                ];
                            }),
                    ];
                }),
            ],
        ]);
    }

    // Start a quiz attempt
    public function startAttempt(Request $request, $quizId)
    {
        $quiz = Quiz::where('id', $quizId)
            ->where('is_published', true)
            ->firstOrFail();

        // Check if student already has a completed attempt
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

        // Check for in-progress attempt
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

        // Create new attempt
        $attempt = QuizAttempt::create([
            'student_id' => $request->user()->id,
            'quiz_id'    => $quizId,
            'score'      => 0,
            'total_points' => $quiz->questions()->sum('points'),
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Quiz started successfully.',
            'attempt' => $attempt,
        ], 201);
    }


    // Submit a quiz and auto-score it
    public function submitQuiz(Request $request, $quizId)
    {
        $request->validate([
            'attempt_id' => 'required|integer',
            'answers'    => 'required|array',
        ]);

        // Find the attempt
        $attempt = QuizAttempt::where('id', $request->attempt_id)
            ->where('student_id', $request->user()->id)
            ->where('quiz_id', $quizId)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $totalScore = 0;
        $answersToSave = [];

        foreach ($request->answers as $answerData) {
            $questionId = $answerData['question_id'];
            $answerType = $answerData['answer_type'];

            $question = Question::with('answerOptions')
                ->findOrFail($questionId);

            $isCorrect    = false;
            $pointsEarned = 0;
            $answerGiven  = '';

            // ── Multiple Choice / True False ──────────────
            if ($answerType === 'multiple_choice' ||
                $answerType === 'true_false') {
                $selectedOptionId =
                    $answerData['selected_option_id'] ?? null;
                $answerGiven = (string) $selectedOptionId;

                if ($selectedOptionId) {
                    $correctOption = $question->answerOptions
                        ->where('is_correct', true)
                        ->first();

                    if ($correctOption &&
                        $correctOption->id == $selectedOptionId) {
                        $isCorrect    = true;
                        $pointsEarned = $question->points;
                    }
                }
            }

            // ── Identification ────────────────────────────
            elseif ($answerType === 'identification') {
                $answerText  =
                    trim($answerData['answer_text'] ?? '');
                $answerGiven = $answerText;

                $correctOption = $question->answerOptions
                    ->where('is_correct', true)
                    ->first();

                if ($correctOption &&
                    strtolower($answerText) ===
                    strtolower(trim($correctOption->option_text))) {
                    $isCorrect    = true;
                    $pointsEarned = $question->points;
                }
            }

            // ── Matching ──────────────────────────────────
            elseif ($answerType === 'matching') {
                $matches     = $answerData['matches'] ?? [];
                $answerGiven = json_encode($matches);

                $correctPairs  = $question->answerOptions;
                $totalPairs    = $correctPairs->count();
                $correctCount  = 0;
                $pointsPerPair = $totalPairs > 0
                    ? $question->points / $totalPairs
                    : 0;

                foreach ($correctPairs as $pair) {
                    $columnA       = $pair->option_text;
                    $correctB      = $pair->match_pair;
                    $studentB      = $matches[$columnA] ?? '';

                    if (strtolower(trim($studentB)) ===
                        strtolower(trim($correctB))) {
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

        // Save all answers
        StudentAnswer::insert($answersToSave);

        // Update attempt as completed
        $attempt->update([
            'score'        => $totalScore,
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        // Load questions with correct answers for result screen
        $quiz = Quiz::with(['questions' => function ($q) {
            $q->orderBy('order')->with('answerOptions');
        }])->findOrFail($quizId);

        // Build detailed results
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

        $percentage = $attempt->total_points > 0
            ? round(($totalScore / $attempt->total_points) * 100)
            : 0;

        return response()->json([
            'message'          => 'Quiz submitted successfully!',
            'attempt_id'       => $attempt->id,
            'score'            => $totalScore,
            'total_points'     => $attempt->total_points,
            'percentage'       => $percentage,
            'quiz_title'       => $quiz->title,
            'question_results' => $questionResults,
        ]);
    }


}