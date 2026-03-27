<?php

namespace App\Providers;

use App\Http\Controllers\RolController;
use App\Http\Controllers\RolesController;
use App\Models\Sistema;
use App\Observers\SistemaObserver;
use App\Services\PermisoService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PermisoService::class, function ($app) {
            return new PermisoService($app->make(RolesController::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sistema::observe(SistemaObserver::class);
    }
}
