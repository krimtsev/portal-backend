<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        if (config('app.database_log')) {
            DB::listen(function ($query) {
                Log::build([
                    'driver' => 'single',
                    'path'   => storage_path('logs/database.log'),
                ])->info("Execution Time: {$query->time}ms\nSQL: {$query->sql}", [
                    'bindings' => $query->bindings,
                ]);
            });
        }

        Blade::anonymousComponentPath(
            resource_path('views/emails/components'),
            'emails'
        );
    }
}
