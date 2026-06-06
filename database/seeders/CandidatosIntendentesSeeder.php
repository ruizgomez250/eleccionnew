<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partido;
use App\Models\Candidato;

class CandidatosIntendentesSeeder extends Seeder
{
    public function run(): void
    {
        $movimientos = [
            'nl' => Partido::where('numero_lista', '3')->first(),
            '3m' => Partido::where('numero_lista', '3M')->first(),
            's21' => Partido::where('numero_lista', '21')->first(),
            'da' => Partido::where('numero_lista', '100')->first(),
        ];

        $candidatos = [
            ['partido' => 'nl', 'nombre' => 'Manolo Achucarro Gill', 'orden' => 1],
            ['partido' => '3m', 'nombre' => 'Dr. César Meza Bría', 'orden' => 1],
            ['partido' => 's21', 'nombre' => 'Freddy Ferreira', 'orden' => 1],
            ['partido' => 'da', 'nombre' => 'Dr. Osvaldo Valdebenito', 'orden' => 1],
        ];

        foreach ($candidatos as $c) {
            $partido = $movimientos[$c['partido']] ?? null;
            if ($partido) {
                Candidato::updateOrCreate(
                    [
                        'partido_id' => $partido->id,
                        'nombre_completo' => $c['nombre'],
                        'cargo' => 'intendente',
                    ],
                    [
                        'numero_orden' => $c['orden'],
                        'activo' => true,
                    ]
                );
            }
        }

        $this->command->info('Candidatos a intendentes cargados correctamente.');
    }
}
