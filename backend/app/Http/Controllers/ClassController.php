<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Quiz;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    // Get all classes for the teacher
    public function index(Request $request)
    {
        $classes = ClassRoom::where('teacher_id', $request->user()->id)
            ->withCount('students')
            ->withCount('quizzes')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['classes' => $classes]);
    }

    // Create a new class
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $class = ClassRoom::create([
            'teacher_id'  => $request->user()->id,
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Class created successfully.',
            'class'   => $class->loadCount(['students', 'quizzes']),
        ], 201);
    }

    // Get a single class with full details
    public function show(Request $request, $classId)
    {
        $class = ClassRoom::where('id', $classId)
            ->where('teacher_id', $request->user()->id)
            ->with(['students' => function ($q) {
                $q->select('users.id', 'users.name', 'users.email')
                  ->orderBy('users.name');
            }])
            ->with(['quizzes' => function ($q) {
                $q->select('quizzes.id', 'quizzes.title', 'quizzes.is_published')
                  ->withCount('questions');
            }])
            ->firstOrFail();

        return response()->json(['class' => $class]);
    }

    // Update a class
    public function update(Request $request, $classId)
    {
        $class = ClassRoom::where('id', $classId)
            ->where('teacher_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $class->update([
            'name'        => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Class updated successfully.',
            'class'   => $class->loadCount(['students', 'quizzes']),
        ]);
    }

    // Delete a class
    public function destroy(Request $request, $classId)
    {
        $class = ClassRoom::where('id', $classId)
            ->where('teacher_id', $request->user()->id)
            ->firstOrFail();

        $class->delete();

        return response()->json(['message' => 'Class deleted successfully.']);
    }

    // Assign a quiz to a class
    public function assignQuiz(Request $request, $classId)
    {
        $class = ClassRoom::where('id', $classId)
            ->where('teacher_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'quiz_id' => 'required|integer|exists:quizzes,id',
        ]);

        // Make sure quiz belongs to teacher
        $quiz = Quiz::where('id', $request->quiz_id)
            ->where('teacher_id', $request->user()->id)
            ->firstOrFail();

        // Check if already assigned
        if ($class->quizzes()->where('quiz_id', $quiz->id)->exists()) {
            return response()->json([
                'message' => 'Quiz is already assigned to this class.',
            ], 409);
        }

        $class->quizzes()->attach($quiz->id, ['assigned_at' => now()]);

        return response()->json([
            'message' => 'Quiz assigned to class successfully.',
        ]);
    }

    // Unassign a quiz from a class
    public function unassignQuiz(Request $request, $classId, $quizId)
    {
        $class = ClassRoom::where('id', $classId)
            ->where('teacher_id', $request->user()->id)
            ->firstOrFail();

        $class->quizzes()->detach($quizId);

        return response()->json([
            'message' => 'Quiz removed from class successfully.',
        ]);
    }
}