<?php

namespace App\Providers;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        // Share store settings to all views
        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('store_settings')) {
                    $settings = StoreSetting::getAllAsArray();
                    $settings['wa_number'] = StoreSetting::getWaNumber($settings['store_phone'] ?? null);
                    $view->with('storeSettings', $settings);
                }
            } catch (\Throwable $e) {
                // Fail-safe fallback if database is not reachable
            }
        });
    }
}
