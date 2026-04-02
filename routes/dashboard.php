<?php

// routes\dashboard.php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\TimelineController;
use App\Http\Controllers\Dashboard\MapsController;
use App\Http\Controllers\Dashboard\DashboardPanenController;
use App\Http\Controllers\Pabrik\DashboardPanenPabrikController;


Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {

    // ============================================================================
    // AGRONOMI DASHBOARD
    // ============================================================================
    Route::middleware('permission:dashboard.agronomi.view')->group(function () {
        Route::match(['GET', 'POST'], 'agronomi', [DashboardController::class, 'agronomi'])->name('agronomi');
    });

    // ============================================================================
    // HPT DASHBOARD
    // ============================================================================
    Route::middleware('permission:dashboard.hpt.view')->group(function () {
        Route::match(['GET', 'POST'], 'hpt', [DashboardController::class, 'hpt'])->name('hpt');
    });

    // ============================================================================
    // TIMELINE DASHBOARD
    // ============================================================================
    Route::middleware('permission:dashboard.timeline.view')->group(function () {
        Route::match(['GET', 'POST'], 'timeline', [TimelineController::class, 'index'])->name('timeline');
    });

    Route::middleware('permission:dashboard.timelineplot.view')->group(function () {
        Route::match(['GET', 'POST'], 'timeline-plot', [TimelineController::class, 'plot'])->name('timeline-plot');
        Route::get('timeline-plot/detail', [DashboardController::class, 'plotDetail'])
        ->name('timeline-plot.detail');
    });

    // ============================================================================
    // MAPS DASHBOARD
    // ============================================================================
    Route::middleware('permission:dashboard.maps.view')->group(function () {
        Route::match(['GET', 'POST'], 'maps', [MapsController::class, 'index'])->name('maps');
        Route::match(['GET', 'POST'], 'mapsapi', [MapsController::class, 'indexapi'])->name('mapsapi');
        Route::match(['GET', 'POST'], 'callmapsapi', [MapsController::class, 'callmapsapi'])->name('callmapsapi');
        Route::match(['GET', 'POST'], 'maps/upload', [MapsController::class, 'upload'])->name('maps.upload');
    });

    // ============================================================================
    // PANEN DASHBOARD
    // ============================================================================
    Route::middleware('permission:dashboard.panen.view')->group(function () {
        Route::get('panen', [DashboardPanenController::class, 'index'])->name('panen');
        Route::get('panen/data', [DashboardPanenController::class, 'getData'])->name('panen.data');
    });

});

// ============================================================================
// PABRIK - DASHBOARD PANEN PABRIK (Outside dashboard prefix)
// ============================================================================
Route::middleware(['auth', 'permission:pabrik.panenpabrik.view'])->group(function () {
    Route::get('pabrik/panen-pabrik', [DashboardPanenPabrikController::class, 'index'])->name('pabrik.panen-pabrik.index');
    Route::get('pabrik/panen-pabrik/data', [DashboardPanenPabrikController::class, 'getData'])->name('pabrik.panen-pabrik.data');
});

