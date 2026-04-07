<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Quiz;
use App\Models\ClassRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeacher(array $overrides = [])
    {
        return User::factory()->create(array_merge([
            'role'   => 'teacher',
            'status' => 'active',
        ], $overrides));
    }

    private function makeStudent(array $overrides = [])
    {
        return User::factory()->create(array_merge([
            'role'   => 'student',
            'status' => 'active',
        ], $overrides));
    }

    private function makeClass($teacher, array $overrides = [])
    {
        return ClassRoom::factory()->create(array_merge([
            'teacher_id' => $teacher->id,
        ], $overrides));
    }

    // ─── TEACHER CLASS MANAGEMENT ────────────────────────────────────────────
    public function test_teacher_can_create_a_class(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->postJson('/api/classes', [
            'name'        => 'Science Class',
            'description' => 'A class about science.',
        ]);

        $response->assertStatus(201)
                ->assertJsonPath('message', 'Class created successfully.');

        $this->assertDatabaseHas('classes', [
            'name'       => 'Science Class',
            'teacher_id' => $teacher->id,
        ]);
    }
    

    public function test_teacher_can_get_all_their_classes(): void
    {
        $teacher = $this->makeTeacher();

        ClassRoom::factory()->count(3)->create(['teacher_id' => $teacher->id]);

        $response = $this->actingAs($teacher)->getJson('/api/classes');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'classes');
    }

    public function test_teacher_can_get_a_single_class(): void
    {
        $teacher = $this->makeTeacher();
        $class   = $this->makeClass($teacher);

        $response = $this->actingAs($teacher)->getJson("/api/classes/{$class->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('class.id', $class->id);
    }

    public function test_teacher_can_update_a_class(): void
    {
        $teacher = $this->makeTeacher();
        $class   = $this->makeClass($teacher);

        $response = $this->actingAs($teacher)->putJson("/api/classes/{$class->id}", [
            'name'        => 'Updated Class Name',
            'description' => 'Updated description.',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Class updated successfully.');

        $this->assertDatabaseHas('classes', [
            'id'   => $class->id,
            'name' => 'Updated Class Name',
        ]);
    }

    public function test_teacher_can_delete_a_class(): void
    {
        $teacher = $this->makeTeacher();
        $class   = $this->makeClass($teacher);

        $response = $this->actingAs($teacher)->deleteJson("/api/classes/{$class->id}");

        $response->assertStatus(200)
                ->assertJsonPath('message', 'Class deleted successfully.');

        $this->assertDatabaseMissing('classes', ['id' => $class->id]);
    }

    public function test_create_class_fails_with_empty_name(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->postJson('/api/classes', [
            'name'        => '',
            'description' => 'Some description.',
        ]);

        $response->assertStatus(422);
    }

    public function test_create_class_fails_with_spaces_only_name(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->postJson('/api/classes', [
            'name'        => '      ',
            'description' => 'Some description.',
        ]);

        $response->assertStatus(422);
    }

    public function test_create_class_fails_with_name_over_100_characters(): void
    {
        $teacher = $this->makeTeacher();

        $response = $this->actingAs($teacher)->postJson('/api/classes', [
            'name'        => str_repeat('A', 101),
            'description' => 'Some description.',
        ]);

        $response->assertStatus(422);
    }

    public function test_non_owner_teacher_cannot_update_a_class(): void
    {
        $owner = $this->makeTeacher();
        $other = $this->makeTeacher();
        $class = $this->makeClass($owner);

        $response = $this->actingAs($other)->putJson("/api/classes/{$class->id}", [
            'name'        => 'Hacked Name',
            'description' => 'Hacked.',
        ]);

        $response->assertStatus(404);
    }

    public function test_non_owner_teacher_cannot_delete_a_class(): void
    {
        $owner = $this->makeTeacher();
        $other = $this->makeTeacher();
        $class = $this->makeClass($owner);

        $response = $this->actingAs($other)->deleteJson("/api/classes/{$class->id}");

        $response->assertStatus(404);
    }

    // ─── QUIZ UNASSIGNMENT ───────────────────────────────────────────────────

    public function test_teacher_can_unassign_a_quiz_from_a_class(): void
    {
        $teacher = $this->makeTeacher();
        $class   = $this->makeClass($teacher);
        $quiz    = Quiz::factory()->create(['teacher_id' => $teacher->id]);

        $class->quizzes()->attach($quiz->id, ['assigned_at' => now()]);

        $response = $this->actingAs($teacher)->deleteJson("/api/classes/{$class->id}/quizzes/{$quiz->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Quiz removed from class successfully.');

        $this->assertDatabaseMissing('class_quizzes', [
            'class_id' => $class->id,
            'quiz_id'  => $quiz->id,
        ]);
    }

    // ─── STUDENT CLASS ACTIONS ───────────────────────────────────────────────

    public function test_student_can_join_a_class_with_valid_code(): void
    {
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent();
        $class   = $this->makeClass($teacher);

        $response = $this->actingAs($student)->postJson('/api/student/classes/join', [
            'class_code' => $class->class_code,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Successfully joined the class!');

        $this->assertDatabaseHas('class_students', [
            'class_id'   => $class->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_student_join_fails_with_invalid_code(): void
    {
        $student = $this->makeStudent();

        $response = $this->actingAs($student)->postJson('/api/student/classes/join', [
            'class_code' => 'INVALID',
        ]);

        $response->assertStatus(404)
                 ->assertJsonPath('message', 'Invalid class code. Please check and try again.');
    }

    public function test_student_join_fails_with_empty_code(): void
    {
        $student = $this->makeStudent();

        $response = $this->actingAs($student)->postJson('/api/student/classes/join', [
            'class_code' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_student_join_fails_if_already_enrolled(): void
    {
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent();
        $class   = $this->makeClass($teacher);

        $class->students()->attach($student->id, ['joined_at' => now()]);

        $response = $this->actingAs($student)->postJson('/api/student/classes/join', [
            'class_code' => $class->class_code,
        ]);

        $response->assertStatus(409)
                 ->assertJsonPath('message', 'You are already enrolled in this class.');
    }

    public function test_student_join_with_lowercase_code_still_works(): void
    {
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent();
        $class   = $this->makeClass($teacher);

        $response = $this->actingAs($student)->postJson('/api/student/classes/join', [
            'class_code' => strtolower($class->class_code),
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Successfully joined the class!');
    }

    public function test_student_can_leave_a_class(): void
    {
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent();
        $class   = $this->makeClass($teacher);

        $class->students()->attach($student->id, ['joined_at' => now()]);

        $response = $this->actingAs($student)->deleteJson("/api/student/classes/{$class->id}/leave");

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'You have left the class successfully.');

        $this->assertDatabaseMissing('class_students', [
            'class_id'   => $class->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_student_cannot_leave_a_class_they_are_not_enrolled_in(): void
    {
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent();
        $class   = $this->makeClass($teacher);

        $response = $this->actingAs($student)->deleteJson("/api/student/classes/{$class->id}/leave");

        $response->assertStatus(404)
                 ->assertJsonPath('message', 'You are not enrolled in this class.');
    }

    public function test_student_can_get_quizzes_in_their_class(): void
    {
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent();
        $class   = $this->makeClass($teacher);
        $quiz    = Quiz::factory()->create([
            'teacher_id'   => $teacher->id,
            'is_published' => true,
        ]);

        $class->students()->attach($student->id, ['joined_at' => now()]);
        $class->quizzes()->attach($quiz->id, ['assigned_at' => now()]);

        $response = $this->actingAs($student)->getJson("/api/student/classes/{$class->id}/quizzes");

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'quizzes');
    }
}