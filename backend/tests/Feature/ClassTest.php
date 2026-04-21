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

    // ─── HELPERS ─────────────────────────────────────────────

    protected function createUser(string $role = 'student', array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role'   => $role,
            'status' => 'active',
        ], $overrides));
    }

    protected function createClass(User $teacher, array $overrides = []): ClassRoom
    {
        return ClassRoom::factory()->create(array_merge([
            'teacher_id' => $teacher->id,
        ], $overrides));
    }

    protected function createQuiz(User $teacher, array $overrides = []): Quiz
    {
        return Quiz::factory()->create(array_merge([
            'teacher_id' => $teacher->id,
        ], $overrides));
    }

    // ─── REQUEST HELPERS ─────────────────────────────────────

    protected function createClassRequest(User $teacher, array $data)
    {
        return $this->actingAs($teacher)->postJson('/api/classes', $data);
    }

    protected function updateClassRequest(User $teacher, int $classId, array $data)
    {
        return $this->actingAs($teacher)->putJson("/api/classes/{$classId}", $data);
    }

    protected function deleteClassRequest(User $teacher, int $classId)
    {
        return $this->actingAs($teacher)->deleteJson("/api/classes/{$classId}");
    }

    // ─── TEACHER CLASS MANAGEMENT ───────────────────────────

    public function test_teacher_can_create_a_class(): void
    {
        $teacher = $this->createUser('teacher');

        $response = $this->createClassRequest($teacher, [
            'name' => 'Science Class',
            'description' => 'A class about science.',
        ]);

        $response->assertCreated()
                 ->assertJsonPath('message', 'Class created successfully.');

        $this->assertDatabaseHas('classes', [
            'name' => 'Science Class',
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_teacher_can_get_all_their_classes(): void
    {
        $teacher = $this->createUser('teacher');

        ClassRoom::factory()->count(3)->create([
            'teacher_id' => $teacher->id
        ]);

        $this->actingAs($teacher)
             ->getJson('/api/classes')
             ->assertOk()
             ->assertJsonCount(3, 'classes');
    }

    public function test_teacher_can_get_a_single_class(): void
    {
        $teacher = $this->createUser('teacher');
        $class   = $this->createClass($teacher);

        $this->actingAs($teacher)
             ->getJson("/api/classes/{$class->id}")
             ->assertOk()
             ->assertJsonPath('class.id', $class->id);
    }

    public function test_teacher_can_update_a_class(): void
    {
        $teacher = $this->createUser('teacher');
        $class   = $this->createClass($teacher);

        $response = $this->updateClassRequest($teacher, $class->id, [
            'name' => 'Updated Class Name',
            'description' => 'Updated description.',
        ]);

        $response->assertOk()
                 ->assertJsonPath('message', 'Class updated successfully.');

        $this->assertDatabaseHas('classes', [
            'id' => $class->id,
            'name' => 'Updated Class Name',
        ]);
    }

    public function test_teacher_can_delete_a_class(): void
    {
        $teacher = $this->createUser('teacher');
        $class   = $this->createClass($teacher);

        $this->deleteClassRequest($teacher, $class->id)
             ->assertOk()
             ->assertJsonPath('message', 'Class deleted successfully.');

        $this->assertDatabaseMissing('classes', [
            'id' => $class->id
        ]);
    }

    // ─── VALIDATION ─────────────────────────────────────────

    public function test_create_class_fails_with_empty_name(): void
    {
        $teacher = $this->createUser('teacher');

        $this->createClassRequest($teacher, [
            'name' => '',
            'description' => 'Some description.',
        ])->assertUnprocessable();
    }

    public function test_create_class_fails_with_spaces_only_name(): void
    {
        $teacher = $this->createUser('teacher');

        $this->createClassRequest($teacher, [
            'name' => '     ',
            'description' => 'Some description.',
        ])->assertUnprocessable();
    }

    public function test_create_class_fails_with_name_over_100_characters(): void
    {
        $teacher = $this->createUser('teacher');

        $this->createClassRequest($teacher, [
            'name' => str_repeat('A', 101),
            'description' => 'Some description.',
        ])->assertUnprocessable();
    }

    // ─── AUTHORIZATION ─────────────────────────────────────

    public function test_non_owner_teacher_cannot_update_a_class(): void
    {
        $owner = $this->createUser('teacher');
        $other = $this->createUser('teacher');
        $class = $this->createClass($owner);

        $this->updateClassRequest($other, $class->id, [
            'name' => 'Hacked',
            'description' => 'Hacked',
        ])->assertNotFound();
    }

    public function test_non_owner_teacher_cannot_delete_a_class(): void
    {
        $owner = $this->createUser('teacher');
        $other = $this->createUser('teacher');
        $class = $this->createClass($owner);

        $this->deleteClassRequest($other, $class->id)
             ->assertNotFound();
    }

    // ─── QUIZ UNASSIGNMENT ────────────────────────────────

    public function test_teacher_can_unassign_a_quiz_from_a_class(): void
    {
        $teacher = $this->createUser('teacher');
        $class   = $this->createClass($teacher);
        $quiz    = $this->createQuiz($teacher);

        $class->quizzes()->attach($quiz->id, ['assigned_at' => now()]);

        $this->actingAs($teacher)
             ->deleteJson("/api/classes/{$class->id}/quizzes/{$quiz->id}")
             ->assertOk()
             ->assertJsonPath('message', 'Quiz removed from class successfully.');

        $this->assertDatabaseMissing('class_quizzes', [
            'class_id' => $class->id,
            'quiz_id'  => $quiz->id,
        ]);
    }

    // ─── STUDENT ACTIONS ───────────────────────────────────

    public function test_student_can_join_a_class_with_valid_code(): void
    {
        $teacher = $this->createUser('teacher');
        $student = $this->createUser('student');
        $class   = $this->createClass($teacher);

        $this->actingAs($student)
             ->postJson('/api/student/classes/join', [
                 'class_code' => $class->class_code,
             ])
             ->assertOk()
             ->assertJsonPath('message', 'Successfully joined the class!');

        $this->assertDatabaseHas('class_students', [
            'class_id' => $class->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_student_join_fails_with_invalid_code(): void
    {
        $student = $this->createUser('student');

        $this->actingAs($student)
             ->postJson('/api/student/classes/join', [
                 'class_code' => 'INVALID',
             ])
             ->assertNotFound()
             ->assertJsonPath('message', 'Invalid class code. Please check and try again.');
    }

    public function test_student_join_fails_with_empty_code(): void
    {
        $student = $this->createUser('student');

        $this->actingAs($student)
             ->postJson('/api/student/classes/join', [
                 'class_code' => '',
             ])
             ->assertUnprocessable();
    }

    public function test_student_join_fails_if_already_enrolled(): void
    {
        $teacher = $this->createUser('teacher');
        $student = $this->createUser('student');
        $class   = $this->createClass($teacher);

        $class->students()->attach($student->id, ['joined_at' => now()]);

        $this->actingAs($student)
             ->postJson('/api/student/classes/join', [
                 'class_code' => $class->class_code,
             ])
             ->assertStatus(409)
             ->assertJsonPath('message', 'You are already enrolled in this class.');
    }

    public function test_student_join_with_lowercase_code_still_works(): void
    {
        $teacher = $this->createUser('teacher');
        $student = $this->createUser('student');
        $class   = $this->createClass($teacher);

        $this->actingAs($student)
             ->postJson('/api/student/classes/join', [
                 'class_code' => strtolower($class->class_code),
             ])
             ->assertOk();
    }

    public function test_student_can_leave_a_class(): void
    {
        $teacher = $this->createUser('teacher');
        $student = $this->createUser('student');
        $class   = $this->createClass($teacher);

        $class->students()->attach($student->id, ['joined_at' => now()]);

        $this->actingAs($student)
             ->deleteJson("/api/student/classes/{$class->id}/leave")
             ->assertOk()
             ->assertJsonPath('message', 'You have left the class successfully.');

        $this->assertDatabaseMissing('class_students', [
            'class_id' => $class->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_student_cannot_leave_a_class_they_are_not_enrolled_in(): void
    {
        $teacher = $this->createUser('teacher');
        $student = $this->createUser('student');
        $class   = $this->createClass($teacher);

        $this->actingAs($student)
             ->deleteJson("/api/student/classes/{$class->id}/leave")
             ->assertNotFound()
             ->assertJsonPath('message', 'You are not enrolled in this class.');
    }

    public function test_student_can_get_quizzes_in_their_class(): void
    {
        $teacher = $this->createUser('teacher');
        $student = $this->createUser('student');
        $class   = $this->createClass($teacher);

        $quiz = $this->createQuiz($teacher, [
            'is_published' => true
        ]);

        $class->students()->attach($student->id, ['joined_at' => now()]);
        $class->quizzes()->attach($quiz->id, ['assigned_at' => now()]);

        $this->actingAs($student)
             ->getJson("/api/student/classes/{$class->id}/quizzes")
             ->assertOk()
             ->assertJsonCount(1, 'quizzes');
    }
}