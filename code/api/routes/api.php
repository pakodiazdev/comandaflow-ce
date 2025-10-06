<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

/**
 * Main API Routes for ComandaFlow CE
 * 
 * Authentication routes are automatically loaded from the CF Auth package
 * via the AuthServiceProvider in CF\CE\Auth\AuthServiceProvider
 */

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/health', [HealthController::class, 'check'])->name('api.health');