<?php

// routes\pabrik.php

use App\Http\Controllers\Pabrik\TrashController;
use App\Http\Controllers\Pabrik\DashboardPanenPabrikController;
use App\Http\Controllers\Pabrik\SuratTeguranController;

Route::middleware('auth')->prefix('pabrik')->name('pabrik.')->group(function () {

    // ============================================================================
    // TRASH
    // ============================================================================
    Route::middleware('permission:pabrik.trash.view')->group(function () {
        Route::get('trash', [TrashController::class, 'index'])->name('trash.index');


        Route::get('trash/surat-jalan/check', [TrashController::class, 'checkSuratJalan'])->name('trash.surat-jalan.check');
        Route::post('trash/report', [TrashController::class, 'generateReport'])->name('trash.report');
        Route::any('trash/report/preview', [TrashController::class, 'reportPreview'])->name('trash.report.preview');
        Route::get('trash/surat-jalan/search-by-date', [TrashController::class, 'searchSuratJalanByDate'])->name('trash.surat-jalan.search-by-date');
    });

    Route::middleware('permission:pabrik.trash.create')->group(function () {
        Route::post('trash', [TrashController::class, 'store'])->name('trash.store');
    });

    Route::middleware('permission:pabrik.trash.edit')->group(function () {
        Route::post('trash/update/{suratjalanno}/{companycode}/{jenis}', [TrashController::class, 'update'])
            ->where('suratjalanno', '.*')
            ->where('companycode', '.*')
            ->where('jenis', '.*')
            ->name('trash.update');
    });

    Route::middleware('permission:pabrik.trash.edit')->group(function () {
        Route::post('trash/delete/{suratjalanno}/{companycode}/{jenis}', [TrashController::class, 'destroy'])
            ->where('suratjalanno', '.*')
            ->where('companycode', '.*')
            ->where('jenis', '.*')
            ->name('trash.destroy');
    });

    // ============================================================================
    // SURAT TEGURAN
    // ============================================================================
    Route::middleware('permission:pabrik.suratteguran.view')->group(function () {
        Route::get('surat-teguran', [SuratTeguranController::class, 'index'])->name('surat-teguran.index');
        Route::get('surat-teguran/create', [SuratTeguranController::class, 'create'])->name('surat-teguran.create');
        Route::post('surat-teguran', [SuratTeguranController::class, 'store'])->name('surat-teguran.store');
        Route::get('surat-teguran/{id}', [SuratTeguranController::class, 'show'])->name('surat-teguran.show');
        Route::get('surat-teguran/{id}/edit', [SuratTeguranController::class, 'edit'])->name('surat-teguran.edit');
        Route::post('surat-teguran/{id}', [SuratTeguranController::class, 'update'])->name('surat-teguran.update');
        Route::post('surat-teguran/{id}/send', [SuratTeguranController::class, 'send'])->name('surat-teguran.send');
        Route::delete('surat-teguran/{id}', [SuratTeguranController::class, 'destroy'])->name('surat-teguran.destroy');
    });

    // ============================================================================
    // PANEN PABRIK DASHBOARD
    // ============================================================================
    Route::middleware('permission:pabrik.panenpabrik.view')->group(function () {
        Route::get('panen-pabrik', [DashboardPanenPabrikController::class, 'index'])->name('panen-pabrik.index');
    });
});
