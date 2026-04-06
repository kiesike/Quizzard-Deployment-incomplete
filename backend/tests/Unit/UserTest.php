<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_name_is_formatted_from_first_middle_and_surname(): void
    {
        $user = User::factory()->make([
            'first_name' => 'John',
            'middle_initial' => 'D',
            'surname' => 'Doe',
        ]);

        $this->assertEquals('John D. Doe', $user->name);
    }

    public function test_name_is_formatted_without_middle_initial(): void
    {
        $user = User::factory()->make([
            'first_name' => 'John',
            'middle_initial' => null,
            'surname' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->name);
    }

    public function test_name_falls_back_to_stored_name_when_split_fields_are_missing(): void
    {
        $user = User::factory()->make([
            'name' => 'Legacy User',
            'first_name' => null,
            'middle_initial' => null,
            'surname' => null,
        ]);

        $this->assertEquals('Legacy User', $user->name);
    }

    public function test_middle_initial_is_uppercased_and_shortened_to_one_letter_in_name(): void
    {
        $user = User::factory()->make([
            'first_name' => 'John',
            'middle_initial' => 'delacruz',
            'surname' => 'Doe',
        ]);

        $this->assertEquals('John D. Doe', $user->name);
    }

    public function test_password_is_hashed_by_cast(): void
    {
        $user = User::factory()->make([
            'password' => 'Student@1234',
        ]);

        $this->assertNotEquals('Student@1234', $user->password);
        $this->assertTrue(password_verify('Student@1234', $user->password));
    }

    public function test_name_trims_spaces_properly(): void
    {
        $user = User::factory()->make([
            'first_name' => '  John  ',
            'middle_initial' => ' d ',
            'surname' => '  Doe ',
        ]);

        $this->assertEquals('John D. Doe', $user->name);
    }

    public function test_name_handles_missing_surname(): void
    {
        $user = User::factory()->make([
            'first_name' => 'John',
            'middle_initial' => 'D',
            'surname' => null,
        ]);

        $this->assertEquals('John D.', trim($user->name));
    }

    public function test_name_handles_missing_first_name(): void
    {
        $user = User::factory()->make([
            'first_name' => null,
            'middle_initial' => 'D',
            'surname' => 'Doe',
        ]);

        $this->assertEquals('D. Doe', trim($user->name));
    }

    public function test_password_is_always_hashed_even_when_reassigned(): void
    {
        $user = User::factory()->make();

        $user->password = 'NewPassword123';

        $this->assertTrue(password_verify('NewPassword123', $user->password));
    }

    
















}