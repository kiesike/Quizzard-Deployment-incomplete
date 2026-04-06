<?php

namespace Tests\Unit;

use App\Support\UserHelper;
use Tests\TestCase;

class UserHelperTest extends TestCase
{
    public function test_normalize_email_converts_to_lowercase_and_trims_spaces(): void
    {
        $this->assertEquals(
            'test@email.com',
            UserHelper::normalizeEmail('  TEST@EMAIL.COM  ')
        );
    }

    public function test_normalize_middle_initial_returns_uppercase_single_letter(): void
    {
        $this->assertEquals('D', UserHelper::normalizeMiddleInitial('d'));
    }

    public function test_normalize_middle_initial_returns_null_for_blank_value(): void
    {
        $this->assertNull(UserHelper::normalizeMiddleInitial('   '));
    }

    public function test_normalize_middle_initial_trims_to_one_character(): void
    {
        $this->assertEquals('A', UserHelper::normalizeMiddleInitial('abc'));
    }
}