<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes with rate limiting
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Student resource routes
    Route::apiResource('students', StudentController::class);
    
    // Attendance routes
    Route::prefix('attendance')->group(function () {
        Route::post('/bulk', [AttendanceController::class, 'bulkStore']);
        Route::get('/monthly-report', [AttendanceController::class, 'monthlyReport']);
        Route::get('/today', [AttendanceController::class, 'todayStatistics']);
    });
});
