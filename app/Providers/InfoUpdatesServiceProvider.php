<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repositories
use App\Repositories\InfoUpdates\NotificationRepository;

// Services
use App\Services\InfoUpdates\NotificationService;

class InfoUpdatesServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Register Repository as Singleton
        $this->app->singleton(NotificationRepository::class);

        // Register Service as Singleton with dependency injection
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService(
                $app->make(NotificationRepository::class)
            );
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        //
    }
}