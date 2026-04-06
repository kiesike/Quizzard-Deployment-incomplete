<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_name_is_formatted_from_factory(): void
    {
        $user = User::factory()->make([
            'first_name' => 'John',
            'middle_initial' => 'D',
            'surname' => 'Doe',
        ]);

        $this->assertEquals('John D. Doe', $user->name);
    }

    public function test_name_without_middle_initial(): void
    {
        $user = User::factory()->make([
            'first_name' => 'John',
            'middle_initial' => null,
            'surname' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->name);
    }

    public function test_name_fallback_to_existing_name(): void
    {
        $user = User::factory()->make([
            'name' => 'Legacy User',
            'first_name' => null,
            'surname' => null,
        ]);

        $this->assertEquals('Legacy User', $user->name);
    }

    public function test_middle_initial_is_uppercase(): void
    {
        $user = User::factory()->make([
            'first_name' => 'John',
            'middle_initial' => 'delacruz',
            'surname' => 'Doe',
        ]);

        $this->assertEquals('John D. Doe', $user->name);
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->make([
            'password' => 'Student@1234',
        ]);

        $this->assertNotEquals('Student@1234', $user->password);
        $this->assertTrue(password_verify('Student@1234', $user->password));
    }
}