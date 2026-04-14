<?php

use App\Http\Controllers\Api\PerhitunganUpahApiMobile;
use App\Http\Controllers\Api\Timbangan;

use App\Http\Controllers\Api\Auth\SanctumAuthController;
use App\Http\Controllers\Api\FileUpload\FotoAbsenController;
use App\Http\Controllers\Api\FileUpload\LkhFotoLampiranController;
use App\Http\Controllers\Api\ApiClientTokenController;

use App\Http\Controllers\MobileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Mobile & External Applications
|--------------------------------------------------------------------------
|
| Routes for mobile apps and external API consumers.
| Uses token-based authentication (Laravel Sanctum).
| All routes are prefixed with /api automatically.
|
*/

// Public endpoints
Route::prefix('auth')->group(function () {
    Route::post('login', [SanctumAuthController::class, 'login']);
});

Route::post('/loginmobile', [MobileController::class, 'loginMobile']);
Route::get('/getcompanymobile', [MobileController::class, 'getCompaniesMobile']);

Route::get('/test-api', function() {
    return response()->json([
        'message' => 'API works', 
        'time' => now(),
        'version' => '1.0.0'
    ]);
});

//Route::post('/timbangan/dev/v1/insertdata', [Timbangan::class, 'insertData']);
Route::middleware(['auth:sanctum', 'api.client.active'])->prefix('timbangan/dev/v1')->group(function () {
        Route::post('/insertdata', [Timbangan::class, 'insertData']);
});

Route::middleware('auth:sanctum')->prefix('admin/api-clients')->group(function () {
    Route::get('/',                      [ApiClientTokenController::class, 'listClients']);
    Route::post('/create',               [ApiClientTokenController::class, 'createClient']);
    Route::patch('/{id}/deactivate',     [ApiClientTokenController::class, 'deactivateClient']);
    Route::delete('/{id}/revoke',        [ApiClientTokenController::class, 'revokeTokens']);
});



// Protected endpoints - requires Sanctum token authentication
Route::middleware('auth:sanctum')->group(function () {
    
    // Mobile wage calculation and insertion
    Route::post('mobile/insert-upah-tenaga-kerja', [PerhitunganUpahApiMobile::class, 'insertWorkerWage'])
        ->name('api.mobile.insert-upah');
    Route::post('mobile/insert-upah-tenaga-kerja-borongan', [PerhitunganUpahApiMobile::class, 'insertUpahBorongan'])
        ->name('api.mobile.insert-upah-borongan');
    
    // Agronomi and HPT data submission
    Route::post('agronomistoremobile', [MobileController::class, 'storeMobileAgronomi']);
    Route::post('hptstoremobile', [MobileController::class, 'storeMobileHPT']);
    Route::post('getfieldbymapping', [MobileController::class, 'getFieldByMapping']);
    Route::post('checkdataagronomi', [MobileController::class, 'checkDataAgronomi']);
    Route::post('checkdatahpt', [MobileController::class, 'checkDataHPT']);
    
    // File upload - Absensi
    Route::post('fileupload/foto-absen-masuk', [FotoAbsenController::class, 'uploadMasuk'])
        ->name('api.fileupload.foto-absen-masuk');
    Route::post('fileupload/foto-absen-pulang', [FotoAbsenController::class, 'uploadPulang'])
        ->name('api.fileupload.foto-absen-pulang');

    // File upload - LKH Lampiran
    Route::post('fileupload/lkh-foto-lampiran', [LkhFotoLampiranController::class, 'upload'])
        ->name('api.fileupload.lkh-foto-lampiran');
   
});