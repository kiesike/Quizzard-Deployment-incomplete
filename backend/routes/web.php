<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminActivationController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AdminQuizController;
use App\Http\Controllers\TeacherAuthController;
use App\Http\Controllers\TeacherDashboardController;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::prefix('admin')->group(function () {
    // Guest routes
    Route::middleware('admin.guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');

        Route::get('/activation', [AdminActivationController::class, 'index'])->name('admin.activation.index');
        Route::patch('/activation/{user}/activate', [AdminActivationController::class, 'activate'])->name('admin.activation.activate');
        Route::patch('/activation/{user}/deactivate', [AdminActivationController::class, 'deactivate'])->name('admin.activation.deactivate');

        Route::get('/profile', [AdminProfileController::class, 'index'])->name('admin.profile');
        Route::post('/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');

        Route::get('/classes', [AdminQuizController::class, 'index'])->name('admin.classes');
        Route::get('/classes/{id}', [AdminQuizController::class, 'show']);
        Route::put('/classes/{id}', [AdminQuizController::class, 'update']);
        Route::delete('/classes/{id}', [AdminQuizController::class, 'destroy']);
    });
});

Route::prefix('teacher')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [TeacherAuthController::class, 'showLoginForm'])->name('teacher.login');
        Route::post('/login', [TeacherAuthController::class, 'login'])->name('teacher.login.submit');
    });

    Route::middleware(['auth', 'teacher'])->group(function () {
        Route::post('/logout', [TeacherAuthController::class, 'logout'])->name('teacher.logout');

        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');

        Route::get('/reports/classes', [TeacherDashboardController::class, 'classes'])->name('teacher.reports.classes');
        Route::get('/reports/quizzes', [TeacherDashboardController::class, 'quizzes'])->name('teacher.reports.quizzes');
        Route::get('/reports/students', [TeacherDashboardController::class, 'students'])->name('teacher.reports.students');
    });
});