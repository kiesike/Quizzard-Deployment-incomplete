<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\AnswerOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    // Get all questions for a quiz
    public function index($quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        $questions = Question::where('quiz_id', $quizId)
            ->orderBy('order')
            ->with('answerOptions')
            ->get()
            ->map(function ($question) {
                return [
                    'id'            => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'media_path'    => $question->media_path,
                    'media_type'    => $question->media_type,
                    'image_path'    => $question->image_path
                        ? asset('storage/' . $question->image_path)
                        : null,
                    'video_path'    => $question->video_path
                        ? asset('storage/' . $question->video_path)
                        : null,
                    'audio_path'    => $question->audio_path
                        ? asset('storage/' . $question->audio_path)
                        : null,
                    'points'        => $question->points,
                    'order'         => $question->order,
                    'answer_options' => $question->answerOptions->map(function ($option) {
                        return [
                            'id'          => $option->id,
                            'option_text' => $option->option_text,
                            'is_correct'  => $option->is_correct,
                            'match_pair'  => $option->match_pair,
                            'image_path'  => $option->image_path
                                ? asset('storage/' . $option->image_path)
                                : null,
                            'video_path'  => $option->video_path
                                ? asset('storage/' . $option->video_path)
                                : null,
                            'audio_path'  => $option->audio_path
                                ? asset('storage/' . $option->audio_path)
                                : null,
                            'order'       => $option->order,
                        ];
                    }),
                ];
            });

        return response()->json([
            'quiz'      => [
                'id'    => $quiz->id,
                'title' => $quiz->title,
            ],
            'questions' => $questions,
        ]);
    }

    // Check if quiz has attempts (helper)
    private function quizHasAttempts($quizId)
    {
        return QuizAttempt::where('quiz_id', $quizId)->exists();
    }

    // Create a multiple choice question
    public function storeMultipleChoice(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        if ($quiz->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Block if quiz has attempts
        if ($this->quizHasAttempts($quizId)) {
            return response()->json([
                'message' => 'This quiz cannot be modified because students have already taken it.',
            ], 403);
        }

        $request->validate([
            'question_text'           => 'required|string',
            'image_path'              => 'nullable|string',
            'video_path'              => 'nullable|string',
            'audio_path'              => 'nullable|string',
            'points'                  => 'integer|min:1',
            'options'                 => 'required|array|min:2',
            'options.*.option_text'   => 'required|string',
            'options.*.is_correct'    => 'required|boolean',
            'options.*.image_path'    => 'nullable|string',
            'options.*.video_path'    => 'nullable|string',
            'options.*.audio_path'    => 'nullable|string',
        ]);

        $correctCount = collect($request->options)
            ->where('is_correct', true)
            ->count();

        if ($correctCount !== 1) {
            return response()->json([
                'message' => 'Multiple choice questions must have exactly one correct answer.'
            ], 422);
        }

        $question = Question::create([
            'quiz_id'       => $quizId,
            'question_text' => $request->question_text,
            'question_type' => 'multiple_choice',
            'image_path'    => $request->image_path,
            'video_path'    => $request->video_path,
            'audio_path'    => $request->audio_path,
            'points'        => $request->points ?? 1,
            'order'         => Question::where('quiz_id', $quizId)->count() + 1,
        ]);

        foreach ($request->options as $index => $option) {
            AnswerOption::create([
                'question_id' => $question->id,
                'option_text' => $option['option_text'],
                'image_path'  => $option['image_path'] ?? null,
                'video_path'  => $option['video_path'] ?? null,
                'audio_path'  => $option['audio_path'] ?? null,
                'is_correct'  => $option['is_correct'],
                'order'       => $index + 1,
            ]);
        }

        return response()->json([
            'message'  => 'Multiple choice question created successfully.',
            'question' => $question->load('answerOptions'),
        ], 201);
    }

    // Create a true or false question
    public function storeTrueFalse(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        if ($quiz->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Block if quiz has attempts
        if ($this->quizHasAttempts($quizId)) {
            return response()->json([
                'message' => 'This quiz cannot be modified because students have already taken it.',
            ], 403);
        }

        $request->validate([
            'question_text'  => 'required|string',
            'image_path'     => 'nullable|string',
            'video_path'     => 'nullable|string',
            'audio_path'     => 'nullable|string',
            'points'         => 'integer|min:1',
            'correct_answer' => 'required|boolean',
        ]);

        $question = Question::create([
            'quiz_id'       => $quizId,
            'question_text' => $request->question_text,
            'question_type' => 'true_false',
            'image_path'    => $request->image_path,
            'video_path'    => $request->video_path,
            'audio_path'    => $request->audio_path,
            'points'        => $request->points ?? 1,
            'order'         => Question::where('quiz_id', $quizId)->count() + 1,
        ]);

        AnswerOption::create([
            'question_id' => $question->id,
            'option_text' => 'True',
            'is_correct'  => $request->correct_answer === true,
            'order'       => 1,
        ]);

        AnswerOption::create([
            'question_id' => $question->id,
            'option_text' => 'False',
            'is_correct'  => $request->correct_answer === false,
            'order'       => 2,
        ]);

        return response()->json([
            'message'  => 'True or False question created successfully.',
            'question' => $question->load('answerOptions'),
        ], 201);
    }

    // Create an identification question
    public function storeIdentification(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        if ($quiz->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Block if quiz has attempts
        if ($this->quizHasAttempts($quizId)) {
            return response()->json([
                'message' => 'This quiz cannot be modified because students have already taken it.',
            ], 403);
        }

        $request->validate([
            'question_text' => 'required|string',
            'image_path'    => 'nullable|string',
            'video_path'    => 'nullable|string',
            'audio_path'    => 'nullable|string',
            'answer'        => 'required|string',
            'points'        => 'integer|min:1',
        ]);

        $question = Question::create([
            'quiz_id'       => $quizId,
            'question_text' => $request->question_text,
            'question_type' => 'identification',
            'image_path'    => $request->image_path,
            'video_path'    => $request->video_path,
            'audio_path'    => $request->audio_path,
            'points'        => $request->points ?? 1,
            'order'         => Question::where('quiz_id', $quizId)->count() + 1,
        ]);

        AnswerOption::create([
            'question_id' => $question->id,
            'option_text' => $request->answer,
            'is_correct'  => true,
            'order'       => 1,
        ]);

        return response()->json([
            'message'  => 'Identification question created successfully.',
            'question' => $question->load('answerOptions'),
        ], 201);
    }

    // Create a matching type question
    public function storeMatching(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        if ($quiz->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Block if quiz has attempts
        if ($this->quizHasAttempts($quizId)) {
            return response()->json([
                'message' => 'This quiz cannot be modified because students have already taken it.',
            ], 403);
        }

        $request->validate([
            'question_text' => 'required|string',
            'image_path'    => 'nullable|string',
            'video_path'    => 'nullable|string',
            'audio_path'    => 'nullable|string',
            'points'        => 'integer|min:1',
            'pairs'         => 'required|array|min:2',
            'pairs.*.left'  => 'required|string',
            'pairs.*.right' => 'required|string',
        ]);

        $question = Question::create([
            'quiz_id'       => $quizId,
            'question_text' => $request->question_text,
            'question_type' => 'matching',
            'image_path'    => $request->image_path,
            'video_path'    => $request->video_path,
            'audio_path'    => $request->audio_path,
            'points'        => $request->points ?? 1,
            'order'         => Question::where('quiz_id', $quizId)->count() + 1,
        ]);

        foreach ($request->pairs as $index => $pair) {
            AnswerOption::create([
                'question_id' => $question->id,
                'option_text' => $pair['left'],
                'match_pair'  => $pair['right'],
                'is_correct'  => true,
                'order'       => $index + 1,
            ]);
        }

        return response()->json([
            'message'  => 'Matching type question created successfully.',
            'question' => $question->load('answerOptions'),
        ], 201);
    }

    // Update a question (handles all question types)
    public function update(Request $request, $quizId, $questionId)
    {
        $quiz = Quiz::findOrFail($quizId);

        if ($quiz->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Block if quiz has attempts
        if ($this->quizHasAttempts($quizId)) {
            return response()->json([
                'message' => 'This question cannot be edited because students have already taken this quiz.',
            ], 403);
        }

        $question = Question::where('quiz_id', $quizId)->findOrFail($questionId);

        $request->validate([
            'question_text' => 'required|string',
            'image_path'    => 'nullable|string',
            'video_path'    => 'nullable|string',
            'audio_path'    => 'nullable|string',
            'points'        => 'integer|min:1',
        ]);

        $question->update([
            'question_text' => $request->question_text,
            'image_path'    => $request->image_path,
            'video_path'    => $request->video_path,
            'audio_path'    => $request->audio_path,
            'points'        => $request->points ?? 1,
        ]);

        // Delete old options — we always recreate them
        $question->answerOptions()->delete();

        switch ($question->question_type) {

            case 'multiple_choice':
                $request->validate([
                    'options'               => 'required|array|min:2|max:4',
                    'options.*.option_text' => 'required|string',
                    'options.*.is_correct'  => 'required|boolean',
                    'options.*.image_path'  => 'nullable|string',
                    'options.*.video_path'  => 'nullable|string',
                    'options.*.audio_path'  => 'nullable|string',
                ]);
                $correctCount = collect($request->options)->where('is_correct', true)->count();
                if ($correctCount !== 1) {
                    return response()->json([
                        'message' => 'Multiple choice must have exactly one correct answer.'
                    ], 422);
                }
                foreach ($request->options as $index => $option) {
                    AnswerOption::create([
                        'question_id' => $question->id,
                        'option_text' => $option['option_text'],
                        'is_correct'  => $option['is_correct'],
                        'image_path'  => $option['image_path'] ?? null,
                        'video_path'  => $option['video_path'] ?? null,
                        'audio_path'  => $option['audio_path'] ?? null,
                        'order'       => $index + 1,
                    ]);
                }
                break;

            case 'true_false':
                $request->validate([
                    'correct_answer' => 'required|boolean',
                ]);
                AnswerOption::create([
                    'question_id' => $question->id,
                    'option_text' => 'True',
                    'is_correct'  => $request->correct_answer === true,
                    'order'       => 1,
                ]);
                AnswerOption::create([
                    'question_id' => $question->id,
                    'option_text' => 'False',
                    'is_correct'  => $request->correct_answer === false,
                    'order'       => 2,
                ]);
                break;

            case 'identification':
                $request->validate([
                    'answer' => 'required|string',
                ]);
                AnswerOption::create([
                    'question_id' => $question->id,
                    'option_text' => $request->answer,
                    'is_correct'  => true,
                    'order'       => 1,
                ]);
                break;

            case 'matching':
                $request->validate([
                    'pairs'        => 'required|array|min:2',
                    'pairs.*.left' => 'required|string',
                    'pairs.*.right'=> 'required|string',
                ]);
                foreach ($request->pairs as $index => $pair) {
                    AnswerOption::create([
                        'question_id' => $question->id,
                        'option_text' => $pair['left'],
                        'match_pair'  => $pair['right'],
                        'is_correct'  => true,
                        'order'       => $index + 1,
                    ]);
                }
                break;
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Question updated successfully.',
            'question' => $question->load('answerOptions'),
        ]);
    }

    // Delete a question
    public function destroy(Request $request, $quizId, $questionId)
    {
        $quiz = Quiz::findOrFail($quizId);

        if ($quiz->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Block if quiz has attempts
        if ($this->quizHasAttempts($quizId)) {
            return response()->json([
                'message' => 'This question cannot be deleted because students have already taken this quiz.',
            ], 403);
        }

        $question = Question::where('quiz_id', $quizId)
            ->findOrFail($questionId);

        $question->delete();

        return response()->json([
            'message' => 'Question deleted successfully.'
        ]);
    }
}