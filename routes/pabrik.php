<?php

// routes\pabrik.php

use App\Http\Controllers\Pabrik\TrashController;
use App\Http\Controllers\Pabrik\DashboardPanenPabrikController;

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
        Route::get('trash/surat-jalan/search-by-nopol', [TrashController::class, 'searchSuratJalanByNopol'])->name('trash.surat-jalan.search-by-nopol');
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
    // PANEN PABRIK DASHBOARD
    // ============================================================================
    Route::middleware('permission:pabrik.panenpabrik.view')->group(function () {
        Route::get('panen-pabrik', [DashboardPanenPabrikController::class, 'index'])->name('panen-pabrik.index');
    });
});
