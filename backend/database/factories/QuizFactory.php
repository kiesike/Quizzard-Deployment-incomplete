<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory()->teacher(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'is_published' => true,
            'cover_image' => null,
        ];
    }

    public function forTeacher(int $teacherId): static
    {
        return $this->state(fn($a) => ['teacher_id' => $teacherId]);
    }

    public function published(): static
    {
        return $this->state(fn($a) => ['is_published' => true]);
    }

    public function unpublished(): static
    {
        return $this->state(fn($a) => ['is_published' => false]);
    }
}
