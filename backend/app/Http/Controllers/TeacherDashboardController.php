<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Quiz;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('teacher.dashboard.index');
    }

    public function classes(Request $request)
    {
        $teacher = $request->user();

        $classes = ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->withCount(['students', 'quizzes'])
            ->with([
                'quizzes.attempts' => function ($query) {
                    $query->where('status', 'completed');
                },
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($class) {
                $attempts = $class->quizzes->flatMap(function ($quiz) {
                    return $quiz->attempts;
                });

                $attemptsCount = $attempts->count();

                $averageScore = $attemptsCount > 0
                    ? round($attempts->avg(function ($attempt) {
                        if ((float) $attempt->total_points <= 0) {
                            return 0;
                        }

                        return ($attempt->score / $attempt->total_points) * 100;
                    }), 2)
                    : null;

                $class->attempts_count = $attemptsCount;
                $class->average_score = $averageScore;

                return $class;
            });

        return view('teacher.reports.classes', compact('classes'));
    }

    public function quizzes(Request $request)
    {
        $teacher = $request->user();

        $quizzes = Quiz::query()
            ->where('teacher_id', $teacher->id)
            ->withCount('classes')
            ->with([
                'attempts' => function ($query) {
                    $query->where('status', 'completed');
                },
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($quiz) {
                $attempts = $quiz->attempts;
                $attemptsCount = $attempts->count();
                $studentsAttempted = $attempts->pluck('student_id')->unique()->count();

                $averageScore = $attemptsCount > 0
                    ? round($attempts->avg(function ($attempt) {
                        if ((float) $attempt->total_points <= 0) {
                            return 0;
                        }

                        return ($attempt->score / $attempt->total_points) * 100;
                    }), 2)
                    : null;

                $quiz->attempts_count = $attemptsCount;
                $quiz->students_attempted_count = $studentsAttempted;
                $quiz->average_score = $averageScore;

                return $quiz;
            });

        return view('teacher.reports.quizzes', compact('quizzes'));
    }

    public function students(Request $request)
    {
        return view('teacher.reports.students');
    }
}