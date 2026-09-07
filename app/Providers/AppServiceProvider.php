<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

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

        // Make the signed-in owner available as $u in EVERY view — including
        // content sections, which are compiled before the layout's own @php
        // runs (so a variable defined only in the layout is undefined there).
        View::composer('*', function ($view) {
            $view->with('u', auth()->user());
        });
    }
}
