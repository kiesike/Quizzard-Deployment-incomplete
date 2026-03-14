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

    // Get all classes student is enrolled in
    public function myClasses(Request $request)
    {
        $student = $request->user();

        $classes = \App\Models\ClassRoom::whereHas('students', function ($q) use ($student) {
            $q->where('student_id', $student->id);
        })
        ->with(['teacher' => function ($q) {
            $q->select('id', 'name', 'email');
        }])
        ->withCount('students')
        ->withCount('quizzes')
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'classes' => $classes->map(function ($class) {
                return [
                    'id'             => $class->id,
                    'name'           => $class->name,
                    'description'    => $class->description,
                    'class_code'     => $class->class_code,
                    'teacher_name'   => $class->teacher->name,
                    'teacher_email'  => $class->teacher->email,
                    'students_count' => $class->students_count,
                    'quizzes_count'  => $class->quizzes_count,
                ];
            }),
        ]);
    }

    // Join a class using class code
    public function joinClass(Request $request)
    {
        $request->validate([
            'class_code' => 'required|string',
        ]);

        $class = \App\Models\ClassRoom::where('class_code', strtoupper(trim($request->class_code)))
            ->first();

        if (!$class) {
            return response()->json([
                'message' => 'Invalid class code. Please check and try again.',
            ], 404);
        }

        // Check if already enrolled
        if ($class->students()->where('student_id', $request->user()->id)->exists()) {
            return response()->json([
                'message' => 'You are already enrolled in this class.',
            ], 409);
        }

        // Enroll student
        $class->students()->attach($request->user()->id, [
            'joined_at' => now(),
        ]);

        return response()->json([
            'message' => 'Successfully joined the class!',
            'class'   => [
                'id'           => $class->id,
                'name'         => $class->name,
                'description'  => $class->description,
                'class_code'   => $class->class_code,
            ],
        ]);
    }

    // Leave a class
    public function leaveClass(Request $request, $classId)
    {
        $class = \App\Models\ClassRoom::findOrFail($classId);

        // Check if enrolled
        if (!$class->students()->where('student_id', $request->user()->id)->exists()) {
            return response()->json([
                'message' => 'You are not enrolled in this class.',
            ], 404);
        }

        $class->students()->detach($request->user()->id);

        return response()->json([
            'message' => 'You have left the class successfully.',
        ]);
    }
}