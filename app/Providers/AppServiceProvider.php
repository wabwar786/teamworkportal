<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Railway terminates TLS at the proxy; force https URLs in production
        // so assets, form actions and redirects don't downgrade to http.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
