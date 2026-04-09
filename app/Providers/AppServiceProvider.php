<?php

namespace App\Providers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate; // <-- Tambahkan ini
use Illuminate\Support\ServiceProvider;
use App\View\Composers\NavigationComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading();

        // 1. PENGAMANAN LOG VIEWER (SANGAT PENTING)
        // Mengecek apakah user yang login memiliki idjabatan = 0 (Admin)
        Gate::define('viewLogViewer', function ($user) {
            return $user && (int) $user->idjabatan === 0;
        });

        // 2. Share navigation data ke view yang butuh sidebar
        View::composer('*', NavigationComposer::class);

        // 3. Share common data untuk Inertia
        Inertia::share([
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'logo_url' => asset('img/logo-tebu.png'),
            ]
        ]);
    }
}