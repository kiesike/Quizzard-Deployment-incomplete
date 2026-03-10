<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@quizzard.com',
            'password' => Hash::make('Admin@1234'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        // Create Test Teacher
        User::create([
            'name'     => 'Teacher Demo',
            'email'    => 'teacher@quizzard.com',
            'password' => Hash::make('Teacher@1234'),
            'role'     => 'teacher',
            'status'   => 'active',
        ]);

        // Create Test Student
        User::create([
            'name'     => 'Student Demo',
            'email'    => 'student@quizzard.com',
            'password' => Hash::make('Student@1234'),
            'role'     => 'student',
            'status'   => 'active',
        ]);
    }
}