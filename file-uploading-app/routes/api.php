<?php

use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FileCollaboratorController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Public routes
Route::controller(AuthController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
});

Route::prefix('files')->group(function () {
    Route::get('latest', [FileController::class, 'latest'])
        ->name('files.latest');
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);


    Route::get('files/{file}/collaborators', [FileCollaboratorController::class, 'index'])
        ->name('files.collaborators.index');

    Route::post('files/{file}/collaborators', [FileCollaboratorController::class, 'store'])
        ->name('files.collaborators.store');

    Route::put('files/{file}/collaborators/{user}', [FileCollaboratorController::class, 'update'])
        ->name('files.collaborators.update');

    Route::delete('files/{file}/collaborators/{user}', [FileCollaboratorController::class, 'destroy'])
        ->name('files.collaborators.destroy');

    Route::prefix('files')->group(function () {
        Route::post('upload', [FileController::class, 'upload'])
            ->name('files.upload');
        Route::post('upload/multiple', [FileController::class, 'uploadMultiple'])
            ->name('files.upload.multiple');
        Route::post('{fileId}/versions', [FileController::class, 'addVersion'])
            ->name('files.versions.add');
        Route::get('{fileId}/versions', [FileController::class, 'versions'])
            ->name('files.versions.list');
        Route::post('versions/{versionId}/comments', [FileController::class, 'addComment'])
            ->name('files.versions.comments.add');
        Route::get('versions/{versionId}/download', [FileController::class, 'download'])
            ->name('files.versions.download');
    });
});
