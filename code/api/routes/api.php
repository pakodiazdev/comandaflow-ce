<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="ComandaFlow CE API",
 *      description="API documentation for ComandaFlow CE application - Sistema de gestión de comandas para restaurantes",
 *      @OA\Contact(
 *          email="admin@comandaflow.com",
 *          name="ComandaFlow Support"
 *      ),
 *      @OA\License(
 *          name="MIT",
 *          url="https://opensource.org/licenses/MIT"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="ComandaFlow CE API Server"
 * )
 *
 * @OA\Tag(
 *     name="Health",
 *     description="Endpoints de monitoreo y salud del sistema"
 * )
 */

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/health', [HealthController::class, 'check'])->name('api.health');