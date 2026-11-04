<?php

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
         'isAdmin' => \App\Http\Middleware\EnsureIsAdmin::class,
         'isCustomer'=> \App\Http\Middleware\EnsureIsCustomer::class,
         'isDeliveryAgent'=> \App\Http\Middleware\EnsureIsDeliveryAgent::class,
         'isRestaurantOwner'=> \App\Http\Middleware\EnsureIsRestaurantOwner::class,
            
        ]);

        // Stripe and Razorpay call these endpoints directly (no browser session / CSRF token available)
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
            'razorpay/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create(); 
