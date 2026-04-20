<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'        => User::factory()->state(['role' => 'student', 'status' => 'active']),
            'student_id'     => 'STU-' . strtoupper(fake()->unique()->bothify('#####')),
            'gender'         => fake()->randomElement(['male', 'female', 'other']),
            'date_of_birth'  => fake()->dateTimeBetween('-25 years', '-15 years')->format('Y-m-d'),
            'contact_number' => '09' . fake()->numerify('#########'),
            'grade_level'    => fake()->randomElement(['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10', 'Grade 11', 'Grade 12']),
            'section'        => fake()->randomElement(['Rizal', 'Bonifacio', 'Luna', 'Mabini', 'Aquino']),
        ];
    }
}
