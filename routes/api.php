<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserController;


Route::middleware('auth:sanctum')->group(function () {
    // SuperAdmin only
    Route::middleware('role:SuperAdmin')->group(function () {
        Route::apiResource('applications', ApplicationController::class);
        Route::post('/users/{user}/addLocationAdmin/{location}', [UserController::class, 'assignLocationAdmin']);
    });

    // SuperAdmin or LocationAdmin
    Route::middleware('role:SuperAdmin|LocationAdmin|UserAdmin')->group(function () {
        Route::apiResource('locations', LocationController::class);

    });

});




