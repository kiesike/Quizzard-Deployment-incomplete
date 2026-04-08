<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(array $overrides = [])
    {
        return User::factory()->create(array_merge([
            'role'   => 'student',
            'status' => 'active',
        ], $overrides));
    }

    private function makeTeacher(array $overrides = [])
    {
        return User::factory()->create(array_merge([
            'role'   => 'teacher',
            'status' => 'active',
        ], $overrides));
    }

    // ─── LOGIN ───────────────────────────────────────────────────────────────

    public function test_valid_login_as_student(): void
    {
        $student = $this->makeStudent();

        $response = $this->postJson('/api/login', [
            'email'    => $student->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Login successful.')
                 ->assertJsonPath('user.role', 'student')
                 ->assertJsonStructure(['token']);
    }

    public function test_valid_login_as_teacher(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->postJson('/api/login', [
            'email'    => $teacher->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Login successful.')
                 ->assertJsonPath('user.role', 'teacher')
                 ->assertJsonStructure(['token']);
    }

    public function test_login_fails_with_empty_fields(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => '',
            'password' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $student = $this->makeStudent();

        $response = $this->postJson('/api/login', [
            'email'    => $student->email,
            'password' => 'WrongPassword@123',
        ]);

        $response->assertStatus(401)
                 ->assertJsonFragment(['message' => 'Invalid credentials. 4 attempt(s) remaining before lockout.']);
    }

    public function test_login_fails_with_deactivated_account(): void
    {
        $user = $this->makeStudent(['status' => 'deactivated']);

        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'Your account has been deactivated. Please contact the administrator.');
    }

    public function test_login_fails_with_pending_account(): void
    {
        $user = $this->makeStudent(['status' => 'pending']);

        $response = $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'Your account is pending approval. Please wait for an administrator to activate your account.');
    }

    public function test_account_locks_after_five_failed_attempts(): void
    {
        $student = $this->makeStudent();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email'    => $student->email,
                'password' => 'WrongPassword@123',
            ]);
        }

        $response = $this->postJson('/api/login', [
            'email'    => $student->email,
            'password' => 'WrongPassword@123',
        ]);

        $response->assertStatus(423);
    }

    public function test_locked_account_cannot_login(): void
    {
        $student = $this->makeStudent([
            'failed_login_attempts' => 5,
            'locked_until'          => Carbon::now()->addMinutes(15),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => $student->email,
            'password' => 'password',
        ]);

        $response->assertStatus(423);
    }

    // ─── LOGOUT ──────────────────────────────────────────────────────────────

    public function test_logged_in_user_can_logout(): void
    {
        $student = $this->makeStudent();

        $loginResponse = $this->postJson('/api/login', [
            'email'    => $student->email,
            'password' => 'password',
        ]);

        $token = $loginResponse->json('token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                        ->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJsonPath('message', 'Logged out successfully.');
    }

    // ─── REGISTER ────────────────────────────────────────────────────────────

    public function test_student_can_register_successfully(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name'            => 'John',
            'middle_initial'        => 'D',
            'surname'               => 'Doe',
            'email'                 => 'john@quizzard.com',
            'password'              => 'Student@1234',
            'password_confirmation' => 'Student@1234',
            'role'                  => 'student',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('user.role', 'student')
                 ->assertJsonPath('user.status', 'pending');

        $this->assertDatabaseHas('users', ['email' => 'john@quizzard.com']);
    }

    public function test_teacher_can_register_successfully(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name'            => 'Jane',
            'middle_initial'        => 'A',
            'surname'               => 'Smith',
            'email'                 => 'jane@quizzard.com',
            'password'              => 'Teacher@1234',
            'password_confirmation' => 'Teacher@1234',
            'role'                  => 'teacher',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('user.role', 'teacher')
                 ->assertJsonPath('user.status', 'pending');

        $this->assertDatabaseHas('users', ['email' => 'jane@quizzard.com']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        $existing = $this->makeStudent();

        $response = $this->postJson('/api/register', [
            'first_name'            => 'Another',
            'surname'               => 'User',
            'email'                 => $existing->email,
            'password'              => 'Student@1234',
            'password_confirmation' => 'Student@1234',
            'role'                  => 'student',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_emoji_in_first_name(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name'            => '😊John',
            'surname'               => 'Doe',
            'email'                 => 'emoji@quizzard.com',
            'password'              => 'Student@1234',
            'password_confirmation' => 'Student@1234',
            'role'                  => 'student',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_emoji_in_surname(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name'            => 'John',
            'surname'               => 'Doe🎉',
            'email'                 => 'emojisurname@quizzard.com',
            'password'              => 'Student@1234',
            'password_confirmation' => 'Student@1234',
            'role'                  => 'student',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_no_special_character_in_password(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name'            => 'John',
            'surname'               => 'Doe',
            'email'                 => 'nospecial@quizzard.com',
            'password'              => 'Student1234',
            'password_confirmation' => 'Student1234',
            'role'                  => 'student',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_no_uppercase_in_password(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name'            => 'John',
            'surname'               => 'Doe',
            'email'                 => 'noupper@quizzard.com',
            'password'              => 'student@1234',
            'password_confirmation' => 'student@1234',
            'role'                  => 'student',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_no_number_in_password(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name'            => 'John',
            'surname'               => 'Doe',
            'email'                 => 'nonumber@quizzard.com',
            'password'              => 'Student@abcd',
            'password_confirmation' => 'Student@abcd',
            'role'                  => 'student',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_mismatched_passwords(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name'            => 'John',
            'surname'               => 'Doe',
            'email'                 => 'mismatch@quizzard.com',
            'password'              => 'Student@1234',
            'password_confirmation' => 'Student@12345',
            'role'                  => 'student',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_empty_fields(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422);
    }

    // ─── PROFILE UPDATE ──────────────────────────────────────────────────────

    public function test_user_can_update_their_name(): void
    {
        $student = $this->makeStudent();

        $response = $this->actingAs($student)->putJson('/api/profile', [
            'first_name' => 'Updated',
            'surname'    => 'Name',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'id'         => $student->id,
            'first_name' => 'Updated',
            'surname'    => 'Name',
        ]);
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $student = $this->makeStudent();

        $response = $this->actingAs($student)->putJson('/api/profile', [
            'current_password' => 'password',
            'new_password'     => 'NewPass@1234',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Profile updated successfully.');
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $student = $this->makeStudent();

        $response = $this->actingAs($student)->putJson('/api/profile', [
            'current_password' => 'WrongPass@123',
            'new_password'     => 'NewPass@1234',
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('message', 'Current password is incorrect.');
    }

    public function test_change_password_fails_when_new_password_same_as_current(): void
    {
        $student = $this->makeStudent(['password' => 'Student@1234']);

        $response = $this->actingAs($student)->putJson('/api/profile', [
            'current_password' => 'Student@1234',
            'new_password'     => 'Student@1234',
        ]);

        $response->assertStatus(422)
                ->assertJsonPath('message', 'New password must be different from your current password.');
    }
}