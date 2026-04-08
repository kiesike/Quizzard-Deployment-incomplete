<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Quiz;
use App\Models\ClassRoom;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeacher()
    {
        return User::factory()->create(['role' => 'teacher', 'status' => 'active']);
    }

    private function makeStudent()
    {
        return User::factory()->create(['role' => 'student', 'status' => 'active']);
    }

    private function makeAttempt($quizId, $studentId)
    {
        return QuizAttempt::create([
            'quiz_id'      => $quizId,
            'student_id'   => $studentId,
            'score'        => 0,
            'total_points' => 10,
            'status'       => 'completed',
            'started_at'   => now(),
        ]);
    }

    // ─── CREATE ──────────────────────────────────────────────────────────────

    public function test_teacher_can_create_a_quiz(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->postJson('/api/quizzes', [
            'title'       => 'My New Quiz',
            'description' => 'A quiz about science.',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Quiz created successfully.');

        $this->assertDatabaseHas('quizzes', [
            'title'      => 'My New Quiz',
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_quiz_creation_fails_without_title(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->postJson('/api/quizzes', [
            'description' => 'No title provided.',
        ]);

        $response->assertStatus(422);
    }

    // ─── READ ────────────────────────────────────────────────────────────────

    public function test_teacher_can_get_all_their_quizzes(): void
    {
        $teacher = $this->makeTeacher();

        Quiz::factory()->count(3)->create(['teacher_id' => $teacher->id]);

        $response = $this->actingAs($teacher)->getJson('/api/quizzes');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_teacher_only_sees_their_own_quizzes(): void
    {
        $teacher = $this->makeTeacher();
        $other   = $this->makeTeacher();

        Quiz::factory()->count(2)->create(['teacher_id' => $teacher->id]);
        Quiz::factory()->count(3)->create(['teacher_id' => $other->id]);

        $response = $this->actingAs($teacher)->getJson('/api/quizzes');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');
    }

    public function test_teacher_can_get_a_single_quiz(): void
    {
        $teacher = $this->makeTeacher();
        $quiz    = Quiz::factory()->create(['teacher_id' => $teacher->id]);

        $response = $this->actingAs($teacher)->getJson("/api/quizzes/{$quiz->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $quiz->id);
    }

    // ─── UPDATE ──────────────────────────────────────────────────────────────

    public function test_teacher_can_update_a_quiz(): void
    {
        $teacher = $this->makeTeacher();
        $quiz    = Quiz::factory()->create(['teacher_id' => $teacher->id]);

        $response = $this->actingAs($teacher)->putJson("/api/quizzes/{$quiz->id}", [
            'title'       => 'Updated Title',
            'description' => 'Updated description.',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Quiz updated successfully.');

        $this->assertDatabaseHas('quizzes', [
            'id'    => $quiz->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_non_owner_teacher_cannot_update_a_quiz(): void
    {
        $owner = $this->makeTeacher();
        $other = $this->makeTeacher();
        $quiz  = Quiz::factory()->create(['teacher_id' => $owner->id]);

        $response = $this->actingAs($other)->putJson("/api/quizzes/{$quiz->id}", [
            'title'       => 'Hacked Title',
            'description' => 'Hacked.',
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_update_quiz_if_it_has_attempts(): void
    {
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent();
        $quiz    = Quiz::factory()->create(['teacher_id' => $teacher->id]);

        $this->makeAttempt($quiz->id, $student->id);

        $response = $this->actingAs($teacher)->putJson("/api/quizzes/{$quiz->id}", [
            'title'       => 'Updated Title',
            'description' => 'Updated.',
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'This quiz cannot be edited because students have already taken it.');
    }

    // ─── DELETE ──────────────────────────────────────────────────────────────

    public function test_teacher_can_delete_a_quiz(): void
    {
        $teacher = $this->makeTeacher();
        $quiz    = Quiz::factory()->create(['teacher_id' => $teacher->id]);

        $response = $this->actingAs($teacher)->deleteJson("/api/quizzes/{$quiz->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Quiz deleted successfully.');

        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
    }

    public function test_non_owner_teacher_cannot_delete_a_quiz(): void
    {
        $owner = $this->makeTeacher();
        $other = $this->makeTeacher();
        $quiz  = Quiz::factory()->create(['teacher_id' => $owner->id]);

        $response = $this->actingAs($other)->deleteJson("/api/quizzes/{$quiz->id}");

        $response->assertStatus(403);
    }

    public function test_cannot_delete_quiz_if_it_has_attempts(): void
    {
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent();
        $quiz    = Quiz::factory()->create(['teacher_id' => $teacher->id]);

        $this->makeAttempt($quiz->id, $student->id);

        $response = $this->actingAs($teacher)->deleteJson("/api/quizzes/{$quiz->id}");

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'This quiz cannot be deleted because students have already taken it.');
    }

    // ─── PUBLISH / UNPUBLISH ─────────────────────────────────────────────────

    public function test_teacher_can_publish_a_quiz(): void
    {
        $teacher = $this->makeTeacher();
        $quiz    = Quiz::factory()->create(['teacher_id' => $teacher->id, 'is_published' => false]);

        $response = $this->actingAs($teacher)->patchJson("/api/quizzes/{$quiz->id}/publish-toggle");

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Quiz published.');

        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'is_published' => true]);
    }

    public function test_teacher_can_unpublish_a_quiz(): void
    {
        $teacher = $this->makeTeacher();
        $quiz    = Quiz::factory()->create(['teacher_id' => $teacher->id, 'is_published' => true]);

        $response = $this->actingAs($teacher)->patchJson("/api/quizzes/{$quiz->id}/publish-toggle");

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Quiz unpublished.');

        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'is_published' => false]);
    }

    public function test_non_owner_teacher_cannot_publish_or_unpublish_a_quiz(): void
    {
        $owner = $this->makeTeacher();
        $other = $this->makeTeacher();
        $quiz  = Quiz::factory()->create(['teacher_id' => $owner->id, 'is_published' => false]);

        $response = $this->actingAs($other)->patchJson("/api/quizzes/{$quiz->id}/publish-toggle");

        $response->assertStatus(403);
    }

    // ─── ASSIGN TO CLASS ─────────────────────────────────────────────────────

    public function test_teacher_can_assign_a_quiz_to_a_class(): void
    {
        $teacher = $this->makeTeacher();
        $quiz    = Quiz::factory()->create(['teacher_id' => $teacher->id]);
        $class   = ClassRoom::factory()->create(['teacher_id' => $teacher->id]);

        $response = $this->actingAs($teacher)->postJson("/api/classes/{$class->id}/assign-quiz", [
            'quiz_id' => $quiz->id,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Quiz assigned to class successfully.');
    }

    public function test_cannot_assign_same_quiz_twice_to_same_class(): void
    {
        $teacher = $this->makeTeacher();
        $quiz    = Quiz::factory()->create(['teacher_id' => $teacher->id]);
        $class   = ClassRoom::factory()->create(['teacher_id' => $teacher->id]);

        $class->quizzes()->attach($quiz->id, ['assigned_at' => now()]);

        $response = $this->actingAs($teacher)->postJson("/api/classes/{$class->id}/assign-quiz", [
            'quiz_id' => $quiz->id,
        ]);

        $response->assertStatus(409)
                 ->assertJsonPath('message', 'Quiz is already assigned to this class.');
    }

    public function test_cannot_assign_another_teachers_quiz_to_class(): void
    {
        $teacher = $this->makeTeacher();
        $other   = $this->makeTeacher();
        $quiz    = Quiz::factory()->create(['teacher_id' => $other->id]);
        $class   = ClassRoom::factory()->create(['teacher_id' => $teacher->id]);

        $response = $this->actingAs($teacher)->postJson("/api/classes/{$class->id}/assign-quiz", [
            'quiz_id' => $quiz->id,
        ]);

        $response->assertStatus(404);
    }
}