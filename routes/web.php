<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskMoveController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to board
Route::get('/', fn() => redirect('/board'));

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Google OAuth
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

// Protected routes
Route::middleware('auth')->group(function () {
    // Board
    Route::get('/board', [BoardController::class, 'index'])->name('board');

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    Route::post('/tasks/{task}/complete', [TaskController::class, 'complete']);
    Route::post('/tasks/{task}/move', [TaskMoveController::class, 'move']);

    // Users (assignee list)
    Route::get('/users', [UserController::class, 'index']);

    // Admin-only routes (statuses only — categories accessible to all)
    Route::middleware('role:admin')->group(function () {
        Route::resource('statuses', StatusController::class)->except(['show']);
    });

    // Categories management — accessible to all authenticated users
    Route::resource('countries', CountryController::class)->except(['show']);

    // Country reorder + rename — available to all authenticated users
    Route::post('/countries/{country}/move', [CountryController::class, 'move']);
    Route::patch('/countries/{country}/rename', [CountryController::class, 'rename']);
    Route::post('/categories', [CountryController::class, 'quickStore']);
});
