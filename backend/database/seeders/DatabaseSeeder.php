<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(AdminUserSeeder::class);
        $this->call(AdminClassesTestSeeder::class);
        //$this->call(QuizReportsTestSeeder::class);
        // $this->call(AdvancedQuizzardTestSeeder::class);
        $this->call(QuizzardDemoSeeder::class);

        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

    }
}
