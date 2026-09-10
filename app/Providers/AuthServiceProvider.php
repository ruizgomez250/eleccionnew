<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Compatibilidad con nombres de permisos cargados manualmente en hosting.
        // La misma habilidad se utiliza en el menu y en el middleware de certificados.
        Gate::define('Carga Certificados', function (User $user): bool {
            return $user->getAllPermissions()->contains(function ($permission) {
                $name = trim(preg_replace('/\s+/u', ' ', $permission->name));

                return $permission->guard_name === 'web'
                    && mb_strtolower($name, 'UTF-8') === 'carga certificados';
            });
        });
    }
}
