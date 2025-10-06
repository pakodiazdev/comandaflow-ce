<?php

use CF\CE\Auth\Http\Controllers\AuthController;
use CF\CE\Auth\Http\Controllers\RoleController;
use CF\CE\Auth\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CF Auth API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the CF Auth package.
| These routes provide authentication, user management, and role management.
|
*/

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// Protected routes
Route::middleware(['auth:api'])->group(function () {
    
    // Authentication routes (protected)
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // User management routes (Owner/Manager only)
    Route::prefix('users')->middleware('cf-role:owner,manager')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('{id}', [UserController::class, 'show']);
        Route::put('{id}', [UserController::class, 'update']);
        Route::delete('{id}', [UserController::class, 'destroy']);
        Route::post('{id}/roles', [UserController::class, 'assignRoles']);
    });

    // Role management routes (Owner only)
    Route::prefix('roles')->middleware('cf-role:owner')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::get('{code}', [RoleController::class, 'show']);
        Route::post('/', [RoleController::class, 'store']);
        Route::put('{code}', [RoleController::class, 'update']);
        Route::delete('{code}', [RoleController::class, 'destroy']);
    });

    // Permission routes
    Route::get('permissions', [RoleController::class, 'permissions']);
});
