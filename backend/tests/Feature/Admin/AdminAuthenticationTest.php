<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_successfully(): void
    {
        $user = User::factory()->admin()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_superadmin_can_login_successfully(): void
    {
        $user = User::factory()->superadmin()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->admin()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_admin_cannot_login(): void
    {
        $user = User::factory()->admin()->inactive()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_non_admin_user_cannot_login_to_admin_panel(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.submit'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}