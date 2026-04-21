<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ─── HELPERS ─────────────────────────────────────────────

    protected function createUser(string $role = 'student', array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role'   => $role,
            'status' => 'active',
        ], $overrides));
    }

    protected function login(array $data)
    {
        return $this->postJson('/api/login', $data);
    }

    protected function register(array $data)
    {
        return $this->postJson('/api/register', $data);
    }

    protected function logout(string $token)
    {
        return $this->withHeader('Authorization', "Bearer {$token}")
                    ->postJson('/api/logout');
    }

    protected function updateProfile(User $user, array $data)
    {
        return $this->actingAs($user)->putJson('/api/profile', $data);
    }

    protected function validRegistration(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'John',
            'middle_initial' => 'D',
            'surname' => 'Doe',
            'email' => 'user'.uniqid().'@quizzard.com',
            'password' => 'Student@1234',
            'password_confirmation' => 'Student@1234',
            'role' => 'student',
        ], $overrides);
    }

    // ─── LOGIN ─────────────────────────────────────────────

    public function test_valid_login_as_student(): void
    {
        $user = $this->createUser('student');

        $this->login([
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()
          ->assertJsonPath('user.role', 'student');
    }

    public function test_valid_login_as_teacher(): void
    {
        $user = $this->createUser('teacher');

        $this->login([
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()
          ->assertJsonPath('user.role', 'teacher');
    }

    public function test_login_fails_with_empty_fields(): void
    {
        $this->login([
            'email' => '',
            'password' => '',
        ])->assertUnprocessable();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = $this->createUser();

        $this->login([
            'email' => $user->email,
            'password' => 'WrongPassword@123',
        ])->assertUnauthorized()
          ->assertJsonFragment([
              'message' => 'Invalid credentials. 4 attempt(s) remaining before lockout.'
          ]);
    }

    public function test_login_fails_with_deactivated_account(): void
    {
        $user = $this->createUser('student', ['status' => 'deactivated']);

        $this->login([
            'email' => $user->email,
            'password' => 'password',
        ])->assertForbidden();
    }

    public function test_login_fails_with_pending_account(): void
    {
        $user = $this->createUser('student', ['status' => 'pending']);

        $this->login([
            'email' => $user->email,
            'password' => 'password',
        ])->assertForbidden();
    }

    public function test_account_locks_after_five_failed_attempts(): void
    {
        $user = $this->createUser();

        for ($i = 0; $i < 5; $i++) {
            $this->login([
                'email' => $user->email,
                'password' => 'WrongPassword@123',
            ]);
        }

        $this->login([
            'email' => $user->email,
            'password' => 'WrongPassword@123',
        ])->assertStatus(423);
    }

    public function test_locked_account_cannot_login(): void
    {
       $user = $this->createUser('student', [
    'failed_login_attempts' => 5,
    'locked_until' => Carbon::now()->addMinutes(15),
]);
        $this->login([
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(423);
    }

    // ─── LOGOUT ────────────────────────────────────────────

    public function test_logged_in_user_can_logout(): void
    {
        $user = $this->createUser();

        $token = $this->login([
            'email' => $user->email,
            'password' => 'password',
        ])->json('token');

        $this->logout($token)
             ->assertOk()
             ->assertJsonPath('message', 'Logged out successfully.');
    }

    // ─── REGISTER ─────────────────────────────────────────

    public function test_student_can_register_successfully(): void
    {
        $this->register($this->validRegistration())
             ->assertCreated()
             ->assertJsonPath('user.status', 'pending');
    }

    public function test_teacher_can_register_successfully(): void
    {
        $this->register($this->validRegistration([
            'role' => 'teacher'
        ]))->assertCreated()
          ->assertJsonPath('user.role', 'teacher');
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        $existing = $this->createUser();

        $this->register($this->validRegistration([
            'email' => $existing->email
        ]))->assertUnprocessable();
    }

    public function test_register_fails_with_emoji_in_first_name(): void
    {
        $this->register($this->validRegistration([
            'first_name' => '😊John'
        ]))->assertUnprocessable();
    }

    public function test_register_fails_with_emoji_in_surname(): void
    {
        $this->register($this->validRegistration([
            'surname' => 'Doe🎉'
        ]))->assertUnprocessable();
    }

    public function test_register_fails_with_no_special_character_in_password(): void
    {
        $this->register($this->validRegistration([
            'password' => 'Student1234',
            'password_confirmation' => 'Student1234',
        ]))->assertUnprocessable();
    }

    public function test_register_fails_with_no_uppercase_in_password(): void
    {
        $this->register($this->validRegistration([
            'password' => 'student@1234',
            'password_confirmation' => 'student@1234',
        ]))->assertUnprocessable();
    }

    public function test_register_fails_with_no_number_in_password(): void
    {
        $this->register($this->validRegistration([
            'password' => 'Student@abcd',
            'password_confirmation' => 'Student@abcd',
        ]))->assertUnprocessable();
    }

    public function test_register_fails_with_mismatched_passwords(): void
    {
        $this->register($this->validRegistration([
            'password_confirmation' => 'Different@1234',
        ]))->assertUnprocessable();
    }

    public function test_register_fails_with_empty_fields(): void
    {
        $this->register([])->assertUnprocessable();
    }

    // ─── PROFILE ──────────────────────────────────────────

    public function test_user_can_update_their_name(): void
    {
        $user = $this->createUser();

        $this->updateProfile($user, [
            'first_name' => 'Updated',
            'surname' => 'Name',
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
            'surname' => 'Name',
        ]);
    }

    public function test_user_can_change_password_with_correct_current_password(): void
    {
        $user = $this->createUser();

        $this->updateProfile($user, [
            'current_password' => 'password',
            'new_password' => 'NewPass@1234',
        ])->assertOk();
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = $this->createUser();

        $this->updateProfile($user, [
            'current_password' => 'WrongPass',
            'new_password' => 'NewPass@1234',
        ])->assertUnprocessable()
          ->assertJsonPath('message', 'Current password is incorrect.');
    }

    public function test_change_password_fails_when_new_password_same_as_current(): void
    {
        $user = $this->createUser('student', [
    'password' => \Illuminate\Support\Facades\Hash::make('Killerkidz098!'),
]);

        $this->updateProfile($user, [
            'current_password' => 'Killerkidz098!',
            'new_password' => 'Killerkidz098!',
        ])->assertUnprocessable()
          ->assertJsonPath('message', 'New password must be different from your current password.');
    }

    public function test_student_cannot_access_teacher_only_route(): void
{
    $student = $this->createUser('student');

    $this->actingAs($student)
        ->get('/teacher/dashboard')
        ->assertForbidden(); // or redirect depending on your middleware
}
 
public function test_user_cannot_register_as_admin(): void
{
    $this->register($this->validRegistration([
        'role' => 'admin'
    ]))->assertUnprocessable();
}

public function test_teacher_cannot_login_when_deactivated(): void
{
    $teacher = $this->createUser('teacher', ['status' => 'deactivated']);

    $this->login([
        'email' => $teacher->email,
        'password' => 'password',
    ])->assertForbidden();
}

public function test_student_cannot_login_when_pending(): void
{
    $student = $this->createUser('student', ['status' => 'pending']);

    $this->login([
        'email' => $student->email,
        'password' => 'password',
    ])->assertForbidden();
}

public function test_user_cannot_change_role_via_profile(): void
{
    $user = $this->createUser('student');

    $this->updateProfile($user, [
        'role' => 'admin'
    ])->assertOk();

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
        'role' => 'admin'
    ]);
}

public function test_password_with_all_required_characters_passes(): void
{
    $this->register($this->validRegistration([
        'password' => 'Aa1@aaaa',
        'password_confirmation' => 'Aa1@aaaa',
    ]))->assertCreated();
}

public function test_authenticated_user_can_access_profile(): void
{
    $user = $this->createUser();

    $this->actingAs($user)
        ->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('id', $user->id);
}

public function test_guest_cannot_access_profile(): void
{
    $this->getJson('/api/me')
        ->assertUnauthorized();
}

public function test_failed_attempts_reset_after_successful_login(): void
{
    $user = $this->createUser();

    // fail twice
    for ($i = 0; $i < 2; $i++) {
        $this->login([
            'email' => $user->email,
            'password' => 'WrongPass@123',
        ]);
    }

    // success
    $this->login([
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'failed_login_attempts' => 0,
    ]);
}

public function test_user_can_login_after_lockout_expires(): void
{
    $user = $this->createUser('student', [
        'locked_until' => now()->subMinutes(1),
        'failed_login_attempts' => 5,
    ]);

    $this->login([
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk();
}

public function test_email_exceeds_max_length_fails(): void
{
    $this->register($this->validRegistration([
        'email' => str_repeat('a', 31) . '@a.com'
    ]))->assertUnprocessable();
}
}