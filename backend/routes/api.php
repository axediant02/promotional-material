<?php

use App\Http\Controllers\Api\Admin\ActivityLogController;
use App\Http\Controllers\Api\Admin\AdminAssignmentController;
use App\Http\Controllers\Api\Admin\AdminRequestController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AgentController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Client\ClientRequestController;
use App\Http\Controllers\Api\Client\ClientRequestHistoryController;
use App\Http\Controllers\Api\Client\DashboardController;
use App\Http\Controllers\Api\Client\FileController;
use App\Http\Controllers\Api\Client\FolderController;
use App\Http\Controllers\Api\Client\RecycleBinController;
use App\Http\Controllers\Api\Production\ProductionRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('dashboard', [DashboardController::class, 'show']);

    Route::apiResource('folders', FolderController::class)->except(['destroy']);
    Route::get('recycle-bin', [RecycleBinController::class, 'index']);
    Route::post('files/{file}/restore', [RecycleBinController::class, 'restore'])->withTrashed();

    Route::get('files/{file}/download', [FileController::class, 'download'])->withTrashed();
    Route::get('files/{file}/preview', [FileController::class, 'preview'])->withTrashed();
    Route::apiResource('files', FileController::class)->except(['create', 'edit']);
    Route::get('requests', [ClientRequestHistoryController::class, 'index']);
    Route::post('requests', [ClientRequestController::class, 'store']);

    Route::prefix('production')->group(function (): void {
        Route::get('requests', [ProductionRequestController::class, 'index']);
        Route::patch('requests/{clientRequest}', [ProductionRequestController::class, 'update']);
    });

    Route::prefix('admin')->group(function (): void {
        Route::post('agents', [AgentController::class, 'store']);
        Route::get('assignments', [AdminAssignmentController::class, 'index']);
        Route::post('assignments', [AdminAssignmentController::class, 'store']);
        Route::delete('assignments/{assignment}', [AdminAssignmentController::class, 'destroy']);
        Route::get('activity-logs', [ActivityLogController::class, 'index']);
        Route::get('requests', [AdminRequestController::class, 'index']);
        Route::get('users', [AdminUserController::class, 'index']);
        Route::patch('requests/{clientRequest}', [AdminRequestController::class, 'update']);
        Route::patch('users/{user}', [AdminUserController::class, 'update']);
    });
});
