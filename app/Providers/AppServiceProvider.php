<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        // The whole app is built on Bootstrap 5 (no Tailwind CSS loaded), but
        // Laravel's default pagination view is Tailwind-styled — its SVG
        // arrow icons rely on Tailwind utility classes for sizing, so without
        // Tailwind they render huge and unstyled. Switch to the Bootstrap-5
        // compatible pagination view so `{{ $paginator->links() }}` renders
        // correctly everywhere it's used.
        Paginator::useBootstrapFive();
    }
}
