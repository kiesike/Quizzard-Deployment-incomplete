<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    // ─── HELPERS ─────────────────────────────────────────────

    protected function makeUser(array $overrides = []): User
    {
        return User::factory()->make($overrides);
    }

    // ─── NAME FORMATTING ─────────────────────────────────────

    public function test_name_is_formatted_from_factory(): void
    {
        $user = $this->makeUser([
            'first_name' => 'John',
            'middle_initial' => 'D',
            'surname' => 'Doe',
        ]);

        $this->assertEquals('John D. Doe', $user->name);
    }

    public function test_name_without_middle_initial(): void
    {
        $user = $this->makeUser([
            'first_name' => 'John',
            'middle_initial' => null,
            'surname' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->name);
    }

    public function test_name_fallback_to_existing_name(): void
    {
        $user = $this->makeUser([
            'name' => 'Legacy User',
            'first_name' => null,
            'surname' => null,
        ]);

        $this->assertEquals('Legacy User', $user->name);
    }

    public function test_middle_initial_is_uppercase(): void
    {
        $user = $this->makeUser([
            'first_name' => 'John',
            'middle_initial' => 'delacruz',
            'surname' => 'Doe',
        ]);

        $this->assertEquals('John D. Doe', $user->name);
    }

    // ─── PASSWORD ───────────────────────────────────────────

    public function test_password_is_hashed(): void
    {
        $plainPassword = 'Student@1234';

        $user = $this->makeUser([
            'password' => $plainPassword,
        ]);

        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(password_verify($plainPassword, $user->password));
    }
}