<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partido;
use App\Models\Candidato;

class CandidatosDirectorioDepartamentalSeeder extends Seeder
{
    public function run(): void
    {
        $movimientos = [
            '3' => Partido::where('numero_lista', '3')->first(),
            '9' => Partido::where('numero_lista', '9')->first(),
            '100' => Partido::where('numero_lista', '100')->first(),
        ];

        $cargo = 'directorio departamental';

        $candidatos = [
            // Nuevo Liberalismo - Lista 3
            ['lista' => '3', 'nombre' => 'Freddy Franco', 'orden' => 1],
            ['lista' => '3', 'nombre' => 'Santi Aguilera', 'orden' => 2],
            ['lista' => '3', 'nombre' => 'Sergio Estigarribia', 'orden' => 3],
            // Frente Radical - Lista 9
            ['lista' => '9', 'nombre' => 'Billy Vaezken', 'orden' => 1],
            ['lista' => '9', 'nombre' => 'Laura Amarilla', 'orden' => 2],
            ['lista' => '9', 'nombre' => 'Fernando Javier García Florentín', 'orden' => 3],
            // Diálogo Azul - Lista 100
            ['lista' => '100', 'nombre' => 'Lorenzo Mendieta', 'orden' => 1],
            ['lista' => '100', 'nombre' => 'Silvano Britez', 'orden' => 2],
            ['lista' => '100', 'nombre' => 'Carlos Zelaya', 'orden' => 3],
        ];

        foreach ($candidatos as $c) {
            $partido = $movimientos[$c['lista']] ?? null;
            if ($partido) {
                Candidato::updateOrCreate(
                    [
                        'partido_id' => $partido->id,
                        'nombre_completo' => $c['nombre'],
                        'cargo' => $cargo,
                    ],
                    [
                        'numero_orden' => $c['orden'],
                        'activo' => true,
                    ]
                );
            }
        }

        $this->command->info('Candidatos a directorio departamental cargados correctamente. Total: ' . count($candidatos));
    }
}
