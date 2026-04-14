<?php

// routes\process.php

use App\Http\Controllers\Process\GPXController;
use App\Http\Controllers\Process\PostController;
use App\Http\Controllers\Process\UnpostController;
use App\Http\Controllers\Process\ClosingController;

Route::middleware('auth')->prefix('process')->name('process.')->group(function () {

    // ============================================================================
    // POSTING
    // ============================================================================
    Route::middleware('permission:process.posting.view')->group(function () {
        Route::match(['GET', 'POST'], 'posting', [PostController::class, 'index'])->name('posting');
        Route::post('posting/submit', [PostController::class, 'posting'])->name('posting.submit');
    });

    // ============================================================================
    // UNPOSTING
    // ============================================================================
    Route::middleware('permission:process.unposting.view')->group(function () {
        Route::match(['GET', 'POST'], 'unposting', [UnpostController::class, 'index'])->name('unposting');
        Route::post('unposting/submit', [UnpostController::class, 'unposting'])->name('unposting.submit');
    });

    // ============================================================================
    // CLOSING
    // ============================================================================
    Route::middleware('permission:process.closing.view')->group(function () {
        Route::get('closing', [ClosingController::class, 'closing'])->name('closing');
    });

    // ============================================================================
    // GPX/KML FILE MANAGEMENT
    // ============================================================================
    Route::middleware('permission:process.uploadgpx.view')->group(function () {
        Route::post('/upload-gpx', [GPXController::class, 'upload'])->name('uploadgpx.submit');
        Route::get('/uploadgpx', [GPXController::class, 'indexUpload'])->name('uploadgpx');
    });

    Route::middleware('permission:process.exportkml.view')->group(function () {
        Route::post('/export-kml', [GPXController::class, 'export'])->name('exportkml.submit');
        Route::get('/exportkml', [GPXController::class, 'indexExport'])->name('exportkml');
    });

});