<?php

use App\Http\Controllers\Finance\PembayaranUpahMingguanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('finance')->name('finance.')->group(function () {

    // ============================================================================
    // PEMBAYARAN UPAH MINGGUAN
    // ============================================================================
    Route::prefix('pembayaran-upah-mingguan')->name('pembayaran-upah-mingguan.')->controller(PembayaranUpahMingguanController::class)->group(function () {

        Route::middleware('permission:finance.pembayaranupahmingguan.view')->group(function () {
            Route::match(['GET', 'POST'], '/', 'index')->name('index');
            Route::get('/show/{transno}', 'show')->name('show');
        });

        Route::get('/excel', 'exportExcel')
            ->middleware('permission:finance.pembayaranupahmingguan.export')
            ->name('export-excel');

        Route::post('/generate', 'generate')
            ->middleware('permission:finance.pembayaranupahmingguan.generate')
            ->name('generate');
    });
});