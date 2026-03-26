<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSystemAccess
{
    public function handle(Request $request, Closure $next)
    {
        $sistemaId = $request->route('sistema') ?? $request->input('sistema_id');
        
        if ($sistemaId && !auth()->user()->sistemasAccesibles->contains($sistemaId)) {
            abort(403, 'No tienes acceso a este sistema');
        }
        
        return $next($request);
    }
}