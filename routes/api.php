<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\LocationController;

// SuperAdmin only
Route::middleware(['auth:sanctum', 'role:SuperAdmin'])->group(function () {
    Route::apiResource('applications', ApplicationController::class);
});

// SuperAdmin or LocationAdmin
Route::middleware(['auth:sanctum', 'role:SuperAdmin|LocationAdmin'])->group(function () {
    Route::apiResource('locations', LocationController::class);
});
