<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partido;
use App\Models\Candidato;

class CandidatosConcejalesSeeder extends Seeder
{
    public function run(): void
    {
        $movimientos = [
            'nl' => Partido::where('numero_lista', '3')->first(),
            '3m' => Partido::where('numero_lista', '3M')->first(),
            'da' => Partido::where('numero_lista', '100')->first(),
        ];

        $lista3 = [
            ['nombre' => 'Francisca Franco', 'orden' => 1],
            ['nombre' => 'Dr. Jose Meza', 'orden' => 2],
            ['nombre' => 'Daysi Rolon', 'orden' => 3],
            ['nombre' => 'Marito Aguilera', 'orden' => 4],
            ['nombre' => 'Jose Troche', 'orden' => 5],
            ['nombre' => 'Julio Gomez', 'orden' => 6],
            ['nombre' => 'Hugo Segovia', 'orden' => 7],
            ['nombre' => 'Cesar Franco Coronel', 'orden' => 8],
            ['nombre' => 'Francisco Cabrera', 'orden' => 9],
            ['nombre' => 'Pedro Valdez', 'orden' => 10],
            ['nombre' => 'Claudio Gamarra', 'orden' => 11],
            ['nombre' => 'Jorge Roman', 'orden' => 12],
        ];

        $lista3m = [
            ['nombre' => 'Manu Morinigo', 'orden' => 1],
            ['nombre' => 'Santi Florentin', 'orden' => 2],
            ['nombre' => 'Rodrigo Rodriguez "Guacamayo luqueño"', 'orden' => 3],
            ['nombre' => 'Romulo Perez', 'orden' => 4],
            ['nombre' => 'Luz Borja', 'orden' => 5],
            ['nombre' => 'Teodoro Beby Trigo', 'orden' => 6],
            ['nombre' => 'Abog. Nestor Noceda', 'orden' => 7],
            ['nombre' => 'Lic. Juan Carlos Aguilera', 'orden' => 8],
            ['nombre' => 'Mati Maldonado', 'orden' => 9],
            ['nombre' => 'Abog. Sonia Rojas', 'orden' => 10],
            ['nombre' => 'Marce Avalos', 'orden' => 11],
            ['nombre' => 'Damian Espinola', 'orden' => 12],
        ];

        $lista100 = [
            ['nombre' => 'Sonia Ibarrola', 'orden' => 1],
            ['nombre' => 'Milciades Fariña', 'orden' => 2],
            ['nombre' => 'Liz Karina Rivas', 'orden' => 3],
            ['nombre' => 'Zully Escobar', 'orden' => 4],
            ['nombre' => 'Maricel Acosta', 'orden' => 5],
            ['nombre' => 'Rafael Ramirez', 'orden' => 6],
            ['nombre' => 'Felicita Ortega', 'orden' => 7],
            ['nombre' => 'Liz Quiroga', 'orden' => 8],
            ['nombre' => 'Betty Coronel', 'orden' => 9],
            ['nombre' => 'Juana Laurent de Ibarrola', 'orden' => 10],
            ['nombre' => 'Andres Sotelo', 'orden' => 11],
            ['nombre' => 'Ana Riveros', 'orden' => 12],
        ];

        $cargo = 'Concejal Municipal';

        foreach ($lista3 as $c) {
            if ($movimientos['nl']) {
                Candidato::updateOrCreate(
                    [
                        'partido_id' => $movimientos['nl']->id,
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

        foreach ($lista3m as $c) {
            if ($movimientos['3m']) {
                Candidato::updateOrCreate(
                    [
                        'partido_id' => $movimientos['3m']->id,
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

        foreach ($lista100 as $c) {
            if ($movimientos['da']) {
                Candidato::updateOrCreate(
                    [
                        'partido_id' => $movimientos['da']->id,
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

        $total = count($lista3) + count($lista3m) + count($lista100);
        $this->command->info("Candidatos a Concejal Municipal cargados correctamente. Total: {$total}");
    }
}
