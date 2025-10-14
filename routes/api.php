<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\UserController;
use App\Enums\Role;


Route::middleware('auth:sanctum')->group(function () {
    // SuperAdmin only
    Route::middleware('role:'.Role::SuperAdmin->value)->group(function () {
        Route::apiResource('applications', ApplicationController::class);
        Route::post('/users/{user}/addLocationAdmin/{location}', [UserController::class, 'assignLocationAdmin']);
    });

    // SuperAdmin or LocationAdmin
    Route::middleware('role:'.implode('|', [Role::SuperAdmin->value, Role::LocationAdmin->value, Role::UserAdmin->value]))->group(function () {
        Route::apiResource('locations', LocationController::class);

    });

});

// Mount Reminders module routes
require __DIR__ . '/reminders.php';
