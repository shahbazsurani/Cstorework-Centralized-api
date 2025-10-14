<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function () {
            // Versioned API routes (v1)
            Route::middleware('api')
                ->prefix('api/v1')
                ->as('api.v1.')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('api/v1')
                ->as('api.v1.')
                ->group(base_path('routes/user.php'));

            // Legacy routes (temporary for backward compatibility)
            Route::middleware('api')
                ->prefix('api')
                ->as('api.')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('api')
                ->as('api.')
                ->group(base_path('routes/user.php'));
        });
    }
}
