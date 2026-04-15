<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class FullPresentationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // ===== TEACHER =====
            $teacherId = DB::table('users')->insertGetId([
                'name' => 'Presentation Teacher',
                'email' => 'presentation_teacher_' . time() . '@quizzard.com',
                'password' => Hash::make('Teacher@1234'),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ===== CLASS =====
            $classId = DB::table('classes')->insertGetId([
                'teacher_id' => $teacherId,
                'name' => 'Presentation Class',
                'description' => 'Auto generated presentation data',
                'class_code' => 'PR01',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ===== STUDENTS =====
            $studentIds = [];

            for ($s = 1; $s <= 10; $s++) {
                $studentId = DB::table('users')->insertGetId([
                    'name' => "Student $s",
                    'email' => 'presentation_student_' . $s . '_' . time() . '@test.com',
                    'password' => Hash::make('Student@1234'),
                    'role' => 'student',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $studentIds[] = $studentId;

                DB::table('class_students')->insert([
                    'class_id' => $classId,
                    'student_id' => $studentId,
                    'joined_at' => now(),
                ]);
            }

            // ===== QUIZZES =====
            for ($q = 1; $q <= 10; $q++) {

                $quizId = DB::table('quizzes')->insertGetId([
                    'teacher_id' => $teacherId,
                    'title' => "Quiz $q",
                    'description' => "Presentation Quiz $q",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('class_quizzes')->insert([
                    'class_id' => $classId,
                    'quiz_id' => $quizId,
                ]);

                $questionIds = [];

                // ===== QUESTIONS =====
                for ($i = 1; $i <= 10; $i++) {
                    $questionId = DB::table('questions')->insertGetId([
                        'quiz_id' => $quizId,
                        'question_text' => "Question $i for Quiz $q",
                        'question_type' => 'multiple_choice',
                        'points' => 1,
                        'order' => $i,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $questionIds[] = $questionId;
                }

                // ===== ATTEMPTS + ANSWERS =====
                foreach ($studentIds as $studentId) {

                    $score = rand(5, 10);

                    $attemptId = DB::table('quiz_attempts')->insertGetId([
                        'quiz_id' => $quizId,
                        'student_id' => $studentId,
                        'score' => $score,
                        'total_points' => 10,
                        'status' => 'completed',
                        'started_at' => Carbon::now()->subMinutes(rand(30, 60)),
                        'completed_at' => Carbon::now()->subMinutes(rand(1, 29)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    foreach ($questionIds as $questionId) {
                        $correct = rand(0, 1);

                        DB::table('student_answers')->insert([
                            'attempt_id' => $attemptId,
                            'question_id' => $questionId,
                            'answer_given' => 'Sample Answer',
                            'is_correct' => $correct,
                            'points_earned' => $correct ? 1 : 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }
}
