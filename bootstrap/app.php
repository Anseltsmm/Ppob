<?php

use App\Http\Middleware\AdminDomain;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\RefreshSettingsCache;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => IsAdmin::class,
        ]);

        $middleware->web(append: [
            AdminDomain::class,
            RefreshSettingsCache::class,
        ]);

        // Webhook callback dari Tripay & OkeConnect tidak pakai CSRF
        $middleware->validateCsrfTokens(except: [
            'webhook/tripay',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
