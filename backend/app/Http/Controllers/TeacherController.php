<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function dashboard(Request $request)
    {
        $teacher = $request->user();

        // Get all quizzes by this teacher
        $quizzes = Quiz::where('teacher_id', $teacher->id)
            ->withCount('questions')
            ->get()
            ->map(function ($quiz) {
                // Count how many students attempted this quiz
                $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('status', 'completed')
                    ->count();

                // Get average score
                $avgScore = QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('status', 'completed')
                    ->avg('score');

                return [
                    'id'             => $quiz->id,
                    'title'          => $quiz->title,
                    'description'    => $quiz->description,
                    'is_published'   => $quiz->is_published,
                    'questions_count' => $quiz->questions_count,
                    'attempts_count' => $attemptCount,
                    'average_score'  => $avgScore ? round($avgScore, 1) : null,
                    'created_at'     => $quiz->created_at,
                ];
            });

        // Total students who took any of this teacher's quizzes
        $totalStudents = QuizAttempt::whereIn(
            'quiz_id',
            Quiz::where('teacher_id', $teacher->id)->pluck('id')
        )
            ->where('status', 'completed')
            ->distinct('student_id')
            ->count('student_id');

        return response()->json([
            'teacher' => [
                'id'              => $teacher->id,
                'name'            => $teacher->name,
                'email'           => $teacher->email,
                'profile_picture' => $teacher->profile_picture,
            ],
            'quizzes'           => $quizzes,
            'total_quizzes'     => $quizzes->count(),
            'published_quizzes' => $quizzes->where('is_published', true)->count(),
            'total_students'    => $totalStudents,
        ]);
    }

    // Get all student attempts for a specific quiz
    public function quizResults(Request $request, $quizId)
    {
        $quiz = \App\Models\Quiz::where('id', $quizId)
            ->where('teacher_id', $request->user()->id)
            ->firstOrFail();

        $attempts = \App\Models\QuizAttempt::where('quiz_id', $quizId)
            ->where('status', 'completed')
            ->with('student')
            ->orderBy('completed_at', 'desc')
            ->get();

        $results = $attempts->map(function ($attempt) use ($quiz) {
            $percentage = $attempt->total_points > 0
                ? round(($attempt->score / $attempt->total_points) * 100)
                : 0;

            return [
                'attempt_id'   => $attempt->id,
                'student_id'   => $attempt->student->id,
                'student_name' => $attempt->student->name,
                'student_email'=> $attempt->student->email,
                'score'        => $attempt->score,
                'total_points' => $attempt->total_points,
                'percentage'   => $percentage,
                'completed_at' => $attempt->completed_at,
            ];
        });

        return response()->json([
            'quiz'    => [
                'id'    => $quiz->id,
                'title' => $quiz->title,
            ],
            'total_attempts' => $attempts->count(),
            'average_score'  => $attempts->count() > 0
                ? round($attempts->avg('score'), 1)
                : 0,
            'results' => $results,
        ]);
    }

    // Get detailed answer breakdown for one student attempt
    public function attemptDetail(Request $request, $quizId, $attemptId)
    {
        // Verify quiz belongs to teacher
        $quiz = \App\Models\Quiz::where('id', $quizId)
            ->where('teacher_id', $request->user()->id)
            ->firstOrFail();

        $attempt = \App\Models\QuizAttempt::where('id', $attemptId)
            ->where('quiz_id', $quizId)
            ->with('student')
            ->firstOrFail();

        $studentAnswers = \App\Models\StudentAnswer::where('attempt_id', $attemptId)
            ->with(['question' => function ($q) {
                $q->with('answerOptions');
            }])
            ->get();

        $percentage = $attempt->total_points > 0
            ? round(($attempt->score / $attempt->total_points) * 100)
            : 0;

        $questionResults = $studentAnswers->map(function ($answer) {
            $question = $answer->question;
            return [
                'id'            => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'points'        => $question->points,
                'points_earned' => $answer->points_earned,
                'is_correct'    => $answer->is_correct,
                'answer_given'  => $answer->answer_given,
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
        });

        return response()->json([
            'quiz' => [
                'id'    => $quiz->id,
                'title' => $quiz->title,
            ],
            'attempt' => [
                'id'           => $attempt->id,
                'score'        => $attempt->score,
                'total_points' => $attempt->total_points,
                'percentage'   => $percentage,
                'completed_at' => $attempt->completed_at,
            ],
            'student' => [
                'id'    => $attempt->student->id,
                'name'  => $attempt->student->name,
                'email' => $attempt->student->email,
            ],
            'question_results' => $questionResults,
        ]);
    }


}