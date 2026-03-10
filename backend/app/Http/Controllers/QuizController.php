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
}