<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        Schema::defaultStringLength(191);

        view()->composer('*', function ($view) {
            try {
                if (Schema::hasTable('parametres')) {
                    $view->with('hopitalParametres', \App\Models\Parametre::getSettings());
                }
            } catch (\Throwable $e) {
                // Silently catch in CLI/migrations
            }
        });
    }
}

