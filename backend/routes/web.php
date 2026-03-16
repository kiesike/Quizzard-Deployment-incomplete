<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminActivationController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AdminQuizController;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->group(function () {
    // Guest routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
    });

    // Authenticated admin routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/profile', function () {
            return view('admin.profile.index');
        })->name('admin.profile');

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

        Route::get('/classes', [AdminQuizController::class, 'index'])
        ->name('admin.classes');

        Route::get('/classes/{id}', [AdminQuizController::class, 'show']);

        Route::put('/classes/{id}', [AdminQuizController::class, 'update']);

        Route::delete('/classes/{id}', [AdminQuizController::class, 'destroy']);
    });
});