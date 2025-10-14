<?php

use Illuminate\Support\Facades\Route;
use App\Reminders\Http\Controllers\ItemsController;
use App\Reminders\Http\Controllers\SubtasksController;
use App\Reminders\Http\Controllers\StagesController;
use App\Reminders\Http\Controllers\TagsController;
use App\Reminders\Http\Controllers\DocumentsController;
use App\Reminders\Http\Controllers\RecurrencesController;
use App\Reminders\Http\Controllers\NotificationsController;
use App\Reminders\Http\Controllers\SharingController;
use App\Reminders\Http\Controllers\SearchController;
use App\Reminders\Http\Controllers\DashboardController;

Route::middleware(['auth:sanctum'])->prefix('reminders')->group(function () {
    // Items
    Route::apiResource('items', ItemsController::class);
    Route::post('items/{item:hash}/complete', [ItemsController::class, 'complete']);
    Route::post('items/{item:hash}/reopen', [ItemsController::class, 'reopen']);

    // Subtasks
    Route::apiResource('items/{item:hash}/subtasks', SubtasksController::class)->shallow();
    Route::post('subtasks/{subtask:hash}/complete', [SubtasksController::class, 'complete']);

    // Stages
    Route::apiResource('stages', StagesController::class);
    Route::post('items/{item:hash}/stages/{stage:hash}', [StagesController::class, 'attach']);
    Route::delete('items/{item:hash}/stages/{stage:hash}', [StagesController::class, 'detach']);

    // Tags
    Route::apiResource('tags', TagsController::class);
    Route::post('items/{item:hash}/tags/{tag:hash}', [TagsController::class, 'attach']);
    Route::delete('items/{item:hash}/tags/{tag:hash}', [TagsController::class, 'detach']);

    // Documents
    Route::post('documents/upload', [DocumentsController::class, 'upload']);
    Route::post('items/{item:hash}/documents/{document:hash}', [DocumentsController::class, 'attach']);
    Route::delete('items/{item:hash}/documents/{document:hash}', [DocumentsController::class, 'detach']);

    // Recurrence
    Route::apiResource('items/{item:hash}/recurrences', RecurrencesController::class)->only(['index','store','update','destroy'])->shallow();

    // Notifications
    Route::apiResource('items/{item:hash}/notifications', NotificationsController::class)->only(['index','store','destroy'])->shallow();

    // Sharing
    Route::apiResource('shares', SharingController::class)->only(['index','store','destroy']);

    // Search & Dashboard
    Route::get('search', [SearchController::class, 'index']);
    Route::get('dashboard', [DashboardController::class, 'index']);
});
