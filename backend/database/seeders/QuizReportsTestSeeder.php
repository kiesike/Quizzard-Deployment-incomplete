<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class QuizReportsTestSeeder extends Seeder
{
    public function run(): void
    {
        // ===== CREATE TEACHER =====
        $teacherId = DB::table('users')->insertGetId([
            'name' => 'Reports Teacher',
            'email' => 'reportsteacher@quizzard.com',
            'password' => Hash::make('Teacher@1234'),
            'role' => 'teacher',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ===== CREATE QUIZ =====
        $quizId = DB::table('quizzes')->insertGetId([
            'teacher_id' => $teacherId,
            'title' => 'Reports Module Demo Quiz',
            'description' => 'Seeder quiz for testing leaderboard',
            'time_limit' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ===== CREATE STUDENTS + ATTEMPTS =====
        for ($i = 1; $i <= 20; $i++) {

            $studentId = DB::table('users')->insertGetId([
                'name' => "Student $i",
                'email' => "student$i@test.com",
                'password' => Hash::make('Student@1234'),
                'role' => 'student',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $score = rand(5, 20);      // random score
            $total = 20;               // total items

            DB::table('quiz_attempts')->insert([
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
        }
    }
}