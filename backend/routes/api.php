<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\QuestionController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Student routes
    Route::get('/student/dashboard', [StudentController::class, 'dashboard']);

    // Teacher routes
    Route::get('/teacher/dashboard', [TeacherController::class, 'dashboard']);

    // Question routes
    Route::get('/quizzes/{quizId}/questions', [QuestionController::class, 'index']);
    Route::post('/quizzes/{quizId}/questions/multiple-choice', [QuestionController::class, 'storeMultipleChoice']);
    Route::post('/quizzes/{quizId}/questions/true-false', [QuestionController::class, 'storeTrueFalse']);
    Route::post('/quizzes/{quizId}/questions/identification', [QuestionController::class, 'storeIdentification']);
    Route::post('/quizzes/{quizId}/questions/matching', [QuestionController::class, 'storeMatching']);
    Route::put('/quizzes/{quizId}/questions/{questionId}', [QuestionController::class, 'update']);
    Route::delete('/quizzes/{quizId}/questions/{questionId}', [QuestionController::class, 'destroy']);
});