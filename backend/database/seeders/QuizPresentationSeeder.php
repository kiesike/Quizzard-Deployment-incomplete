<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class QuizPresentationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | TEACHER
            |--------------------------------------------------------------------------
            */
            $teacher = DB::table('users')
                ->where('email', 'presentation.teacher@quizzard.com')
                ->first();

            if (!$teacher) {
                $teacherId = DB::table('users')->insertGetId([
                    'name' => 'Presentation Teacher',
                    'email' => 'presentation.teacher@quizzard.com',
                    'password' => Hash::make('Teacher@123'),
                    'role' => 'teacher',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $teacherId = $teacher->id;
            }

            /*
            |--------------------------------------------------------------------------
            | CLASS
            |--------------------------------------------------------------------------
            */
            $class = DB::table('classes')
                ->where('class_code', 'PRESENT2026')
                ->first();

            if (!$class) {
                $classId = DB::table('classes')->insertGetId([
                    'teacher_id' => $teacherId,
                    'name' => 'Presentation Test Class',
                    'description' => 'Generated for final presentation',
                    'class_code' => 'PR26',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $classId = $class->id;
            }

            /*
            |--------------------------------------------------------------------------
            | STUDENTS
            |--------------------------------------------------------------------------
            */
            $studentIds = [];

            for ($i = 1; $i <= 10; $i++) {
                $email = "presentation.student{$i}@quizzard.com";

                $student = DB::table('users')
                    ->where('email', $email)
                    ->first();

                if (!$student) {
                    $studentId = DB::table('users')->insertGetId([
                        'name' => "Student $i",
                        'email' => $email,
                        'password' => Hash::make('Student@123'),
                        'role' => 'student',
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $studentId = $student->id;
                }

                $studentIds[] = $studentId;

                $enrolled = DB::table('class_students')
                    ->where('class_id', $classId)
                    ->where('student_id', $studentId)
                    ->exists();

                if (!$enrolled) {
                    DB::table('class_students')->insert([
                        'class_id' => $classId,
                        'student_id' => $studentId,
                        'joined_at' => now(),
                    ]);
                }

                $profileExists = DB::table('student_profiles')
                    ->where('user_id', $studentId)
                    ->exists();

                if (!$profileExists) {
                    DB::table('student_profiles')->insert([
                        'user_id' => $studentId,
                        'student_id' => 'PRES-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                        'gender' => $i % 2 === 0 ? 'Male' : 'Female',
                        'grade_level' => 'Grade 10',
                        'section' => 'A',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | QUIZZES + QUESTIONS + ATTEMPTS + ANSWERS
            |--------------------------------------------------------------------------
            */
            for ($quizNo = 1; $quizNo <= 5; $quizNo++) {

                $quizTitle = "Presentation Quiz {$quizNo}";

                $quiz = DB::table('quizzes')
                    ->where('title', $quizTitle)
                    ->first();

                if (!$quiz) {
                    $quizId = DB::table('quizzes')->insertGetId([
                        'teacher_id' => $teacherId,
                        'title' => $quizTitle,
                        'description' => "Quiz {$quizNo} for presentation",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $quizId = $quiz->id;
                }

                $assigned = DB::table('class_quizzes')
                    ->where('class_id', $classId)
                    ->where('quiz_id', $quizId)
                    ->exists();

                if (!$assigned) {
                    DB::table('class_quizzes')->insert([
                        'class_id' => $classId,
                        'quiz_id' => $quizId,
                        'assigned_at' => now(),
                    ]);
                }

                $questionIds = DB::table('questions')
                    ->where('quiz_id', $quizId)
                    ->pluck('id')
                    ->toArray();

                if (count($questionIds) < 10) {
                    for ($q = count($questionIds) + 1; $q <= 10; $q++) {
                        $questionId = DB::table('questions')->insertGetId([
                            'quiz_id' => $quizId,
                            'question_text' => "Question {$q} for Quiz {$quizNo}",
                            'question_type' => 'multiple_choice',
                            'points' => 1,
                            'order' => $q,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $questionIds[] = $questionId;
                    }
                }

                foreach ($studentIds as $studentId) {
                    $attemptExists = DB::table('quiz_attempts')
                        ->where('quiz_id', $quizId)
                        ->where('student_id', $studentId)
                        ->exists();

                    if ($attemptExists) {
                        continue;
                    }

                    $score = rand(5, 10);

                    $attemptId = DB::table('quiz_attempts')->insertGetId([
                        'quiz_id' => $quizId,
                        'student_id' => $studentId,
                        'score' => $score,
                        'total_points' => 10,
                        'status' => 'completed',
                        'started_at' => Carbon::now()->subMinutes(rand(30, 60)),
                        'completed_at' => Carbon::now()->subMinutes(rand(1, 20)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    foreach ($questionIds as $questionId) {
                        $isCorrect = rand(0, 1);

                        DB::table('student_answers')->insert([
                            'attempt_id' => $attemptId,
                            'question_id' => $questionId,
                            'answer_given' => 'Sample Answer',
                            'is_correct' => $isCorrect,
                            'points_earned' => $isCorrect ? 1 : 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }
}