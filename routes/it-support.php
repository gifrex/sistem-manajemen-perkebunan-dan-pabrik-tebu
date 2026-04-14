<?php

use App\Http\Controllers\ItSupport\DeleteRkhController;
use App\Http\Controllers\ItSupport\ChangeDateRkhController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('it-support')->name('it-support.')->group(function () {

    // ============================================================================
    // DELETE RKH - ALL ACTIONS IN ONE PERMISSION GROUP
    // ============================================================================
    Route::middleware('permission:it-support.delete-rkh.view')->group(function () {
        Route::get('delete-rkh', [DeleteRkhController::class, 'index'])->name('delete-rkh.index');
        Route::post('delete-rkh/search', [DeleteRkhController::class, 'search'])->name('delete-rkh.search');
        Route::delete('delete-rkh/{rkhno}', [DeleteRkhController::class, 'destroy'])->name('delete-rkh.destroy');
    });

    // ============================================================================
    // CHANGE DATE RKH - ALL ACTIONS IN ONE PERMISSION GROUP
    // ============================================================================
    Route::middleware('permission:it-support.change-date-rkh.view')->group(function () {
        Route::get('change-date-rkh', [ChangeDateRkhController::class, 'index'])->name('change-date-rkh.index');
        Route::post('change-date-rkh/search', [ChangeDateRkhController::class, 'search'])->name('change-date-rkh.search');
        Route::put('change-date-rkh/{rkhno}', [ChangeDateRkhController::class, 'update'])->name('change-date-rkh.update');
    });

});