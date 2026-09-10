<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AccesoEfectividadElectoral
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        abort_unless($user && in_array((int) $user->id, [1, 2, 3, 4], true)
            && $user->can('Carga Certificados'), 403);

        return $next($request);
    }
}
