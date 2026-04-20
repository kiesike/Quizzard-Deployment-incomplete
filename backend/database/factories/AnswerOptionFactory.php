<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnswerOptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'option_text' => fake()->sentence(),
            'is_correct' => false,
            'match_pair' => null,
            'order' => 1,
            'image_path' => null,
            'video_path' => null,
            'audio_path' => null,
        ];
    }

    public function correct(): static
    {
        return $this->state(fn($a) => ['is_correct' => true]);
    }

    public function matchPair(string $left, string $right): static
    {
        return $this->state(fn($a) => [
            'option_text' => $left,
            'match_pair' => $right,
            'is_correct' => true,
        ]);
    }
}
