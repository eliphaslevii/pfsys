<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\HasPermission;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => HasPermission::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'haspermission' => \App\Http\Middleware\HasPermission::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'nfe/espelho/lote',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSingletons([
        Illuminate\Contracts\Console\Kernel::class => App\Console\Kernel::class,
    ])
    ->create();
