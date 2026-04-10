<?php

// routes\transaction.php

use App\Http\Controllers\Transaction\AgronomiController;
use App\Http\Controllers\Transaction\KoreksiSJPanenController;
use App\Http\Controllers\Transaction\SuratJalanNonNfcController;
use App\Http\Controllers\Transaction\GudangBbmController;
use App\Http\Controllers\Transaction\GudangController;
use App\Http\Controllers\Transaction\HPTController;
use App\Http\Controllers\Transaction\KendaraanController;
use App\Http\Controllers\Transaction\KendaraanSupplyController;
use App\Http\Controllers\Transaction\MappingBsmController;
use App\Http\Controllers\Transaction\NfcController;
use App\Http\Controllers\Transaction\OrderBbmController;
use App\Http\Controllers\Transaction\PiasController;
use App\Http\Controllers\Transaction\RencanaKerjaHarian\ApprovalInfoController;
use App\Http\Controllers\Transaction\RencanaKerjaHarian\Domain\MaterialUsageController;
use App\Http\Controllers\Transaction\RencanaKerjaHarian\LkhController;
use App\Http\Controllers\Transaction\RencanaKerjaHarian\Report\DthReportController;
use App\Http\Controllers\Transaction\RencanaKerjaHarian\Report\OperatorRekapReportController;
use App\Http\Controllers\Transaction\RencanaKerjaHarian\Report\OperatorReportController;
use App\Http\Controllers\Transaction\RencanaKerjaHarian\Report\RekapLkhReportController;
use App\Http\Controllers\Transaction\RencanaKerjaHarian\RkhController;
use App\Http\Controllers\Transaction\RencanaKerjaHarian\Utility\RkhUtilityController;
use App\Http\Controllers\Transaction\RencanaKerjaMingguanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('transaction')->name('transaction.')->group(function () {

    // ============================================================================
    // AGRONOMI
    // ============================================================================
    Route::middleware('permission:transaction.agronomi.view')->group(function () {
        Route::get('agronomi', [AgronomiController::class, 'index'])->name('agronomi.index');
        Route::post('agronomi', [AgronomiController::class, 'handle'])->name('agronomi.handle');
        Route::get('agronomi/show/{nosample}/{companycode}/{tanggalpengamatan}', [AgronomiController::class, 'show'])->name('agronomi.show');
        Route::post('agronomi/get-blok', [AgronomiController::class, 'getBlokbyField'])->name('agronomi.getBlok');
        Route::post('agronomi/get-var', [AgronomiController::class, 'getVarietasandKategori'])->name('agronomi.getVar');
    });

    Route::middleware('permission:transaction.agronomi.create')->group(function () {
        Route::get('agronomi/create', [AgronomiController::class, 'create'])->name('agronomi.create');
    });

    Route::middleware('permission:transaction.agronomi.edit')->group(function () {
        Route::get('agronomi/{nosample}/{companycode}/{tanggalpengamatan}/edit', [AgronomiController::class, 'edit'])->name('agronomi.edit');
        Route::put('agronomi/{nosample}/{companycode}/{tanggalpengamatan}', [AgronomiController::class, 'update'])->name('agronomi.update');
    });

    Route::middleware('permission:transaction.agronomi.delete')->group(function () {
        Route::delete('agronomi/{nosample}/{companycode}/{tanggalpengamatan}', [AgronomiController::class, 'destroy'])->name('agronomi.destroy');
    });

    Route::middleware('permission:transaction.agronomi.export')->group(function () {
        Route::get('agronomi/excel', [AgronomiController::class, 'excel'])->name('agronomi.exportExcel');
    });

    // ============================================================================
    // HPT
    // ============================================================================
    Route::middleware('permission:transaction.hpt.view')->group(function () {
        Route::get('hpt', [HPTController::class, 'index'])->name('hpt.index');
        Route::post('hpt', [HPTController::class, 'handle'])->name('hpt.handle');
        Route::get('hpt/show/{nosample}/{companycode}/{tanggalpengamatan}', [HPTController::class, 'show'])->name('hpt.show');
        Route::post('hpt/get-blok', [HPTController::class, 'getBlokbyField'])->name('hpt.getBlok');
        Route::post('hpt/get-var', [HPTController::class, 'getVarietasandKategori'])->name('hpt.getVar');
    });

    Route::middleware('permission:transaction.hpt.create')->group(function () {
        Route::get('hpt/create', [HPTController::class, 'create'])->name('hpt.create');
    });

    Route::middleware('permission:transaction.hpt.edit')->group(function () {
        Route::get('hpt/{nosample}/{companycode}/{tanggalpengamatan}/edit', [HPTController::class, 'edit'])->name('hpt.edit');
        Route::put('hpt/{nosample}/{companycode}/{tanggalpengamatan}', [HPTController::class, 'update'])->name('hpt.update');
    });

    Route::middleware('permission:transaction.hpt.delete')->group(function () {
        Route::delete('hpt/{nosample}/{companycode}/{tanggalpengamatan}', [HPTController::class, 'destroy'])->name('hpt.destroy');
    });

    Route::middleware('permission:transaction.hpt.export')->group(function () {
        Route::get('hpt/excel', [HPTController::class, 'excel'])->name('hpt.exportExcel');
    });

    // ============================================================================
    // RENCANA KERJA MINGGUAN
    // ============================================================================
    Route::prefix('rencana-kerja-mingguan')->name('rencana-kerja-mingguan.')->controller(RencanaKerjaMingguanController::class)->group(function () {

        Route::middleware('permission:transaction.rencanakerjamingguan.view')->group(function () {
            Route::match(['GET', 'POST'], '/', 'index')->name('index');
            Route::get('/show/{rkmno}', 'show')->name('show');
            Route::get('/excel', 'excel')->name('exportExcel');
            Route::get('/getplot/{blok}', 'getPlot')->name('getPlot');
            Route::post('/getdata', 'getData')->name('getData');
        });

        Route::middleware('permission:transaction.rencanakerjamingguan.create')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
        });

        Route::middleware('permission:transaction.rencanakerjamingguan.edit')->group(function () {
            Route::get('/{rkmno}/edit', 'edit')->name('edit');
            Route::put('/{rkmno}', 'update')->name('update');
        });

        Route::delete('/{rkmno}', 'destroy')
            ->middleware('permission:transaction.rencanakerjamingguan.delete')
            ->name('destroy');
    });

    // ============================================================================
    // RENCANA KERJA HARIAN
    // ============================================================================
    Route::middleware('permission:transaction.rencanakerjaharian.view')->group(function () {
        Route::prefix('kerjaharian/rencanakerjaharian')->name('rencanakerjaharian.')->group(function () {

            // ============================================================
            // RKH CRUD
            // ============================================================
            Route::controller(RkhController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::get('/create-v2', 'createV2')->name('create-v2');
                Route::post('/store', 'store')->name('store');
                Route::get('/{rkhno}/show', 'show')->name('show');
                Route::get('/{rkhno}/edit', 'editV2')->name('edit');
                Route::put('/{rkhno}', 'update')->name('update');
                Route::delete('/{rkhno}', 'destroy')->name('destroy');

                Route::get('/{rkhno}/print', 'printView')->name('print');
                Route::post('/{rkhno}/cancel', 'cancel')->name('cancel');
                Route::get('/{rkhno}/batal-detail', 'getBatalDetail')->name('batalDetail');
            });

            // ============================================================
            // APPROVAL INFO (RKH & LKH - Read Only)
            // ============================================================
            Route::controller(ApprovalInfoController::class)->group(function () {
                // RKH Approval Info
                Route::get('/{rkhno}/approval-detail', 'getRkhApprovalDetail')->name('getApprovalDetail');
                Route::post('/update-status', 'updateRkhStatus')->name('updateStatus');

                // LKH Approval Info
                Route::get('/lkh/{lkhno}/approval-detail', 'getLkhApprovalDetail')->name('getLkhApprovalDetail');
            });

            // ============================================================
            // LKH MANAGEMENT
            // ============================================================
            Route::controller(LkhController::class)->group(function () {
                Route::get('/{rkhno}/lkh', 'getLKHData')->name('getLKHData');
                Route::get('/lkh/{lkhno}/show', 'showLKH')->name('showLKH');
                Route::get('/lkh/{lkhno}/edit', 'editLKH')->name('editLKH');
                Route::put('/lkh/{lkhno}', 'updateLKH')->name('updateLKH');
                Route::post('/lkh/submit', 'submitLKH')->name('submitLKH');
                Route::post('/{rkhno}/generate-lkh', 'manualGenerateLkh')->name('manualGenerateLkh');
                Route::post('/lkh/recalculate-wages', 'recalculateWages')->name('recalculateWages');
                Route::get('/lkh/{lkhno}/print', 'printLKH')->name('printLKH');
            });

            // ============================================================
            // REPORTS
            // ============================================================
            // DTH Report
            Route::controller(DthReportController::class)->group(function () {
                Route::post('/generate-dth', 'generate')->name('generateDTH');
                Route::get('/dth-report', 'show')->name('dth-report');
                Route::get('/dth-data', 'getData')->name('dth-data');
            });

            // Rekap LKH Report
            Route::controller(RekapLkhReportController::class)->group(function () {
                Route::post('/generate-rekap-lkh', 'generate')->name('generateRekapLKH');
                Route::get('/rekap-lkh-report', 'show')->name('rekap-lkh-report');
                Route::get('/lkh-rekap-data', 'getData')->name('lkh-rekap-data');
            });

            // Operator Rekap Report (All Operators - Summary)
            Route::controller(OperatorRekapReportController::class)->group(function () {
                Route::post('/generate-operator-rekap-report', 'generate')->name('generateOperatorRekapReport');
                Route::get('/operator-rekap-report', 'show')->name('operator-rekap-report');
                Route::get('/operator-rekap-report-data', 'getData')->name('operator-rekap-report-data');
            });

            // Operator Report
            Route::controller(OperatorReportController::class)->group(function () {
                Route::get('/operators-for-date', 'getOperatorsForDate')->name('getOperatorsForDate');
                Route::post('/generate-operator-report', 'generate')->name('generateOperatorReport');
                Route::get('/operator-report', 'show')->name('operator-report');
                Route::get('/operator-report-data', 'getData')->name('operator-report-data');
            });

            // ============================================================
            // UTILITY / HELPERS
            // ============================================================
            Route::controller(RkhUtilityController::class)->group(function () {
                Route::get('/load-absen-by-date', 'loadAbsenByDate')->name('loadAbsenByDate');
                Route::get('/plot-info/{plot}/{activitycode}', 'getPlotInfo')->name('getPlotInfo');
                Route::post('/check-outstanding', 'checkOutstandingRKH')->name('checkOutstanding');
                Route::get('/lkh-panen-report/get-sj', 'getSuratJalan')->name('lkh-panen-report.get-sj');
            });

            // ============================================================
            // MATERIAL USAGE
            // ============================================================
            Route::controller(MaterialUsageController::class)->group(function () {
                Route::get('/{rkhno}/material-usage', 'getMaterialUsageApi')->name('getMaterialUsage');
                Route::post('/generate-material-usage', 'generateMaterialUsage')->name('generateMaterialUsage');
            });
        });
    });

    // ============================================================================
    // GUDANG
    // ============================================================================
    Route::middleware('permission:transaction.gudang.view')->group(function () {
        Route::get('gudang', [GudangController::class, 'home'])->name('gudang.index');
        Route::get('gudang/detail', [GudangController::class, 'detail'])->name('gudang.detail');
        Route::post('gudang/submit', [GudangController::class, 'submit'])->name('gudang.submit');
        Route::any('gudang/retur', [GudangController::class, 'retur'])->name('gudang.retur');
        Route::any('gudang/returall', [GudangController::class, 'returAll'])->name('gudang.returall');
        Route::get('gudang/report', [GudangController::class, 'report'])->name('gudang.report');
        Route::get('gudang/koreksi-insert', [GudangController::class, 'koreksi_insert'])->name('gudang.koreksi');
        Route::post('gudang/koreksi-submit', [GudangController::class, 'koreksi_submit'])->name('gudang.koreksi.submit');

        Route::post('gudang/get-items-by-rkh', [GudangController::class, 'getItemsByRkh'])->name('gudang.getItemsByRkh');
        Route::post('gudang/get-item-detail', [GudangController::class, 'getItemDetail'])->name('gudang.getItemDetail');
    });

    // ============================================================================
    // PIAS
    // ============================================================================
    Route::middleware('permission:transaction.pias.view')->group(function () {
        Route::get('pias', [PiasController::class, 'home'])->name('pias.index');
        Route::get('pias/detail', [PiasController::class, 'detail'])->name('pias.detail');
        Route::post('pias/submit', [PiasController::class, 'submit'])->name('pias.submit');
        Route::get('pias/report', [PiasController::class, 'report'])->name('pias.report');
        Route::get('pias/export', [PiasController::class, 'exportExcel'])->name('pias.export');
    });


    // ============================================================================
    // KENDARAAN SUPPLY - Mandor Kendaraan
    // ============================================================================
    Route::middleware('permission:transaction.kendaraansupply.view')->group(function () {
        Route::prefix('kendaraan-supply')->name('kendaraan-supply.')->group(function () {
            Route::get('/', [KendaraanSupplyController::class, 'index'])->name('index');
            Route::post('/store', [KendaraanSupplyController::class, 'store'])->name('store');
            Route::put('/{id}', [KendaraanSupplyController::class, 'update'])->name('update');
            Route::delete('/{id}', [KendaraanSupplyController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/submit', [KendaraanSupplyController::class, 'submit'])->name('submit');

            // API
            Route::get('/kendaraan-list', [KendaraanSupplyController::class, 'getKendaraanList'])->name('kendaraan-list');
            Route::get('/operator-list', [KendaraanSupplyController::class, 'getOperatorList'])->name('operator-list');
            Route::get('/pending-for-order', [KendaraanSupplyController::class, 'getPendingForOrder'])->name('pending-for-order');
        });
    });

    // ============================================================================
    // ORDER BBM - Admin Kendaraan
    // ============================================================================
    Route::middleware('permission:transaction.orderbbm.view')->group(function () {
        Route::prefix('order-bbm')->name('order-bbm.')->group(function () {
            Route::get('/', [OrderBbmController::class, 'index'])->name('index');
            Route::post('/', [OrderBbmController::class, 'store'])->name('store');
            Route::get('/{orderno}', [OrderBbmController::class, 'show'])->name('show');
            Route::put('/{orderno}', [OrderBbmController::class, 'update'])->name('update');
            Route::post('/{orderno}/submit', [OrderBbmController::class, 'submit'])->name('submit');
            Route::get('/lkh/{lkhno}/kendaraan', [OrderBbmController::class, 'getKendaraanFromLkh'])->name('lkh-kendaraan');
            Route::get('/{orderno}/items', [OrderBbmController::class, 'getItems'])->name('items');
        });
    });

    // ============================================================================
    // GUDANG BBM - Admin Gudang BBM
    // ============================================================================
    Route::middleware('permission:transaction.gudangbbm.view')->group(function () {
        Route::prefix('gudang-bbm')->name('gudang-bbm.')->group(function () {
            Route::get('/', [GudangBbmController::class, 'index'])->name('index');
            Route::get('/{orderno}', [GudangBbmController::class, 'show'])->name('show');
            Route::post('/{orderno}/confirm-item', [GudangBbmController::class, 'confirmItem'])->name('confirm-item');
            Route::post('/{orderno}/finalize', [GudangBbmController::class, 'finalizeAll'])->name('finalize');
            Route::post('/{orderno}/sync-citrix', [GudangBbmController::class, 'syncCitrix'])->name('sync-citrix');
        });
    });

    // ============================================================================
    // NFC CARD MANAGEMENT
    // ============================================================================
    Route::middleware('permission:transaction.nfc.view')->group(function () {
        Route::prefix('nfc')->name('nfc.')->controller(NfcController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/transaction-out', 'transactionOut')->name('transaction-out');
            Route::post('/transaction-in', 'transactionIn')->name('transaction-in');
            Route::post('/pos-in', 'posIn')->name('pos-in');
            Route::post('/external-in', 'externalIn')->name('external-in');
            Route::post('/external-out', 'externalOut')->name('external-out');
        });
    });

    // ============================================================================
    // KOREKSI SJ PANEN
    // ============================================================================
    Route::middleware('permission:transaction.koreksisjpanen.view')->group(function () {
        Route::prefix('koreksi-sj-panen')->name('koreksi-sj-panen.')->group(function () {
            Route::get('/', [KoreksiSJPanenController::class, 'index'])->name('index');
            Route::post('/store', [KoreksiSJPanenController::class, 'store'])->name('store');
            Route::get('/get-sj', [KoreksiSJPanenController::class, 'getSuratJalanDetail'])->name('get-sj');
            Route::get('/check-plot', [KoreksiSJPanenController::class, 'checkPlot'])->name('check-plot');
        });
    });

    // ============================================================================
    // INPUT SJ NON-NFC
    // ============================================================================
    Route::middleware('permission:transaction.surat-jalan-non-nfc.view')->group(function () {
        Route::prefix('surat-jalan-non-nfc')->name('surat-jalan-non-nfc.')->group(function () {
            Route::get('/', [SuratJalanNonNfcController::class, 'index'])->name('index');
            Route::get('/{id}', [SuratJalanNonNfcController::class, 'show'])->name('show');
            Route::post('/store', [SuratJalanNonNfcController::class, 'store'])->name('store');
            Route::post('/mark-printed', [SuratJalanNonNfcController::class, 'markPrinted'])->name('mark-printed');
            Route::get('/form-data', [SuratJalanNonNfcController::class, 'getFormData'])->name('form-data');
            Route::get('/attachment/{id}', [SuratJalanNonNfcController::class, 'getAttachment'])->name('attachment');
        });
    });

    // ============================================================================
    // MAPPING BSM
    // ============================================================================
    Route::middleware('permission:transaction.mappingbsm.view')->group(function () {
        Route::match(['GET', 'POST'], 'mapping-bsm', [MappingBsmController::class, 'index'])->name('mapping-bsm.index');
        Route::get('mapping-bsm/get-bsm-detail', [MappingBsmController::class, 'getBsmDetail'])->name('mapping-bsm.get-bsm-detail');
        Route::post('update-bsm', [MappingBsmController::class, 'updateBsm'])->name('mapping-bsm.update-bsm');
        Route::post('update-bsm-bulk', [MappingBsmController::class, 'updateBsmBulk'])->name('mapping-bsm.update-bsm-bulk');
        Route::get('get-bsm-for-copy', [MappingBsmController::class, 'getBsmForCopy'])->name('mapping-bsm.get-bsm-for-copy');
        Route::post('copy-bsm', [MappingBsmController::class, 'copyBsm'])->name('mapping-bsm.copy-bsm');
        Route::post('remap-bsm', [MappingBsmController::class, 'remapBsm'])->name('mapping-bsm.remap-bsm');
    });


});