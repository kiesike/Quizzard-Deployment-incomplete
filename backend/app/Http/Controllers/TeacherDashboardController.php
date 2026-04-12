<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Quiz;
use Illuminate\Http\Request;
use App\Exports\ClassDetailExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentsExport;
use App\Exports\StudentQuizInfoExport;

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
        $teacher = $request->user();

        $classes = ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->with([
                'students.studentProfile',
                'quizzes.attempts' => function ($query) {
                    $query->where('status', 'completed');
                },
            ])
            ->get();

        $students = $classes
            ->flatMap(function ($class) {
                return $class->students->map(function ($student) use ($class) {
                    $student->teacher_class_id = $class->id;
                    return $student;
                });
            })
            ->unique('id')
            ->map(function ($student) use ($classes) {
                $studentClasses = $classes->filter(function ($class) use ($student) {
                    return $class->students->contains('id', $student->id);
                })->map(function ($class) {
                    return ['id' => $class->id, 'name' => $class->name];
                })->values();

                $student->enrolled_classes = $studentClasses;

                return $student;
            })
            ->sortBy(fn($s) => $s->first_name . ' ' . $s->surname)
            ->values();

        return view('teacher.reports.students', compact('students'));
    }


    public function exportStudents(Request $request)
    {
        $teacher = $request->user();

        $classes = ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->with([
                'students.studentProfile',
            ])
            ->get();

        $students = $classes
            ->flatMap(fn($class) => $class->students)
            ->unique('id')
            ->map(function ($student) {
                return [
                    $student->studentProfile?->student_id ?? '—',
                    $student->studentProfile?->gender ? ucfirst($student->studentProfile->gender) : '—',
                    $student->studentProfile?->date_of_birth?->format('M d, Y') ?? '—',
                    $student->studentProfile?->contact_number ?? '—',
                    $student->studentProfile?->grade_level ?? '—',
                    $student->studentProfile?->section ?? '—',
                ];
            })
            ->values();

        return Excel::download(new StudentsExport($students), 'students_report.xlsx');
    }

    public function studentQuizInfo(Request $request, $studentId, $classId)
    {
        $teacher = $request->user();

        $class = ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->where('id', $classId)
            ->with([
                'quizzes.attempts' => function ($query) use ($studentId) {
                    $query->where('student_id', $studentId)
                        ->where('status', 'completed');
                },
            ])
            ->firstOrFail();

        $student = $class->students()->where('users.id', $studentId)->firstOrFail();

        $quizzes = $class->quizzes->map(function ($quiz) use ($studentId) {
            $attempt = $quiz->attempts->first();
            
            return (object) [
                'name'           => $quiz->title,
                'score'          => $attempt?->score ?? null,
                'total'          => $attempt?->total_points ?? null,
                'status'         => $attempt ? 'Taken' : 'Not Taken',
                'date_published' => $quiz->created_at,
                'date_completed' => $attempt?->completed_at ?? null,
            ];
        });

        return view('teacher.reports.student_quiz_info', compact('class', 'student', 'quizzes'));
    }


    public function exportStudentQuizInfo(Request $request, $studentId, $classId)
    {
        $teacher = $request->user();

        $class = ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->where('id', $classId)
            ->with([
                'quizzes.attempts' => function ($query) use ($studentId) {
                    $query->where('student_id', $studentId)
                        ->where('status', 'completed');
                },
            ])
            ->firstOrFail();

        $student = $class->students()->where('users.id', $studentId)->firstOrFail();

        $quizzes = $class->quizzes->map(function ($quiz) use ($studentId) {
            $attempt = $quiz->attempts->first();

            return (object) [
                'name'           => $quiz->title,
                'score'          => $attempt?->score ?? null,
                'total'          => $attempt?->total_points ?? null,
                'status'         => $attempt ? 'Taken' : 'Not Taken',
                'date_published' => $quiz->created_at,
                'date_completed' => $attempt?->completed_at ?? null,
            ];
        });

        $filename = 'student_' . $studentId . '_class_' . $classId . '_quiz_info.xlsx';

        return Excel::download(new StudentQuizInfoExport($quizzes), $filename);
    }




    public function classDetail(Request $request, $classId)
    {
        $teacher = $request->user();

        $class = ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->where('id', $classId)
            ->with([
                'students.studentProfile',
                'quizzes',
                'quizzes.attempts' => function ($query) {
                    $query->where('status', 'completed');
                },
            ])
            ->firstOrFail();

        $totalQuizzes = $class->quizzes->count();

        $students = $class->students
            ->map(function ($student) use ($class, $totalQuizzes) {
                $attempts = $class->quizzes
                    ->flatMap(function ($quiz) use ($student) {
                        return $quiz->attempts->where('student_id', $student->id);
                    })
                    ->unique('id')
                    ->values();

                $quizzesTaken = $attempts->count();

                $sumOfScores = $attempts->sum(function ($attempt) {
                    if ((float) $attempt->total_points <= 0) {
                        return 0;
                    }
                    return ($attempt->score / $attempt->total_points) * 100;
                });

                $overallGrade = $totalQuizzes > 0
                    ? round($sumOfScores / $totalQuizzes, 2)
                    : null;

                $student->quizzes_taken = $quizzesTaken;
                $student->overall_grade = $overallGrade;

                return $student;
            })
            ->sortBy(fn($s) => $s->first_name . ' ' . $s->surname)
            ->values();

        return view('teacher.reports.class_detail', compact('class', 'students', 'totalQuizzes'));
    }


    public function classQuizzes(Request $request, $classId)
    {
        $teacher = $request->user();

        $class = ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->where('id', $classId)
            ->with([
                'quizzes.questions',
                'quizzes.attempts' => function ($query) {
                    $query->where('status', 'completed');
                },
                'students',
            ])
            ->firstOrFail();

        $quizzes = $class->quizzes
            ->map(function ($quiz) {
                $studentsTaken = $quiz->attempts
                    ->pluck('student_id')
                    ->unique()
                    ->count();

                $quiz->questions_count = $quiz->questions->count();
                $quiz->students_taken_count = $studentsTaken;

                return $quiz;
            })
            ->sortBy('title')
            ->values();

        return view('teacher.reports.class_quizzes', compact('class', 'quizzes'));
    }

    public function classQuizDetail(Request $request, $classId, $quizId)
    {
        $teacher = $request->user();

        $class = ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->where('id', $classId)
            ->with([
                'students.studentProfile',
            ])
            ->firstOrFail();

        $quiz = $class->quizzes()->where('quiz_id', $quizId)->firstOrFail();

        $quiz->load([
            'questions',
            'attempts' => function ($query) use ($classId) {
                $query->where('status', 'completed')
                    ->whereIn('student_id', function ($subQuery) use ($classId) {
                        $subQuery->select('student_id')
                            ->from('class_students')
                            ->where('class_id', $classId);
                    });
            },
        ]);

        $totalPoints = $quiz->questions->sum('points');

        $students = $class->students
            ->map(function ($student) use ($quiz, $totalPoints) {
                $attempt = $quiz->attempts
                    ->where('student_id', $student->id)
                    ->sortByDesc('completed_at')
                    ->first();

                $student->quiz_score = $attempt?->score ?? null;
                $student->quiz_total_points = $totalPoints;
                $student->quiz_percentage = ($attempt && $totalPoints > 0)
                    ? round(($attempt->score / $totalPoints) * 100, 2)
                    : null;
                $student->quiz_status = $attempt ? 'Taken' : 'Not Taken';

                return $student;
            })
            ->sortBy(fn($s) => $s->first_name . ' ' . $s->surname)
            ->values();

        return view('teacher.reports.class_quiz_detail', compact('class', 'quiz', 'students', 'totalPoints'));
    }


    public function exportClassDetail(Request $request, $classId)
    {
        $teacher = $request->user();

        $class = ClassRoom::query()
            ->where('teacher_id', $teacher->id)
            ->where('id', $classId)
            ->with([
                'students.studentProfile',
                'quizzes',
                'quizzes.attempts' => function ($query) {
                    $query->where('status', 'completed');
                },
            ])
            ->firstOrFail();

        $totalQuizzes = $class->quizzes->count();

        $students = $class->students
            ->map(function ($student) use ($class, $totalQuizzes) {
                $attempts = $class->quizzes
                    ->flatMap(function ($quiz) use ($student) {
                        return $quiz->attempts->where('student_id', $student->id);
                    })
                    ->unique('id')
                    ->values();

                $quizzesTaken = $attempts->count();

                $sumOfScores = $attempts->sum(function ($attempt) {
                    if ((float) $attempt->total_points <= 0) {
                        return 0;
                    }
                    return ($attempt->score / $attempt->total_points) * 100;
                });

                $overallGrade = $totalQuizzes > 0
                    ? round($sumOfScores / $totalQuizzes, 2)
                    : null;

                $student->quizzes_taken = $quizzesTaken;
                $student->overall_grade = $overallGrade;

                return $student;
            })
            ->sortBy(fn($s) => $s->first_name . ' ' . $s->surname)
            ->values();

        $filename = 'class_' . $classId . '_report.xlsx';

        return Excel::download(new ClassDetailExport($students, $totalQuizzes), $filename);
    }


}