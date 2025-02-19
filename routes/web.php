<?php
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

// Fallback route for all non-matching web routes
Route::fallback(function () {
    return new JsonResponse([
        'status'  => 404,
        'message' => 'Not Found',
    ], 404);
});
