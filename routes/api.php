<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DrumPatternController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Test route
Route::get('/test', function() {
    return response()->json(['message' => 'API is working']);
});

// Drum Pattern Routes
Route::get('/drum-patterns/generate', [DrumPatternController::class, 'generate']);
Route::get('/drum-patterns/generate-file', [DrumPatternController::class, 'generateAndDownload']);
Route::get('/drum-patterns/{pattern}/download', [DrumPatternController::class, 'download'])->name('api.drum-patterns.download');
