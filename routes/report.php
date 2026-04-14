<?php

// routes/report.php

use App\Http\Controllers\Report\AbsenReportController;
use App\Http\Controllers\Report\BiayaPerPlotController;
use App\Http\Controllers\Report\MasterLahanReportController;
use App\Http\Controllers\Report\PanenTebuController;
use App\Http\Controllers\Report\RekapPremiTargetKontraktorController;
use App\Http\Controllers\Report\PanenTrackPlotReportController;
use App\Http\Controllers\Report\PivotController;
use App\Http\Controllers\Report\RekapUpahMingguanController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Report\ZpkReportController;
use App\Http\Controllers\Report\SaldoPanenReportController;
use App\Http\Controllers\Report\SuratJalanReportController;
use App\Http\Controllers\Report\SuratJalanTimbanganReportController;
use App\Http\Controllers\Report\TrackPiasReportController;
use App\Http\Controllers\Report\CheckUseController;
use App\Http\Controllers\Transaction\AgronomiController;
use App\Http\Controllers\Transaction\HPTController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('report')->name('report.')->group(function () {

    // ============================================================================
    // ABSEN
    // ============================================================================
    Route::middleware('permission:report.absen.view')->group(function () {
        Route::get('absen', [AbsenReportController::class, 'index'])->name('absen.index');
        Route::get('absen/{absenno}', [AbsenReportController::class, 'show'])->name('absen.show');
        Route::get('absen/{absenno}/gallery', [AbsenReportController::class, 'gallery'])->name('absen.gallery');
        Route::get('absen/export/excel', [AbsenReportController::class, 'exportExcel'])->name('absen.excel');
    });

    // ============================================================================
    // AGRONOMI
    // ============================================================================
    Route::middleware('permission:report.agronomi.view')->group(function () {
        Route::match(['GET', 'POST'], 'agronomi', [ReportController::class, 'agronomi'])->name('agronomi.index');
        Route::get('agronomi/excel', [AgronomiController::class, 'excel'])->name('agronomi.exportExcel');
    });

    // ============================================================================
    // HPT
    // ============================================================================
    Route::middleware('permission:report.hpt.view')->group(function () {
        Route::match(['GET', 'POST'], 'hpt', [ReportController::class, 'hpt'])->name('hpt.index');
        Route::get('hpt/excel', [HPTController::class, 'excel'])->name('hpt.exportExcel');
    });

    // ============================================================================
    // ZPK
    // ============================================================================
    Route::middleware('permission:report.zpk.view')->group(function () {
        Route::match(['GET', 'POST'], 'report-zpk', [ZpkReportController::class, 'index'])->name('report-zpk.index');
        Route::get('report-zpk/excel', [ZpkReportController::class, 'exportExcel'])->name('report-zpk.exportExcel');
    });

    // ============================================================================
    // TRASH
    // ============================================================================
    Route::middleware('permission:report.trash.view')->group(function () {
        Route::match(['GET', 'POST'], 'trash-report', [ReportController::class, 'trash'])->name('trash-report.index');
    });

    // ============================================================================
    // MANAJEMEN LAHAN
    // ============================================================================
    Route::middleware('permission:report.manajemenlahan.view')->group(function () {
        Route::get('manajemen-lahan', [MasterLahanReportController::class, 'index'])->name('report-manajemen-lahan.index');
        Route::get('manajemen-lahan/data', [MasterLahanReportController::class, 'getData'])->name('report-manajemen-lahan.data');
    });

    // ============================================================================
    // PANEN TEBU
    // ============================================================================
    Route::middleware('permission:report.panentebu.view')->group(function () {
        Route::match(['GET', 'POST'], 'panen-tebu-report', [PanenTebuController::class, 'index'])->name('panen-tebu-report.index');
        Route::post('panen-tebu-report/proses', [PanenTebuController::class, 'proses'])->name('panen-tebu-report.proses');
        Route::get('panen-tebu-report/history-data', [PanenTebuController::class, 'getHistoryData'])->name('panen-tebu-report.history-data');
        Route::get('panen-tebu-report/show/{nodoc}', [PanenTebuController::class, 'show'])->name('panen-tebu-report.show');
        Route::delete('panen-tebu-report/{nodoc}', [PanenTebuController::class, 'destroy'])->name('panen-tebu-report.destroy');
    });

    // ============================================================================
    // REKAP PREMI TARGET KONTRAKTOR
    // ============================================================================
    Route::middleware('permission:report.rekapitulasipremi.view')->group(function () {
        Route::match(['GET', 'POST'], 'rekapitulasi-premi-report', [RekapPremiTargetKontraktorController::class, 'index'])->name('rekapitulasi-premi-report.index');
        Route::post('rekapitulasi-premi-report/search', [RekapPremiTargetKontraktorController::class, 'search'])->name('rekapitulasi-premi-report.search');
        Route::post('rekapitulasi-premi-report/proses', [RekapPremiTargetKontraktorController::class, 'proses'])->name('rekapitulasi-premi-report.proses');
        Route::get('rekapitulasi-premi-report/{nodoc}', [RekapPremiTargetKontraktorController::class, 'show'])->name('rekapitulasi-premi-report.show');
        Route::delete('rekapitulasi-premi-report/{nodoc}', [RekapPremiTargetKontraktorController::class, 'destroy'])->name('rekapitulasi-premi-report.destroy');
    });

    // ============================================================================
    // SURAT JALAN (tanpa timbangan)
    // ============================================================================
    Route::middleware('permission:report.suratjalan.view')->group(function () {
        Route::get('surat-jalan', [SuratJalanReportController::class, 'index'])->name('report-surat-jalan.index');
        Route::get('surat-jalan/data', [SuratJalanReportController::class, 'getData'])->name('report-surat-jalan.data');
    });

    // ============================================================================
    // SURAT JALAN & TIMBANGAN
    // ============================================================================
    Route::middleware('permission:report.suratjalantimbangan.view')->group(function () {
        Route::get('surat-jalan-timbangan', [SuratJalanTimbanganReportController::class, 'index'])->name('report-surat-jalan-timbangan.index');
        Route::get('surat-jalan-timbangan/data', [SuratJalanTimbanganReportController::class, 'getData'])->name('report-surat-jalan-timbangan.data');
        Route::get('surat-jalan-timbangan/{suratjalanno}', [SuratJalanTimbanganReportController::class, 'show'])->name('report-surat-jalan-timbangan.show');
        Route::get('surat-jalan-timbangan/{suratjalanno}/detail', [SuratJalanTimbanganReportController::class, 'getDetail'])->name('report-surat-jalan-timbangan.detail');
    });

    // ============================================================================
    // PANEN TRACK PLOT
    // ============================================================================
    Route::middleware('permission:report.panentrackplot.view')->group(function () {
        Route::get('panen-track-plot', [PanenTrackPlotReportController::class, 'index'])->name('panen-track-plot.index');
        Route::get('panen-track-plot/batches', [PanenTrackPlotReportController::class, 'getBatches'])->name('panen-track-plot.batches');
        Route::get('panen-track-plot/data', [PanenTrackPlotReportController::class, 'getData'])->name('panen-track-plot.data');
    });

    // ============================================================================
    // SALDO PANEN
    // ============================================================================
    Route::middleware('permission:report.saldopanen.view')->group(function () {
        Route::get('saldo-panen', [SaldoPanenReportController::class, 'index'])->name('saldo-panen.index');
        Route::get('saldo-panen/data', [SaldoPanenReportController::class, 'getData'])->name('saldo-panen.data');
    });

    // ============================================================================
    // REKAP UPAH MINGGUAN
    // ============================================================================
    Route::prefix('rekap-upah-mingguan')->name('rekap-upah-mingguan.')->controller(RekapUpahMingguanController::class)->group(function () {

        Route::middleware('permission:report.rekapupahminggu.view')->group(function () {
            Route::match(['GET', 'POST'], '/', 'index')->name('index');
            Route::get('/show/{lkhno}', 'show')->name('show');
        });

        Route::match(['GET', 'POST'], '/preview', 'previewReport')
            ->middleware('permission:report.rekapupahminggu.preview')
            ->name('preview');

        Route::get('/export-excel', 'exportExcel')
            ->middleware('permission:report.rekapupahminggu.export')
            ->name('export-excel');

        Route::get('/print-bp', 'printBp')
            ->middleware('permission:report.rekapupahminggu.print')
            ->name('print-bp');
    });

    // ============================================================================
    // PIVOT TABLES
    // ============================================================================
    Route::middleware('permission:dashboard.agronomi.pivot')->group(function () {
        Route::get('agronomipivot', [PivotController::class, 'pivotTableAgronomi'])->name('pivotTableAgronomi');
    });
    Route::middleware('permission:dashboard.hpt.pivot')->group(function () {
        Route::get('hptpivot', [PivotController::class, 'pivotTableHPT'])->name('pivotTableHPT');
    });

    // ============================================================================
    // TRACK PIAS
    // ============================================================================
    Route::middleware('permission:report.track-pias.view')->group(function () {
        Route::get('track-pias', [TrackPiasReportController::class, 'index'])->name('track-pias.index');
        Route::post('track-pias/data', [TrackPiasReportController::class, 'getData'])->name('track-pias.data');
    });

    // ============================================================================
    // CEK SINKRONISASI NO USE
    // ============================================================================
    Route::get('check-use', [CheckUseController::class, 'index'])->name('check-use.index');
    Route::post('check-use/check', [CheckUseController::class, 'check'])->name('check-use.check');

    // ============================================================================
    // BIAYA PER PLOT
    // ============================================================================
    Route::middleware('permission:report.biayaperplot.view')->group(function () {
        Route::get('biaya-per-plot', [BiayaPerPlotController::class, 'index'])->name('biaya-per-plot.index');
        Route::post('biaya-per-plot/data', [BiayaPerPlotController::class, 'getData'])->name('biaya-per-plot.data');
        Route::get('biaya-per-plot/{batchno}', [BiayaPerPlotController::class, 'show'])->name('biaya-per-plot.show');
        Route::get('biaya-per-plot/{batchno}/detail', [BiayaPerPlotController::class, 'getDetail'])->name('biaya-per-plot.detail');

        // NEW: Cycle comparison for chart
        Route::get('biaya-per-plot/{batchno}/cycle-comparison', [BiayaPerPlotController::class, 'getCycleComparison'])->name('biaya-per-plot.cycle-comparison');

        // Export routes
        Route::post('biaya-per-plot/export-excel', [BiayaPerPlotController::class, 'exportExcel'])->name('biaya-per-plot.export-excel');
        Route::post('biaya-per-plot/export-pdf', [BiayaPerPlotController::class, 'exportPdf'])->name('biaya-per-plot.export-pdf');
    });
});