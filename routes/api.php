<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApplicationController;


Route::middleware(['auth:sanctum'])->group(function () {

    Route::apiResource('applications', ApplicationController::class);
});
