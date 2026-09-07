<?php

use App\Http\Middleware\AuthenticateAgent;
use App\Http\Middleware\EnsureOwnerTier;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Railway (and most PaaS) sit behind a proxy — trust it so Laravel
        // sees the real https scheme, host and client IP.
        $middleware->trustProxies(at: '*');

        // route middleware aliases
        $middleware->alias([
            'tier' => EnsureOwnerTier::class,
            'agent' => AuthenticateAgent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
