<?php

use App\Http\Controllers\Api\FileController;
use Illuminate\Support\Facades\Route;

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
    Route::get('latest', [FileController::class, 'latest'])
        ->name('files.latest');
});
