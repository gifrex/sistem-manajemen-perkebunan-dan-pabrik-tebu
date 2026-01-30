<?php

// routes\web.php

use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\GPXController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\UsernameLoginController;
use App\Http\Controllers\LiveChatController;

// ============================================================================
// PUBLIC ROUTES (No Authentication)
// ============================================================================
Route::get('/login', [UsernameLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UsernameLoginController::class, 'login'])->name('login.post');
Route::post('/logout', [UsernameLoginController::class, 'logout'])->name('logout');

// ============================================================================
// AUTHENTICATED ROUTES
// ============================================================================
Route::middleware(['auth', 'mandor.access'])->group(function () {

    // ============================================================================
    // HOME & DASHBOARD
    // ============================================================================
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('/set-session', [HomeController::class, 'setSession'])->name('setSession');

    // ============================================================================
    // LIVE CHAT
    // ============================================================================
    Route::post('/chat/send', [LiveChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages', [LiveChatController::class, 'getMessages'])->name('chat.messages');
});
