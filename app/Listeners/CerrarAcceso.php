<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class CerrarAcceso
{
    public function handle(Logout $event): void
    {
        $user = $event->user;
        if (!$user) {
            return;
        }

        try {
            $acceso = DB::table('accesos')
                ->where('user_id', $user->getAuthIdentifier())
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first();

            if ($acceso) {
                $logoutAt = now();

                DB::table('accesos')
                    ->where('id', $acceso->id)
                    ->update([
                        'logout_at' => $logoutAt,
                        'duracion_segundos' => (int) $logoutAt->diffInSeconds($acceso->login_at),
                        'updated_at' => $logoutAt,
                    ]);
            }
        } catch (QueryException $exception) {
            // El historial auxiliar no debe interrumpir la autenticación.
            Log::warning('No se pudo cerrar el acceso de sesión.', [
                'user_id' => $user->getAuthIdentifier(),
                'sqlstate' => $exception->errorInfo[0] ?? $exception->getCode(),
            ]);
        }
    }
}
