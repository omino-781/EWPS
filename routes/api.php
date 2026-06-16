<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\ReportApiController;
use App\Http\Controllers\Api\VenueApiController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/events', [EventApiController::class, 'index']);
    Route::post('/events', [EventApiController::class, 'store']);
    Route::get('/events/{event}', [EventApiController::class, 'show']);
    Route::put('/events/{event}', [EventApiController::class, 'update']);
    Route::delete('/events/{event}', [EventApiController::class, 'destroy']);
    Route::post('/events/{event}/register', [EventApiController::class, 'register']);
    Route::patch('/events/{event}/attendance/confirm', [EventApiController::class, 'confirmAttendance'])->middleware('role:participant');

    Route::get('/venues', [VenueApiController::class, 'index']);
    Route::post('/venues', [VenueApiController::class, 'store'])->middleware('role:administrator,organizer');

    Route::post('/payments', [PaymentApiController::class, 'store'])->middleware('role:administrator,organizer');

    Route::get('/reports', [ReportApiController::class, 'index'])->middleware('role:administrator,organizer');
    Route::post('/reports', [ReportApiController::class, 'generate'])->middleware('role:administrator,organizer');
});
