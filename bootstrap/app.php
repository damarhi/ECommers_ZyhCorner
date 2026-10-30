<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'blockKasir' => \App\Http\Middleware\CheckKasirRole::class,
            'preventBackHistory' => \App\Http\Middleware\PreventBackHistory::class,
            'role' => \App\Http\Middleware\EnsureUserRole::class,
            'checkBlockedCustomer' => \App\Http\Middleware\CheckBlockedCustomer::class,
        ]);
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\CheckBlockedCustomer::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            '/midtrans-callback',
            '/biteship/rates',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
