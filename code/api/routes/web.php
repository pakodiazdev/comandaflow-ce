<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SwaggerAssetController;
use App\Http\Controllers\SwaggerUIController;
use App\Http\Controllers\SwaggerJsonController;

Route::get('docs/asset/{asset}', SwaggerAssetController::class)
    ->where('asset', '.*\.(png|ico|svg|css|js|json)');

Route::get('api/v1/doc', SwaggerUIController::class)->name('swagger.ui');
Route::get('api/v1/doc/docs.json', SwaggerJsonController::class)->name('swagger.json');
Route::get('api/docs.json', SwaggerJsonController::class)->name('swagger.json.legacy');

Route::get('/', function () {
    return view('welcome');
});
