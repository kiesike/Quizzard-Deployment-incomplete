<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id'   => User::factory()->state(['role' => 'teacher', 'status' => 'active']),
            'title'        => fake()->sentence(4),
            'description'  => fake()->paragraph(),
            'is_published' => false,
            'cover_image'  => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }
}