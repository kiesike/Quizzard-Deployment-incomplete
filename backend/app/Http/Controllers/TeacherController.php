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
}