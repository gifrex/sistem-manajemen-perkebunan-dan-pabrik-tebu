<?php

use App\Http\Controllers\Settings\HargaPanenController;
use App\Http\Controllers\Settings\TargetKontraktorController;
use Illuminate\Support\Facades\Route;

    Route::middleware('permission:settings.menu.view')->group(function () {
    Route::get('hargapanen', [HargaPanenController::class, 'index'])->name('settings.harga-panen.index');
    Route::post('hargapanen/store', [HargaPanenController::class, 'store'])->name('settings.harga-panen.store');
    Route::put('hargapanen/update/{id}', [HargaPanenController::class, 'update'])->name('settings.harga-panen.update');
    Route::delete('hargapanen/destroy/{id}', [HargaPanenController::class, 'destroy'])->name('settings.harga-panen.destroy');

    Route::get('target-kontraktor', [TargetKontraktorController::class, 'index'])->name('settings.target-kontraktor.index');
    Route::post('target-kontraktor/store', [TargetKontraktorController::class, 'store'])->name('settings.target-kontraktor.store');
    Route::put('target-kontraktor/update/{id}', [TargetKontraktorController::class, 'update'])->name('settings.target-kontraktor.update');
    Route::delete('target-kontraktor/destroy/{id}', [TargetKontraktorController::class, 'destroy'])->name('settings.target-kontraktor.destroy');

});