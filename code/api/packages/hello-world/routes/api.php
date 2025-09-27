<?php

use Illuminate\Support\Facades\Route;
use CF\HelloWorld\Http\Controllers\HelloWorldController;

Route::get('/hello', HelloWorldController::class)->name('hello-world.hello');