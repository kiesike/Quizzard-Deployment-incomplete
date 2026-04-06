<?php

namespace Tests\Unit;

use App\Support\QuizLogic;
use Carbon\Carbon;
use Tests\TestCase;

class QuizLogicTest extends TestCase
{
    public function test_calculate_percentage_returns_correct_value(): void
    {
        $this->assertEquals(80.0, QuizLogic::calculatePercentage(8, 10));
    }

    public function test_calculate_percentage_returns_zero_when_total_points_is_zero(): void
    {
        $this->assertEquals(0.0, QuizLogic::calculatePercentage(5, 0));
    }

    public function test_calculate_percentage_rounds_correctly(): void
    {
        $this->assertEquals(33.33, QuizLogic::calculatePercentage(1, 3));
    }

    public function test_is_locked_returns_true_when_locked_until_is_in_future(): void
    {
        $lockedUntil = Carbon::now()->addMinutes(10);

        $this->assertTrue(QuizLogic::isLocked($lockedUntil));
    }

    public function test_is_locked_returns_false_when_locked_until_is_in_past(): void
    {
        $lockedUntil = Carbon::now()->subMinutes(5);

        $this->assertFalse(QuizLogic::isLocked($lockedUntil));
    }

    public function test_is_locked_returns_false_when_locked_until_is_null(): void
    {
        $this->assertFalse(QuizLogic::isLocked(null));
    }

    public function test_is_exact_match_returns_true_for_matching_answers(): void
    {
        $this->assertTrue(QuizLogic::isExactMatch('Paris', 'Paris'));
    }

    public function test_is_exact_match_returns_true_for_trimmed_answers(): void
    {
        $this->assertTrue(QuizLogic::isExactMatch('  Paris  ', 'Paris'));
    }

    public function test_is_exact_match_returns_false_for_different_answers(): void
    {
        $this->assertFalse(QuizLogic::isExactMatch('London', 'Paris'));
    }

    public function test_is_exact_match_is_case_sensitive(): void
    {
        $this->assertFalse(QuizLogic::isExactMatch('paris', 'Paris'));
    }
}