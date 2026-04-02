<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Force HTTPS in production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Use /tmp for storage on serverless (Vercel)
        if (isset($_ENV['VERCEL']) || getenv('VERCEL')) {
            config([
                'view.compiled' => '/tmp/storage/framework/views',
                'cache.stores.file.path' => '/tmp/storage/framework/cache',
                'session.files' => '/tmp/storage/framework/sessions',
                'logging.channels.single.path' => '/tmp/storage/logs/laravel.log',
            ]);
        }
    }
}
