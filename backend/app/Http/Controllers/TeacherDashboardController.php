<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Quiz;
use Illuminate\Http\Request;
use App\Exports\ClassDetailExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentsExport;
use App\Exports\StudentQuizInfoExport;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;



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














    public function quizQuestions(Request $request, $quizId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->with(['questions' => function ($query) {
                $query->orderBy('order')->with('answerOptions');
            }])
            ->firstOrFail();

        return view('teacher.reports.quiz_questions', compact('quiz'));
    }

    public function quizAnswers(Request $request, $quizId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->with(['questions' => function ($query) {
                $query->orderBy('order')->with('answerOptions');
            }])
            ->firstOrFail();

        return view('teacher.reports.quiz_answers', compact('quiz'));
    }

    public function exportQuizQuestionsDocx(Request $request, $quizId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->with(['questions' => function ($query) {
                $query->orderBy('order')->with('answerOptions');
            }])
            ->firstOrFail();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection([
            'marginTop'    => 1440,
            'marginBottom' => 1440,
            'marginLeft'   => 1440,
            'marginRight'  => 1440,
        ]);

        // Title
        $titleStyle = ['bold' => true, 'size' => 16, 'name' => 'Times New Roman'];
        $section->addText($quiz->title, $titleStyle, ['alignment' => 'center']);
        $section->addText('Test Questionnaire', ['size' => 12, 'italic' => true, 'name' => 'Times New Roman'], ['alignment' => 'center']);
        $section->addTextBreak(1);

        $questionNumber = 1;

        foreach ($quiz->questions as $question) {
            $qLabel = $questionNumber .  $question->question_text . '. (' . $question->points . ' ' . ($question->points == 1 ? 'pt' : 'pts') . ') ';
            $section->addText($qLabel, ['bold' => true, 'name' => 'Times New Roman', 'size' => 12]);

            switch ($question->question_type) {
                case 'multiple_choice':
                    $letters = ['A', 'B', 'C', 'D'];
                    foreach ($question->answerOptions->sortBy('order') as $i => $option) {
                        $letter = $letters[$i] ?? chr(65 + $i);
                        $section->addText('    ' . $letter . '. ' . $option->option_text, ['name' => 'Times New Roman', 'size' => 12]);
                    }
                    break;

                case 'true_false':
                    $section->addText('    A. True', ['name' => 'Times New Roman', 'size' => 12]);
                    $section->addText('    B. False', ['name' => 'Times New Roman', 'size' => 12]);
                    break;

                case 'identification':
                    $section->addText('    Answer: ___________________________', ['name' => 'Times New Roman', 'size' => 12]);
                    break;

                case 'matching':
                    $pairs = $question->answerOptions->sortBy('order');
                    $tableStyle = [
                        'borderSize'        => 0,
                        'borderColor'       => 'ffffff', // match page background
                        'cellMargin'        => 80,
                        'borderTopSize'     => 0,
                        'borderBottomSize'  => 0,
                        'borderLeftSize'    => 0,
                        'borderRightSize'   => 0,
                        'borderTopColor'    => 'ffffff',
                        'borderBottomColor' => 'ffffff',
                        'borderLeftColor'   => 'ffffff',
                        'borderRightColor'  => 'ffffff',
                    ];
                    $table = $section->addTable($tableStyle);
                    foreach ($pairs as $pair) {
                        $table->addRow();
                        $table->addCell(3000)->addText($pair->option_text, ['name' => 'Times New Roman', 'size' => 12]);
                        $table->addCell(1000)->addText('', ['name' => 'Times New Roman', 'size' => 12]);
                        $table->addCell(3000)->addText($pair->match_pair, ['name' => 'Times New Roman', 'size' => 12]);
                    }
                    break;
            }

            $section->addTextBreak(1);
            $questionNumber++;
        }

        $filename = 'quiz_' . $quizId . '_questions.docx';
        $tempPath = storage_path('app/' . $filename);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function exportQuizQuestionsPdf(Request $request, $quizId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->with(['questions' => function ($query) {
                $query->orderBy('order')->with('answerOptions');
            }])
            ->firstOrFail();

        $html = view('teacher.reports.pdf.quiz_questions_pdf', compact('quiz'))->render();

        $options = new Options();
        $options->set('defaultFont', 'Times New Roman');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'quiz_' . $quizId . '_questions.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportQuizAnswersDocx(Request $request, $quizId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->with(['questions' => function ($query) {
                $query->orderBy('order')->with('answerOptions');
            }])
            ->firstOrFail();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection([
            'marginTop'    => 1440,
            'marginBottom' => 1440,
            'marginLeft'   => 1440,
            'marginRight'  => 1440,
        ]);

        // Title
        $titleStyle = ['bold' => true, 'size' => 16, 'name' => 'Times New Roman'];
        $section->addText($quiz->title, $titleStyle, ['alignment' => 'center']);
        $section->addText('Answer Key', ['size' => 12, 'italic' => true, 'name' => 'Times New Roman'], ['alignment' => 'center']);
        $section->addTextBreak(1);

        $questionNumber = 1;

        foreach ($quiz->questions as $question) {
            $qLabel = $questionNumber . '. (' . $question->points . ' ' . ($question->points == 1 ? 'pt' : 'pts') . ') ' . $question->question_text;
            $section->addText($qLabel, ['bold' => true, 'name' => 'Times New Roman', 'size' => 12]);

            switch ($question->question_type) {
                case 'multiple_choice':
                    $correct = $question->answerOptions->firstWhere('is_correct', true);
                    $section->addText('    Answer: ' . ($correct?->option_text ?? '—'), ['name' => 'Times New Roman', 'size' => 12, 'color' => '16A34A']);
                    break;

                case 'true_false':
                    $correct = $question->answerOptions->firstWhere('is_correct', true);
                    $section->addText('    Answer: ' . ($correct?->option_text ?? '—'), ['name' => 'Times New Roman', 'size' => 12, 'color' => '16A34A']);
                    break;

                case 'identification':
                    $correct = $question->answerOptions->first();
                    $section->addText('    Answer: ' . ($correct?->option_text ?? '—'), ['name' => 'Times New Roman', 'size' => 12, 'color' => '16A34A']);
                    break;

                case 'matching':
                    $pairs = $question->answerOptions->sortBy('order');
                    $tableStyle = ['borderSize' => 6, 'borderColor' => 'CCCCCC', 'cellMargin' => 80];
                    $cellStyle  = ['bgColor' => 'F0FDF4'];
                    $table = $section->addTable($tableStyle);

                    // Header row
                    $table->addRow();
                    $table->addCell(3500)->addText('Premise', ['bold' => true, 'name' => 'Times New Roman', 'size' => 11]);
                    $table->addCell(3500)->addText('Correct Match', ['bold' => true, 'name' => 'Times New Roman', 'size' => 11]);

                    foreach ($pairs as $pair) {
                        $table->addRow();
                        $table->addCell(3500, $cellStyle)->addText($pair->option_text, ['name' => 'Times New Roman', 'size' => 12]);
                        $table->addCell(3500, $cellStyle)->addText($pair->match_pair, ['name' => 'Times New Roman', 'size' => 12]);
                    }
                    break;
            }

            $section->addTextBreak(1);
            $questionNumber++;
        }

        $filename = 'quiz_' . $quizId . '_answers.docx';
        $tempPath = storage_path('app/' . $filename);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function exportQuizAnswersPdf(Request $request, $quizId)
    {
        $teacher = $request->user();

        $quiz = Quiz::where('teacher_id', $teacher->id)
            ->where('id', $quizId)
            ->with(['questions' => function ($query) {
                $query->orderBy('order')->with('answerOptions');
            }])
            ->firstOrFail();

        $html = view('teacher.reports.pdf.quiz_answers_pdf', compact('quiz'))->render();

        $options = new Options();
        $options->set('defaultFont', 'Times New Roman');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'quiz_' . $quizId . '_answers.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }







}