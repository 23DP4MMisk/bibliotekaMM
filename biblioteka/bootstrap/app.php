<?php

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
    ->withMiddleware(function (Middleware $middleware): void {
        
        $middleware->validateCsrfTokens([
            'http://localhost:5173',
        ]);

        $middleware->api(prepend: [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ]);
        
       
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'reģistrēties',
            'pieslēgties',
            'izrakstīties',
            'check-auth'
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
