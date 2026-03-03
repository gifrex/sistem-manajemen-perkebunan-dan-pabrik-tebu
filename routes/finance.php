<?php

use App\Http\Controllers\Finance\PembayaranUpahMingguanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('finance')->name('finance.')->group(function () {

    // ============================================================================
    // PEMBAYARAN UPAH MINGGUAN
    // ============================================================================
    Route::middleware('permission:finance.pembayaranupahmingguan.view')->group(function () {
        Route::prefix('pembayaran-upah-mingguan')->name('pembayaran-upah-mingguan.')->group(function () {
            Route::controller(PembayaranUpahMingguanController::class)->group(function () {
                Route::match(['GET', 'POST'], '/', 'index')->name('index');
                Route::get('/show/{transno}', 'show')->name('show');
                Route::get('/excel', 'exportExcel')->name('export-excel');
                Route::post('/generate', 'generate')->name('generate');
            });
        });
    });
});