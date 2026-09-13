<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class RegistrarAcceso
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        if (!$user) {
            return;
        }

        $request = request();

        DB::table('accesos')->insert([
            'user_id' => $user->getAuthIdentifier(),
            'login_at' => now(),
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}