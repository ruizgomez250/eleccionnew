<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermisosReportesUsuariosSeeder extends Seeder
{
    public const PERMISSIONS = [
        'Gestion de Permisos',
        'Listar Permisos',
        'Guardar Permisos',
        'Actualizar Permisos',
        'Eliminar Permisos',
        'Reportes',
        'Carga Certificados',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $users = DB::transaction(function () {
            $users = User::whereIn('id', [1, 4])->orderBy('id')->get();
            if ($users->count() !== 2) {
                throw new \RuntimeException('Deben existir los usuarios con ID 1 y 4. No se asignaron permisos.');
            }

            $permissions = collect(self::PERMISSIONS)->map(fn ($name) =>
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']));
            foreach ($users as $user) {
                // Añadir sin quitar permisos anteriores ni modificar roles compartidos.
                $user->givePermissionTo($permissions);
            }

            return $users;
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach ($users as $user) {
            $this->command?->info('Permisos de gestión de permisos y reportes asignados a '.$user->id.' - '.$user->name);
        }
    }
}
