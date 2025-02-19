<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Posts\PostController;
use App\Http\Controllers\Api\Weather\WeatherController;
use Illuminate\Support\Facades\Route;

// Public Route
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Weather Route
Route::middleware(['throttle:weather'])->group(function () {
    Route::get('/weather', [WeatherController::class, 'getWeather']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('posts', PostController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/user/{id}', [AuthController::class, 'show']);
});
