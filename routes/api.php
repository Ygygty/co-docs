<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\FilesController;
use App\Http\Controllers\Api\V1\VersionsController;
use App\Http\Controllers\Api\V1\ScanController;

Route::prefix('v1')->group(function () {
    Route::get('files', [FilesController::class, 'index']);
    Route::get('files/{file}', [FilesController::class, 'show']);
    Route::get('files/{file}/versions', [VersionsController::class, 'index']);
    Route::get('files/{file}/versions/{version}/original', [VersionsController::class, 'downloadOriginal']);
    Route::get('files/{file}/versions/{version}/extracted-text', [VersionsController::class, 'extractedText']);

    Route::post('scan', [ScanController::class, 'start']);
    Route::get('scan-runs/{run}', [ScanController::class, 'show']);
});
