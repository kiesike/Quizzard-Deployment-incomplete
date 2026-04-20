<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizAttemptFactory extends Factory
{
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'quiz_id' => Quiz::factory(),
            'student_id' => User::factory()->student(),
            'score' => 0,
            'total_points' => 0,
            'status' => 'completed',
            'started_at' => $startedAt,
            'completed_at' => fake()->dateTimeBetween($startedAt, 'now'),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn($a) => ['status' => 'completed']);
    }

    public function inProgress(): static
    {
        return $this->state(fn($a) => [
            'status' => 'in_progress',
            'completed_at' => null,
        ]);
    }
}
