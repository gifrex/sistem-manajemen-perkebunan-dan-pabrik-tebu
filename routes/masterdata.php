<?php

// routes\masterdata.php
 
use App\Http\Controllers\MasterData\ActivityController;
use App\Http\Controllers\MasterData\BlokController;
use App\Http\Controllers\MasterData\BatchController;
use App\Http\Controllers\MasterData\CompanyController;
use App\Http\Controllers\MasterData\MasterListController;
use App\Http\Controllers\MasterData\CostcenterController;
use App\Http\Controllers\MasterData\HerbisidaController;
use App\Http\Controllers\MasterData\HerbisidaGroupController;
use App\Http\Controllers\MasterData\HerbisidaDosageController;
use App\Http\Controllers\MasterData\ApprovalController;
use App\Http\Controllers\MasterData\VarietasController;
use App\Http\Controllers\MasterData\AccountingController;
use App\Http\Controllers\MasterData\MandorController;
use App\Http\Controllers\MasterData\TenagaKerjaController;
use App\Http\Controllers\MasterData\UpahController;
use App\Http\Controllers\MasterData\UpahBoronganController;
use App\Http\Controllers\MasterData\KendaraanController;
use App\Http\Controllers\MasterData\KontraktorController;
use App\Http\Controllers\MasterData\SubkontraktorController;
use App\Http\Controllers\MasterData\SplitMergePlotController;
use App\Http\Controllers\MasterData\OpenReworkController;

Route::middleware('auth')->prefix('masterdata')->name('masterdata.')->group(function () {

    // ============================================================================
    // COMPANY
    // ============================================================================
    Route::middleware('permission:masterdata.company.view')->group(function () {
        Route::get('company', [CompanyController::class, 'index'])->name('company.index');
        Route::post('company', [CompanyController::class, 'handle'])->name('company.handle');
    });

    Route::middleware('permission:masterdata.company.edit')->group(function () {
        Route::put('company/{companycode}', [CompanyController::class, 'update'])->name('company.update');
    });

    Route::middleware('permission:masterdata.company.delete')->group(function () {
        Route::delete('company/{companycode}', [CompanyController::class, 'destroy'])->name('company.destroy');
    });

    // ============================================================================
    // BLOK
    // ============================================================================
    Route::middleware('permission:masterdata.blok.view')->group(function () {
        Route::get('blok', [BlokController::class, 'index'])->name('blok.index');
        Route::post('blok', [BlokController::class, 'handle'])->name('blok.handle');
    });

    Route::middleware('permission:masterdata.blok.edit')->group(function () {
        Route::put('blok/{blok}/{companycode}', [BlokController::class, 'update'])->name('blok.update');
    });

    Route::middleware('permission:masterdata.blok.delete')->group(function () {
        Route::delete('blok/{blok}/{companycode}', [BlokController::class, 'destroy'])->name('blok.destroy');
    });

    // ============================================================================
    // HERBISIDA
    // ============================================================================
    Route::middleware('permission:masterdata.herbisida.view')->group(function () {
        Route::get('herbisida', [HerbisidaController::class, 'index'])->name('herbisida.index');
        Route::post('herbisida', [HerbisidaController::class, 'store'])->name('herbisida.store');
        Route::get('herbisida/group', [HerbisidaController::class, 'group'])->name('herbisida.group');
        Route::get('herbisida/items', [HerbisidaController::class, 'items'])->name('herbisida.items');
    });

    Route::middleware('permission:masterdata.herbisida.edit')->group(function () {
        Route::match(['put', 'patch'], 'herbisida/{companycode}/{itemcode}', [HerbisidaController::class, 'update'])->name('herbisida.update');
    });

    Route::middleware('permission:masterdata.herbisida.delete')->group(function () {
        Route::delete('herbisida/{companycode}/{itemcode}', [HerbisidaController::class, 'destroy'])->name('herbisida.destroy');
    });

    // ============================================================================
    // HERBISIDA GROUP
    // ============================================================================
    Route::middleware('permission:masterdata.herbisidagroup.view')->group(function () {
        Route::get('herbisida-group', [HerbisidaGroupController::class, 'home'])->name('herbisida-group.index');
        Route::post('herbisida-group', [HerbisidaGroupController::class, 'insert']);
        Route::patch('herbisida-group/{id}', [HerbisidaGroupController::class, 'edit']);
        Route::delete('herbisida-group/{id}', [HerbisidaGroupController::class, 'delete']);
    });

    // ============================================================================
    // COSTCENTER
    // ============================================================================
    Route::middleware('permission:masterdata.costcenter.create')->group(function () {
        Route::get('costcenter', [CostCenterController::class, 'index'])->name('costcenter.index');
        Route::patch('costcenter/{herbisidagroupid}', [CostCenterController::class, 'update'])->name('costcenter.update');
        Route::delete('costcenter/{herbisidagroupid}', [CostCenterController::class, 'destroy'])->name('costcenter.destroy');
    });

    // ============================================================================
    // HERBISIDA DOSAGE
    // ============================================================================
    Route::middleware('permission:masterdata.herbisidadosage.view')->group(function () {
        Route::get('herbisida-dosage', [HerbisidaDosageController::class, 'index'])->name('herbisida-dosage.index');
        Route::post('herbisida-dosage', [HerbisidaDosageController::class, 'store'])->name('herbisida-dosage.store');
    });

    Route::middleware('permission:masterdata.herbisidadosage.edit')->group(function () {
        Route::match(['put', 'patch'], 'herbisida-dosage/{herbisidagroupid}/{itemcode}', [HerbisidaDosageController::class, 'update'])->name('herbisida-dosage.update');
    });

    Route::middleware('permission:masterdata.herbisidadosage.delete')->group(function () {
        Route::delete('herbisida-dosage/{herbisidagroupid}/{itemcode}', [HerbisidaDosageController::class, 'destroy'])->name('herbisida-dosage.destroy');
    });

    //

    //

    // ============================================================================
    // AKTIVITAS
    // ============================================================================
    Route::middleware('permission:masterdata.aktivitas.view')->group(function () {
        Route::get('aktivitas', [ActivityController::class, 'index'])->name('aktivitas.index');
        Route::post('aktivitas', [ActivityController::class, 'store'])->name('aktivitas.store');
        Route::put('aktivitas/{aktivitas}', [ActivityController::class, 'update'])->name('aktivitas.update');
        Route::delete('aktivitas/{aktivitas}', [ActivityController::class, 'destroy'])->name('aktivitas.destroy');
    });

    // ============================================================================
    // APPROVAL
    // ============================================================================
    Route::middleware('permission:masterdata.approval.view')->group(function () {
        Route::get('approval', [ApprovalController::class, 'index'])->name('approval.index');
        Route::post('approval', [ApprovalController::class, 'store'])->name('approval.store');
    });

    Route::middleware('permission:masterdata.approval.edit')->group(function () {
        Route::match(['put', 'patch'], 'approval/{companycode}/{category}', [ApprovalController::class, 'update'])->name('approval.update');
    });

    Route::middleware('permission:masterdata.approval.delete')->group(function () {
        Route::delete('approval/{companycode}/{category}', [ApprovalController::class, 'destroy'])->name('approval.destroy');
    });

    // ============================================================================
    // VARIETAS
    // ============================================================================
    Route::middleware('permission:masterdata.varietas.view')->group(function () {
        Route::get('varietas', [VarietasController::class, 'index'])->name('varietas.index');
        Route::post('varietas', [VarietasController::class, 'store'])->name('varietas.store');
    });

    Route::middleware('permission:masterdata.varietas.edit')->group(function () {
        Route::match(['put', 'patch'], 'varietas/{kodevarietas}', [VarietasController::class, 'update'])->name('varietas.update');
    });

    Route::middleware('permission:masterdata.varietas.delete')->group(function () {
        Route::delete('varietas/{kodevarietas}', [VarietasController::class, 'destroy'])->name('varietas.destroy');
    });

    // ============================================================================
    // ACCOUNTING
    // ============================================================================
    Route::middleware('permission:masterdata.accounting.view')->group(function () {
        Route::get('accounting', [AccountingController::class, 'index'])->name('accounting.index');
        Route::post('accounting', [AccountingController::class, 'store'])->name('accounting.store');
    });

    Route::middleware('permission:masterdata.accounting.edit')->group(function () {
        Route::match(['put', 'patch'], 'accounting/{activitycode}/{jurnalaccno}/{jurnalacctype}', [AccountingController::class, 'update'])->name('accounting.update');
    });

    Route::middleware('permission:masterdata.accounting.delete')->group(function () {
        Route::delete('accounting/{activitycode}/{jurnalaccno}/{jurnalacctype}', [AccountingController::class, 'destroy'])->name('accounting.destroy');
    });

    // ============================================================================
    // MASTER LIST
    // ============================================================================
    Route::middleware('permission:masterdata.masterlist.view')->group(function () {
        Route::get('master-list', [MasterListController::class, 'index'])->name('master-list.index');
        Route::post('master-list', [MasterListController::class, 'store'])->name('master-list.store');
    });

    Route::middleware('permission:masterdata.masterlist.edit')->group(function () {
        Route::match(['put', 'patch'], 'master-list/{companycode}/{plot}', [MasterListController::class, 'update'])->name('master-list.update');
    });

    Route::middleware('permission:masterdata.masterlist.delete')->group(function () {
        Route::delete('master-list/{companycode}/{plot}', [MasterListController::class, 'destroy'])->name('master-list.destroy');
    });

    // ============================================================================
    // BATCH
    // ============================================================================
    Route::middleware('permission:masterdata.batch.view')->group(function () {
        Route::get('batch', [BatchController::class, 'index'])->name('batch.index');
        Route::post('batch', [BatchController::class, 'store'])->name('batch.store');
    });

    Route::middleware('permission:masterdata.batch.edit')->group(function () {
        Route::match(['put', 'patch'], 'batch/{batchno}', [BatchController::class, 'update'])->name('batch.update');
    });

    Route::middleware('permission:masterdata.batch.delete')->group(function () {
        Route::delete('batch/{batchno}', [BatchController::class, 'destroy'])->name('batch.destroy');
    });

    // ============================================================================
    // SPLIT MERGE PLOT
    // ============================================================================
    Route::middleware('permission:masterdata.splitmergeplot.view')->group(function () {
        Route::get('split-merge-plot', [SplitMergePlotController::class, 'index'])->name('split-merge-plot.index');
        Route::get('split-merge-plot/approval/{approvalno}', [SplitMergePlotController::class, 'getApprovalDetail'])->name('split-merge-plot.getApprovalDetail');
        Route::get('split-merge-plot/batch/{batchno}', [SplitMergePlotController::class, 'getBatchDetails'])->name('split-merge-plot.batch-details');
        Route::get('split-merge-plot/check-plot', [SplitMergePlotController::class, 'checkPlotExists'])->name('split-merge-plot.check-plot');
        Route::post('split-merge-plot/split', [SplitMergePlotController::class, 'split'])->name('split-merge-plot.split');
        Route::post('split-merge-plot/merge', [SplitMergePlotController::class, 'merge'])->name('split-merge-plot.merge');
        Route::delete('split-merge-plot/{transactionNumber}', [SplitMergePlotController::class, 'destroy'])->name('split-merge-plot.destroy');
    });

    Route::middleware('permission:masterdata.open-rework.view')->group(function () {
        Route::get('open-rework', [OpenReworkController::class, 'index'])->name('open-rework.index');
        Route::post('open-rework/get-lkh-list', [OpenReworkController::class, 'getLkhList'])->name('open-rework.get-lkh-list');
        Route::get('open-rework/lkh-detail/{lkhno}', [OpenReworkController::class, 'getLkhDetailPlots'])->name('open-rework.lkh-detail');
        Route::get('open-rework/approval/{approvalno}', [OpenReworkController::class, 'getApprovalDetail'])->name('open-rework.approval-detail');
        Route::post('open-rework', [OpenReworkController::class, 'store'])->name('open-rework.store');
    });

    

    // ============================================================================
    // MANDOR
    // ============================================================================
    Route::middleware('permission:masterdata.mandor.view')->group(function () {
        Route::get('mandor', [MandorController::class, 'index'])->name('mandor.index');
        });
    Route::middleware('permission:masterdata.mandor.create')->group(function () {
        Route::post('mandor', [MandorController::class, 'store'])->name('mandor.store');
    });
    Route::middleware('permission:masterdata.mandor.edit')->group(function () {
        Route::match(['put', 'patch'], 'mandor/{companycode}/{userid}', [MandorController::class, 'update'])->name('mandor.update');
    });
    Route::middleware('permission:masterdata.mandor.delete')->group(function () {
        Route::delete('mandor/{companycode}/{userid}', [MandorController::class, 'destroy'])->name('mandor.destroy');
    });

    // ============================================================================
    // TENAGA KERJA
    // ============================================================================
    Route::middleware('permission:masterdata.tenagakerja.view')->group(function () {
        Route::get('tenagakerja', [TenagaKerjaController::class, 'index'])->name('tenagakerja.index');
    });
    Route::middleware('permission:masterdata.tenagakerja.create')->group(function () {
        Route::post('tenagakerja', [TenagaKerjaController::class, 'store'])->name('tenagakerja.store');
        Route::get('tenagakerja/download-template', [TenagaKerjaController::class, 'downloadTemplate'])->name('tenagakerja.download-template');
        Route::post('tenagakerja/bulk-upload', [TenagaKerjaController::class, 'bulkUpload'])->name('tenagakerja.bulk-upload');
    });
    Route::middleware('permission:masterdata.tenagakerja.edit')->group(function () {
        Route::match(['put', 'patch'], 'tenagakerja/{companycode}/{id}', [TenagaKerjaController::class, 'update'])->name('tenagakerja.update');
    });
    Route::middleware('permission:masterdata.tenagakerja.delete')->group(function () {
        Route::delete('tenagakerja/{companycode}/{id}', [TenagaKerjaController::class, 'destroy'])->name('tenagakerja.destroy');
    });

    // ============================================================================
    // UPAH
    // ============================================================================
    Route::middleware('permission:masterdata.upah.view')->group(function () {
        Route::get('upah', [UpahController::class, 'index'])->name('upah.index');
        Route::post('upah', [UpahController::class, 'store'])->name('upah.store');
    });

    Route::middleware('permission:masterdata.upah.edit')->group(function () {
        Route::put('upah/{id}', [UpahController::class, 'update'])->name('upah.update');
    });

    Route::middleware('permission:masterdata.upah.delete')->group(function () {
        Route::delete('upah/{id}', [UpahController::class, 'destroy'])->name('upah.destroy');
    });

    // UPAH BORONGAN
    Route::middleware('permission:masterdata.upah.view')->group(function () {
        Route::get('upah-borongan', [UpahBoronganController::class, 'index'])->name('upah-borongan.index');
        Route::get('upah-borongan/activities-by-group', [UpahBoronganController::class, 'getActivitiesByGroup'])->name('upah-borongan.activities-by-group');
        Route::get('upah-borongan/current-wage', [UpahBoronganController::class, 'getCurrentWage'])->name('upah-borongan.current-wage');
        Route::post('upah-borongan', [UpahBoronganController::class, 'store'])->name('upah-borongan.store');
        Route::put('upah-borongan/{id}', [UpahBoronganController::class, 'update'])->name('upah-borongan.update');
        Route::delete('upah-borongan/{id}', [UpahBoronganController::class, 'destroy'])->name('upah-borongan.destroy');
    });

    // ============================================================================
    // KENDARAAN
    // ============================================================================
    Route::middleware('permission:masterdata.kendaraan.view')->group(function () {
        Route::get('kendaraan', [KendaraanController::class, 'index'])->name('kendaraan.index');
        Route::post('kendaraan', [KendaraanController::class, 'handle'])->name('kendaraan.handle');
    });

    Route::middleware('permission:masterdata.kendaraan.edit')->group(function () {
        Route::put('kendaraan/{companycode}/{nokendaraan}', [KendaraanController::class, 'update'])->name('kendaraan.update');
    });

    Route::middleware('permission:masterdata.kendaraan.delete')->group(function () {
        Route::delete('kendaraan/{companycode}/{nokendaraan}', [KendaraanController::class, 'destroy'])->name('kendaraan.destroy');
    });

    // ============================================================================
    // KONTRAKTOR
    // ============================================================================
    Route::middleware('permission:masterdata.kontraktor.view')->group(function () {
        Route::get('kontraktor', [KontraktorController::class, 'index'])->name('kontraktor.index');
        Route::post('kontraktor', [KontraktorController::class, 'store'])->name('kontraktor.store');
    });

    Route::middleware('permission:masterdata.kontraktor.edit')->group(function () {
        Route::match(['put', 'patch'], 'kontraktor/{companycode}/{id}', [KontraktorController::class, 'update'])->name('kontraktor.update');
    });

    Route::middleware('permission:masterdata.kontraktor.delete')->group(function () {
        Route::delete('kontraktor/{companycode}/{id}', [KontraktorController::class, 'destroy'])->name('kontraktor.destroy');
    });

    // ============================================================================
    // SUBKONTRAKTOR
    // ============================================================================
    Route::middleware('permission:masterdata.subkontraktor.view')->group(function () {
        Route::get('subkontraktor', [SubkontraktorController::class, 'index'])->name('subkontraktor.index');
        Route::post('subkontraktor', [SubkontraktorController::class, 'store'])->name('subkontraktor.store');
    });

    Route::middleware('permission:masterdata.subkontraktor.edit')->group(function () {
        Route::match(['put', 'patch'], 'subkontraktor/{companycode}/{id}', [SubkontraktorController::class, 'update'])->name('subkontraktor.update');
    });

    Route::middleware('permission:masterdata.subkontraktor.delete')->group(function () {
        Route::delete('subkontraktor/{companycode}/{id}', [SubkontraktorController::class, 'destroy'])->name('subkontraktor.destroy');
    });
});
