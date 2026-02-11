<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ConversionController;

// Test route to verify API is working
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'laravel_version' => app()->version(),
        'timestamp' => now()
    ]);
});

// Get authenticated user (default Laravel API route)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Currency routes
Route::prefix('currencies')->group(function () {
    Route::get('/', [CurrencyController::class, 'index']);
    Route::post('/sync', [CurrencyController::class, 'sync']);
    Route::get('/rate', [CurrencyController::class, 'getRate']);
    Route::get('/cache-stats', [CurrencyController::class, 'cacheStats']);
    Route::post('/clear-cache', [CurrencyController::class, 'clearCache']);
});

// Conversion routes
Route::post('/convert', [ConversionController::class, 'convert']);
Route::get('/conversions/history', [ConversionController::class, 'history']);

// Favorite pairs routes
Route::prefix('favorites')->group(function () {
    Route::get('/', [ConversionController::class, 'getFavorites']);
    Route::post('/', [ConversionController::class, 'addFavorite']);
    Route::delete('/{id}', [ConversionController::class, 'removeFavorite']);
});