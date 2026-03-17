<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ClassRoom;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminClassesTestSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [];

        for ($i = 1; $i <= 5; $i++) {
            $teacher = User::updateOrCreate(
                ['email' => "teacher{$i}@test.com"],
                [
                    'name' => "Teacher $i",
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                    'status' => $i <= 4 ? 'active' : 'deactivated',
                ]
            );

            $teachers[] = $teacher;
        }

        $students = [];

        for ($i = 1; $i <= 30; $i++) {
            $student = User::updateOrCreate(
                ['email' => "student{$i}@test.com"],
                [
                    'name' => "Student $i",
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'status' => 'active',
                ]
            );

            $students[] = $student;
        }

        foreach ($teachers as $teacher) {
            for ($c = 1; $c <= 3; $c++) {
                $className = "Class {$teacher->id} - $c";

                $class = ClassRoom::firstOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'name' => $className,
                    ],
                    [
                        'description' => "This is a demo description for class {$teacher->id}-$c",
                        'class_code' => strtoupper(Str::random(6)),
                    ]
                );

                $randomStudents = collect($students)->random(rand(5, 15));

                foreach ($randomStudents as $student) {
                    DB::table('class_students')->updateOrInsert(
                        [
                            'class_id' => $class->id,
                            'student_id' => $student->id,
                        ],
                        []
                    );
                }
            }
        }
    }
}