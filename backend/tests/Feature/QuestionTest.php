<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionTest extends TestCase
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

    private function makeQuiz($teacher)
    {
        return Quiz::create([
            'teacher_id'   => $teacher->id,
            'title'        => 'Sample Quiz',
            'description'  => 'Sample Description',
            'is_published' => false,
        ]);
    }

    // ─── INDEX ───────────────────────────────────────────────────────────────

    public function test_teacher_can_get_all_questions_for_a_quiz(): void
    {
        $teacher = $this->makeTeacher();
        $quiz = $this->makeQuiz($teacher);

        Question::create([
            'quiz_id'       => $quiz->id,
            'question_text' => 'What is 1+1?',
            'question_type' => 'identification',
            'points'        => 1,
            'order'         => 1,
        ]);

        $response = $this->actingAs($teacher)->getJson("/api/quizzes/{$quiz->id}/questions");

        $response->assertStatus(200)
                 ->assertJsonPath('quiz.id', $quiz->id)
                 ->assertJsonCount(1, 'questions');
    }

    // ─── MULTIPLE CHOICE ─────────────────────────────────────────────────────

    public function test_teacher_can_create_multiple_choice_question(): void
    {
        $teacher = $this->makeTeacher();
        $quiz = $this->makeQuiz($teacher);

        $response = $this->actingAs($teacher)->postJson("/api/quizzes/{$quiz->id}/questions/multiple-choice", [
            'question_text' => 'What is the capital of France?',
            'points'        => 2,
            'options'       => [
                ['option_text' => 'Paris',  'is_correct' => true],
                ['option_text' => 'London', 'is_correct' => false],
                ['option_text' => 'Berlin', 'is_correct' => false],
                ['option_text' => 'Madrid', 'is_correct' => false],
            ],
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Multiple choice question created successfully.');
    }

    public function test_multiple_choice_fails_if_no_correct_answer(): void
    {
        $teacher = $this->makeTeacher();
        $quiz = $this->makeQuiz($teacher);

        $response = $this->actingAs($teacher)->postJson("/api/quizzes/{$quiz->id}/questions/multiple-choice", [
            'question_text' => 'What is the capital of France?',
            'options'       => [
                ['option_text' => 'Paris',  'is_correct' => false],
                ['option_text' => 'London', 'is_correct' => false],
            ],
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('message', 'Multiple choice questions must have exactly one correct answer.');
    }

    public function test_multiple_choice_fails_if_more_than_one_correct_answer(): void
    {
        $teacher = $this->makeTeacher();
        $quiz = $this->makeQuiz($teacher);

        $response = $this->actingAs($teacher)->postJson("/api/quizzes/{$quiz->id}/questions/multiple-choice", [
            'question_text' => 'What is the capital of France?',
            'options'       => [
                ['option_text' => 'Paris',  'is_correct' => true],
                ['option_text' => 'London', 'is_correct' => true],
            ],
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('message', 'Multiple choice questions must have exactly one correct answer.');
    }

    // ─── TRUE/FALSE ──────────────────────────────────────────────────────────

    public function test_teacher_can_create_true_false_question(): void
    {
        $teacher = $this->makeTeacher();
        $quiz = $this->makeQuiz($teacher);

        $response = $this->actingAs($teacher)->postJson("/api/quizzes/{$quiz->id}/questions/true-false", [
            'question_text'  => 'The sky is blue.',
            'points'         => 1,
            'correct_answer' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'True or False question created successfully.');
    }

    // ─── IDENTIFICATION ──────────────────────────────────────────────────────

    public function test_teacher_can_create_identification_question(): void
    {
        $teacher = $this->makeTeacher();
        $quiz = $this->makeQuiz($teacher);

        $response = $this->actingAs($teacher)->postJson("/api/quizzes/{$quiz->id}/questions/identification", [
            'question_text' => 'What is the chemical symbol for water?',
            'points'        => 1,
            'answer'        => 'H2O',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Identification question created successfully.');
    }

    // ─── MATCHING ────────────────────────────────────────────────────────────

    public function test_teacher_can_create_matching_question(): void
    {
        $teacher = $this->makeTeacher();
        $quiz = $this->makeQuiz($teacher);

        $response = $this->actingAs($teacher)->postJson("/api/quizzes/{$quiz->id}/questions/matching", [
            'question_text' => 'Match the countries to their capitals.',
            'points'        => 3,
            'pairs'         => [
                ['left' => 'France',  'right' => 'Paris'],
                ['left' => 'Germany', 'right' => 'Berlin'],
            ],
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Matching type question created successfully.');
    }

    // ─── UPDATE ──────────────────────────────────────────────────────────────

    public function test_teacher_can_update_a_question(): void
    {
        $teacher = $this->makeTeacher();
        $quiz = $this->makeQuiz($teacher);

        $question = Question::create([
            'quiz_id'       => $quiz->id,
            'question_text' => 'Old question text',
            'question_type' => 'identification',
            'points'        => 1,
            'order'         => 1,
        ]);

        $response = $this->actingAs($teacher)->putJson("/api/quizzes/{$quiz->id}/questions/{$question->id}", [
            'question_text' => 'Updated question text',
            'points'        => 2,
            'answer'        => 'Updated Answer',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Question updated successfully.');
    }

    // ─── DELETE ──────────────────────────────────────────────────────────────

    public function test_teacher_can_delete_a_question(): void
    {
        $teacher = $this->makeTeacher();
        $quiz = $this->makeQuiz($teacher);

        $question = Question::create([
            'quiz_id'       => $quiz->id,
            'question_text' => 'Question to delete',
            'question_type' => 'identification',
            'points'        => 1,
            'order'         => 1,
        ]);

        $response = $this->actingAs($teacher)->deleteJson("/api/quizzes/{$quiz->id}/questions/{$question->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Question deleted successfully.');

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }

    // ─── AUTHORIZATION ───────────────────────────────────────────────────────

    public function test_non_owner_teacher_cannot_add_question(): void
    {
        $owner = $this->makeTeacher();
        $other = $this->makeTeacher();
        $quiz = $this->makeQuiz($owner);

        $response = $this->actingAs($other)->postJson("/api/quizzes/{$quiz->id}/questions/identification", [
            'question_text' => 'Unauthorized question',
            'answer'        => 'Answer',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_owner_teacher_cannot_update_question(): void
    {
        $owner = $this->makeTeacher();
        $other = $this->makeTeacher();
        $quiz = $this->makeQuiz($owner);

        $question = Question::create([
            'quiz_id'       => $quiz->id,
            'question_text' => 'Original',
            'question_type' => 'identification',
            'points'        => 1,
            'order'         => 1,
        ]);

        $response = $this->actingAs($other)->putJson("/api/quizzes/{$quiz->id}/questions/{$question->id}", [
            'question_text' => 'Hacked',
            'points'        => 1,
            'answer'        => 'Hacked Answer',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_owner_teacher_cannot_delete_question(): void
    {
        $owner = $this->makeTeacher();
        $other = $this->makeTeacher();
        $quiz = $this->makeQuiz($owner);

        $question = Question::create([
            'quiz_id'       => $quiz->id,
            'question_text' => 'Original',
            'question_type' => 'identification',
            'points'        => 1,
            'order'         => 1,
        ]);

        $response = $this->actingAs($other)->deleteJson("/api/quizzes/{$quiz->id}/questions/{$question->id}");

        $response->assertStatus(403);
    }

    // ─── QUIZ HAS ATTEMPTS ───────────────────────────────────────────────────

    public function test_cannot_add_question_if_quiz_has_attempts(): void
    {
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent();
        $quiz = $this->makeQuiz($teacher);

        QuizAttempt::create([
            'quiz_id'    => $quiz->id,
            'student_id' => $student->id,
            'score'      => 0,
            'total_points' => 10,
            'status'     => 'completed',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($teacher)->postJson("/api/quizzes/{$quiz->id}/questions/identification", [
            'question_text' => 'New question',
            'answer'        => 'Answer',
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'This quiz cannot be modified because students have already taken it.');
    }

    public function test_cannot_update_question_if_quiz_has_attempts(): void
    {
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent();
        $quiz = $this->makeQuiz($teacher);

        $question = Question::create([
            'quiz_id'       => $quiz->id,
            'question_text' => 'Original',
            'question_type' => 'identification',
            'points'        => 1,
            'order'         => 1,
        ]);

        QuizAttempt::create([
            'quiz_id'    => $quiz->id,
            'student_id' => $student->id,
            'score'      => 0,
            'total_points' => 10,
            'status'     => 'completed',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($teacher)->putJson("/api/quizzes/{$quiz->id}/questions/{$question->id}", [
            'question_text' => 'Updated',
            'points'        => 1,
            'answer'        => 'Updated Answer',
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'This question cannot be edited because students have already taken this quiz.');
    }

    public function test_cannot_delete_question_if_quiz_has_attempts(): void
    {
        $teacher = $this->makeTeacher();
        $student = $this->makeStudent();
        $quiz = $this->makeQuiz($teacher);

        $question = Question::create([
            'quiz_id'       => $quiz->id,
            'question_text' => 'Original',
            'question_type' => 'identification',
            'points'        => 1,
            'order'         => 1,
        ]);

        QuizAttempt::create([
            'quiz_id'    => $quiz->id,
            'student_id' => $student->id,
            'score'      => 0,
            'total_points' => 10,
            'status'     => 'completed',
            'started_at' => now(),
        ]);

        $response = $this->actingAs($teacher)->deleteJson("/api/quizzes/{$quiz->id}/questions/{$question->id}");

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'This question cannot be deleted because students have already taken this quiz.');
    }
}