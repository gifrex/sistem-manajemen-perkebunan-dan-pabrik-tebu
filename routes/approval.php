<?php

// routes/approval.php

use App\Http\Controllers\Approval\AbsenApprovalController;
use App\Http\Controllers\Approval\ApprovalDashboardController;
use App\Http\Controllers\Approval\LkhApprovalController;
use App\Http\Controllers\Approval\OtherApprovalController;
use App\Http\Controllers\Approval\RkhApprovalController;
use App\Http\Controllers\Approval\UpahMingguanApprovalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Approval\OrderBbmApprovalController;

Route::middleware('auth')->prefix('approval')->name('approval.')->group(function () {

    // ============================================================================
    // APPROVAL DASHBOARD
    // Unified dashboard untuk semua jenis approval
    // ============================================================================
    Route::get('/', [ApprovalDashboardController::class, 'index'])->name('index');

    // ============================================================================
    // RKH APPROVAL
    // High-frequency approval with embedded fields
    // ============================================================================
    Route::prefix('rkh')->name('rkh.')->group(function () {
        Route::post('/process', [RkhApprovalController::class, 'process'])->name('process');
        Route::get('/{rkhno}/detail', [RkhApprovalController::class, 'detail'])->name('detail');
        Route::get('/{rkhno}/history', [RkhApprovalController::class, 'history'])->name('history');
    });

    // ============================================================================
    // LKH APPROVAL
    // High-frequency approval with embedded fields
    // ============================================================================
    Route::prefix('lkh')->name('lkh.')->group(function () {
        Route::post('/process', [LkhApprovalController::class, 'process'])->name('process');
        Route::get('/{lkhno}/detail', [LkhApprovalController::class, 'detail'])->name('detail');
        Route::get('/{lkhno}/history', [LkhApprovalController::class, 'history'])->name('history');
    });

    // ============================================================================
    // OTHER APPROVALS (Generic)
    // Low-frequency approvals using approvaltransaction table
    // Split/Merge, Purchase Request, Leave Request, etc.
    // ============================================================================
    Route::prefix('other')->name('other.')->group(function () {
        Route::post('/process', [OtherApprovalController::class, 'process'])->name('process');
        Route::get('/{approvalno}/detail', [OtherApprovalController::class, 'detail'])->name('detail');
        Route::get('/{approvalno}/history', [OtherApprovalController::class, 'history'])->name('history');
    });

    // ============================================================================
    // ABSEN APPROVAL
    // Attendance approval (header + individual foto approval)
    // ============================================================================
    Route::prefix('absen')->name('absen.')->group(function () {
        Route::post('/process', [AbsenApprovalController::class, 'process'])->name('process');
        Route::post('/foto/process', [AbsenApprovalController::class, 'processFoto'])->name('foto.process');
        Route::get('/{absenno}/detail', [AbsenApprovalController::class, 'detail'])->name('detail');
        Route::get('/{absenno}/history', [AbsenApprovalController::class, 'history'])->name('history');
    });

    // ============================================================================
    // UPAH MINGGUAN APPROVAL
    // Weekly Paid approval
    // ============================================================================
    Route::prefix('upah-mingguan')->name('upah-mingguan.')->group(function () {
        Route::post('/process', [UpahMingguanApprovalController::class, 'process'])->name('process');
        Route::post('/process-group', [UpahMingguanApprovalController::class, 'processGroup'])->name('process-group');
        Route::get('/detail-group', [UpahMingguanApprovalController::class, 'detailGroup'])->name('detail-group');
        Route::get('/{transno}/detail', [UpahMingguanApprovalController::class, 'detail'])->name('detail');
        Route::get('/{transno}/history', [UpahMingguanApprovalController::class, 'history'])->name('history');
    });

    // ============================================================================
    // APPROVAL HISTORY & AUDIT (Future)
    // ============================================================================
    // Route::get('/history', [ApprovalHistoryController::class, 'index'])->name('history.index');
    // Route::get('/audit', [ApprovalAuditController::class, 'index'])->name('audit.index');

    // Order BBM Approval
    Route::post('/order-bbm/process', [OrderBbmApprovalController::class, 'process'])->name('order-bbm.process');
    Route::get('/order-bbm/{orderno}/detail', [OrderBbmApprovalController::class, 'detail'])->name('order-bbm.detail');
});