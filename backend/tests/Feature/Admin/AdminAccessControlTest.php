<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_superadmin_can_access_dashboard(): void
    {
        $user = User::factory()->superadmin()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    public function test_guest_is_redirected_to_admin_login_when_accessing_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect('/login');
    }

    public function test_student_cannot_access_dashboard(): void
{
    /** @var \App\Models\User $user */
    $user = User::factory()->create([
        'role' => 'student',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertForbidden();
}
}