<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    // Get dashboard data for the logged-in student
    public function dashboard(Request $request)
    {
        $student = $request->user();

        // Get available published quizzes
        $availableQuizzes = Quiz::where('is_published', true)
            ->with('teacher:id,name')
            ->get()
            ->map(function ($quiz) use ($student) {
                // Check if student already attempted this quiz
                $attempt = QuizAttempt::where('student_id', $student->id)
                    ->where('quiz_id', $quiz->id)
                    ->where('status', 'completed')
                    ->first();

                return [
                    'id'           => $quiz->id,
                    'title'        => $quiz->title,
                    'description'  => $quiz->description,
                    'teacher_name' => $quiz->teacher->name ?? 'Unknown',
                    'already_taken' => $attempt ? true : false,
                    'score'        => $attempt ? $attempt->score : null,
                    'total_points' => $attempt ? $attempt->total_points : null,
                ];
            });

        // Get recent scores
        $recentScores = QuizAttempt::where('student_id', $student->id)
            ->where('status', 'completed')
            ->with('quiz:id,title')
            ->orderBy('completed_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($attempt) {
                return [
                    'quiz_title'   => $attempt->quiz->title ?? 'Unknown',
                    'score'        => $attempt->score,
                    'total_points' => $attempt->total_points,
                    'percentage'   => $attempt->total_points > 0
                        ? round(($attempt->score / $attempt->total_points) * 100)
                        : 0,
                    'completed_at' => $attempt->completed_at,
                ];
            });

        return response()->json([
            'student'          => [
                'id'              => $student->id,
                'name'            => $student->name,
                'email'           => $student->email,
                'profile_picture' => $student->profile_picture,
            ],
            'available_quizzes' => $availableQuizzes,
            'recent_scores'     => $recentScores,
            'total_quizzes_taken' => QuizAttempt::where('student_id', $student->id)
                ->where('status', 'completed')
                ->count(),
        ]);
    }
}