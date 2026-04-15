<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\ClassRoom;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuizExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_results_excel()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $teacher = User::factory()->create([
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $class = ClassRoom::factory()->create([
            'teacher_id' => $teacher->id,
        ]);

        $quiz = Quiz::factory()->create([
            'teacher_id' => $teacher->id,
        ]);

        $class->quizzes()->attach($quiz->id);

        $response = $this->actingAs($admin)->get(
            route('admin.classes.quizzes.export.results', [
                'classId' => $class->id,
                'quizId' => $quiz->id,
            ])
        );

        $response->assertStatus(200);
        $response->assertHeader(
            'content-disposition',
            fn ($value) => str_contains($value, '.xlsx')
        );
    }

    public function test_admin_can_export_analytics_excel()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $teacher = User::factory()->create([
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $class = ClassRoom::factory()->create([
            'teacher_id' => $teacher->id,
        ]);

        $quiz = Quiz::factory()->create([
            'teacher_id' => $teacher->id,
        ]);

        $class->quizzes()->attach($quiz->id);

        $response = $this->actingAs($admin)->get(
            route('admin.classes.quizzes.export.analytics', [
                'classId' => $class->id,
                'quizId' => $quiz->id,
            ])
        );

        $response->assertStatus(200);
        $response->assertHeader(
            'content-disposition',
            fn ($value) => str_contains($value, '.xlsx')
        );
    }
}
