<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        if (config('database.default') === 'sqlite' && !file_exists(database_path('database.sqlite'))) {
            touch(database_path('database.sqlite'));
        }
        
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
