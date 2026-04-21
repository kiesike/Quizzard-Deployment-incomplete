<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\ClassRoom;
use App\Models\StudentAnswer;
use App\Models\StudentProfile;

class QuizReportsTestSeeder extends Seeder
{
    public function run(): void
    {
        // ===== CREATE TEACHER =====
        $teacher = DB::table('users')
            ->where('email', 'reportsteacher@quizzard.com')
            ->first();

        if (!$teacher) {
            $teacherId = DB::table('users')->insertGetId([
                'name' => 'Reports Teacher',
                'email' => 'reportsteacher@quizzard.com',
                'password' => Hash::make('Teacher@1234'),
                'role' => 'teacher',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $teacherId = $teacher->id;
        }

        // ===== CREATE TEST CLASS =====
        $existingClass = DB::table('classes')
    ->where('class_code', 'TEST123')
    ->first();

if (!$existingClass) {
    $classId = DB::table('classes')->insertGetId([
        'teacher_id' => $teacherId,
        'name' => 'Test Class',
        'description' => 'Generated for reports testing',
        'class_code' => 'TEST123',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
} else {
    $classId = $existingClass->id;
}

        // ===== CREATE QUIZ =====
         $quizId = DB::table('quizzes')->insertGetId([
            'teacher_id' => $teacherId,
            'title' => 'Reports Module Demo Quiz',
            'description' => 'Seeder quiz for testing leaderboard',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('class_quizzes')->insert([
            'class_id' => $classId,
            'quiz_id' => $quizId,
            'assigned_at' => now(),
        ]);

        // ===== CREATE STUDENTS + ATTEMPTS =====
        for ($i = 1; $i <= 20; $i++) {

            $existingStudent = DB::table('users')
                ->where('email', "student$i@test.com")
                ->first();

            if (!$existingStudent) {
                $studentId = DB::table('users')->insertGetId([
                    'name' => "Student $i",
                    'email' => "student$i@test.com",
                    'password' => Hash::make('Student@1234'),
                    'role' => 'student',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $studentId = $existingStudent->id;
            }

            $score = rand(5, 20);      // random score
            $total = 20;               // total items

            DB::table('class_students')->insert([
    'class_id' => $classId,
    'student_id' => $studentId,
    'joined_at' => now(),
]);

DB::table('student_profiles')->insert([
    'user_id' => $studentId,
    'student_id' => '2026-' . str_pad($i, 4, '0', STR_PAD_LEFT),
    'gender' => $i % 2 === 0 ? 'Male' : 'Female',
    'grade_level' => 'Grade 10',
    'section' => 'A',
    'created_at' => now(),
    'updated_at' => now(),
]);

$attemptId = DB::table('quiz_attempts')->insertGetId([
    'quiz_id' => $quizId,
    'student_id' => $studentId,
    'score' => $score,
    'total_points' => $total,
    'status' => 'completed',
    'started_at' => Carbon::now()->subMinutes(rand(60, 300)),
    'completed_at' => Carbon::now()->subMinutes(rand(1, 59)),
    'created_at' => now(),
    'updated_at' => now(),
]);

$questionIds = DB::table('questions')
    ->where('quiz_id', $quizId)
    ->pluck('id');

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
}