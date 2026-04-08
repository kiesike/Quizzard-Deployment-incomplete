<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassRoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id'  => User::factory()->state(['role' => 'teacher', 'status' => 'active']),
            'name'        => fake()->words(3, true),
            'description' => fake()->sentence(),
            'class_code'  => strtoupper(fake()->unique()->bothify('??####')),
        ];
    }
}