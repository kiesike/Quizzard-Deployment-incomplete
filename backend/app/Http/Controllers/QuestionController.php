<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\AnswerOption;
use App\Models\Quiz;
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
                    'points'        => $question->points,
                    'order'         => $question->order,
                    'answer_options' => $question->answerOptions->map(function ($option) {
                        return [
                            'id'         => $option->id,
                            'option_text' => $option->option_text,
                            'is_correct' => $option->is_correct,
                            'match_pair' => $option->match_pair,
                            'order'      => $option->order,
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

    // Create a multiple choice question
    public function storeMultipleChoice(Request $request, $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        // Make sure this teacher owns the quiz
        if ($quiz->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'question_text'          => 'required|string',
            'points'                 => 'integer|min:1',
            'options'                => 'required|array|min:2|max:4',
            'options.*.option_text'  => 'required|string',
            'options.*.is_correct'   => 'required|boolean',
        ]);

        // Make sure exactly one option is correct
        $correctCount = collect($request->options)
            ->where('is_correct', true)
            ->count();

        if ($correctCount !== 1) {
            return response()->json([
                'message' => 'Multiple choice questions must have exactly one correct answer.'
            ], 422);
        }

        // Create the question
        $question = Question::create([
            'quiz_id'       => $quizId,
            'question_text' => $request->question_text,
            'question_type' => 'multiple_choice',
            'points'        => $request->points ?? 1,
            'order'         => Question::where('quiz_id', $quizId)->count() + 1,
        ]);

        // Create the answer options
        foreach ($request->options as $index => $option) {
            AnswerOption::create([
                'question_id' => $question->id,
                'option_text' => $option['option_text'],
                'is_correct'  => $option['is_correct'],
                'order'       => $index + 1,
            ]);
        }

        return response()->json([
            'message'  => 'Multiple choice question created successfully.',
            'question' => $question->load('answerOptions'),
        ], 201);
    }

    // Update a question
    public function update(Request $request, $quizId, $questionId)
    {
        $quiz = Quiz::findOrFail($quizId);

        if ($quiz->teacher_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $question = Question::where('quiz_id', $quizId)
            ->findOrFail($questionId);

        $request->validate([
            'question_text'         => 'required|string',
            'points'                => 'integer|min:1',
            'options'               => 'required|array|min:2|max:4',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct'  => 'required|boolean',
        ]);

        $correctCount = collect($request->options)
            ->where('is_correct', true)
            ->count();

        if ($correctCount !== 1) {
            return response()->json([
                'message' => 'Multiple choice questions must have exactly one correct answer.'
            ], 422);
        }

        // Update question
        $question->update([
            'question_text' => $request->question_text,
            'points'        => $request->points ?? 1,
        ]);

        // Delete old options and recreate
        $question->answerOptions()->delete();
        foreach ($request->options as $index => $option) {
            AnswerOption::create([
                'question_id' => $question->id,
                'option_text' => $option['option_text'],
                'is_correct'  => $option['is_correct'],
                'order'       => $index + 1,
            ]);
        }

        return response()->json([
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

        $question = Question::where('quiz_id', $quizId)
            ->findOrFail($questionId);

        $question->delete();

        return response()->json([
            'message' => 'Question deleted successfully.'
        ]);
    }
}