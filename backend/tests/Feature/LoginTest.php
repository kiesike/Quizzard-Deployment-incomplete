<?php

namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    public function test_student_can_login(): void
    {
        \App\Models\User::create([
            'name' => 'Student Demo',
            'email' => 'student@quizzard.com',
            'password' => bcrypt('Student@1234'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'student@quizzard.com',
            'password' => 'Student@1234',
        ]);
 
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'token'
                ]);
    }


    public function test_login_fails_with_wrong_password(): void
    {
        \App\Models\User::create([
            'name' => 'Student Demo',
            'email' => 'student@quizzard.com',
            'password' => bcrypt('Student@1234'),
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'student@quizzard.com',
            'password' => 'WrongPassword123!',
        ]);

        $response->assertStatus(401);
    }

}