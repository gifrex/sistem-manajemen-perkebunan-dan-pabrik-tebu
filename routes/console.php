<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// === SCHEDULE ===

// Hapus file upload lebih dari 2 hari, jalan setiap hari jam 00:00
Schedule::call(function () {
    $path = public_path('uploads');

    if (!File::isDirectory($path)) return;

    $threshold = Carbon::now()->subDays(2);
    $deleted = [];

    foreach (File::files($path) as $file) {
        if (Carbon::createFromTimestamp($file->getMTime())->lt($threshold)) {
            $deleted[] = $file->getFilename();
            File::delete($file->getPathname());
        }
    }

    if (count($deleted) > 0) {
        Log::info('Hapus file lama: ' . implode(', ', $deleted));
    }
})->daily()->at('00:00')
  ->name('hapus-file-lama')
  ->withoutOverlapping();

// Nanti tambah job lain di sini, contoh:
// Schedule::command('cache:prune-stale-tags')->hourly();