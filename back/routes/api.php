<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Category\CategoryController;
use App\Http\Controllers\Api\Horse\HorseController;
use App\Http\Controllers\Api\Setting\SettingController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');

Route::get('categories', [CategoryController::class, 'index']);
Route::get('horses', [HorseController::class, 'index']);
Route::get('horses/{horse}', [HorseController::class, 'show']);
Route::get('settings', [SettingController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::post('horses', [HorseController::class, 'store']);
    // POST, not PUT: PHP does not parse multipart/form-data on PUT, and this accepts image uploads.
    Route::post('horses/{horse}', [HorseController::class, 'update']);
    Route::delete('horses/{horse}', [HorseController::class, 'destroy']);
    Route::delete('horses/{horse}/images/{mediaId}', [HorseController::class, 'deleteImage']);
    Route::delete('horses/{horse}/video', [HorseController::class, 'deleteVideo']);

    Route::put('settings', [SettingController::class, 'update']);
});
