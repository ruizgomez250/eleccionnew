<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class RegistrarAcceso
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        if (!$user) {
            return;
        }

        $request = request();

        try {
            DB::table('accesos')->insert([
                'user_id' => $user->getAuthIdentifier(),
                'login_at' => now(),
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (QueryException $exception) {
            // El historial auxiliar no debe interrumpir la autenticación.
            Log::warning('No se pudo registrar el acceso de sesión.', [
                'user_id' => $user->getAuthIdentifier(),
                'sqlstate' => $exception->errorInfo[0] ?? $exception->getCode(),
            ]);
        }
    }
}
