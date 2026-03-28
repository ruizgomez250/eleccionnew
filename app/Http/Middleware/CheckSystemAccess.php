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
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder a esta página');
        }
        
        return $next($request);
    }
}