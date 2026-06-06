<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partido;
use App\Models\Candidato;

class CandidatosNacionalesPLRASeeder extends Seeder
{
    public function run(): void
    {
        $movimientos = [
            'cl' => Partido::where('numero_lista', '1')->first(),
            'nl' => Partido::where('numero_lista', '3')->first(),
            'fr' => Partido::where('numero_lista', '9')->first(),
            'cpc' => Partido::where('numero_lista', '13')->first(),
            's21' => Partido::where('numero_lista', '21')->first(),
            'ldl' => Partido::where('numero_lista', '45')->first(),
            'da' => Partido::where('numero_lista', '100')->first(),
        ];

        $cargo = 'presidente - vice 1 y vice 2 - plra';

        $candidatos = [
            // Coherencia Liberal - Lista 1
            ['mov' => 'cl', 'nombre' => 'Marlene "Chispita" Orué', 'orden' => 1],
            ['mov' => 'cl', 'nombre' => 'Luz Borja', 'orden' => 2],
            ['mov' => 'cl', 'nombre' => 'Fernando Pfannl', 'orden' => 3],
            // Nuevo Liberalismo - Lista 3
            ['mov' => 'nl', 'nombre' => 'Alcides Riveros', 'orden' => 1],
            ['mov' => 'nl', 'nombre' => 'Antonio Buzarquis', 'orden' => 2],
            ['mov' => 'nl', 'nombre' => 'Julia Rivas', 'orden' => 3],
            // Frente Radical - Lista 9
            ['mov' => 'fr', 'nombre' => 'Ever Villalba', 'orden' => 1],
            ['mov' => 'fr', 'nombre' => 'Efraín Alegre hijo', 'orden' => 2],
            ['mov' => 'fr', 'nombre' => 'Zulma María Ycassatti viuda de Acevedo', 'orden' => 3],
            // Cambio para el Cambio - Lista 13
            ['mov' => 'cpc', 'nombre' => 'Alfredo Jaeggli', 'orden' => 1],
            ['mov' => 'cpc', 'nombre' => 'Víctor Pavón', 'orden' => 2],
            ['mov' => 'cpc', 'nombre' => 'Gisselle Vázquez', 'orden' => 3],
            // Siglo 21 - Lista 21
            ['mov' => 's21', 'nombre' => 'Dr. Sevoi', 'orden' => 1],
            ['mov' => 's21', 'nombre' => 'Felipe Fernández', 'orden' => 2],
            ['mov' => 's21', 'nombre' => 'Carmen Salinas', 'orden' => 3],
            // La Derecha Liberal - Lista 45
            ['mov' => 'ldl', 'nombre' => 'Abel Villalba', 'orden' => 1],
            ['mov' => 'ldl', 'nombre' => 'Eduardo Villalba', 'orden' => 2],
            ['mov' => 'ldl', 'nombre' => 'Derlis Santachiz', 'orden' => 3],
            // Diálogo Azul - Lista 100
            ['mov' => 'da', 'nombre' => 'Senador Dionisio Amarilla', 'orden' => 1],
            ['mov' => 'da', 'nombre' => 'Noelia Cabrera Petters', 'orden' => 2],
            ['mov' => 'da', 'nombre' => 'Clementino Portillo', 'orden' => 3],
        ];

        foreach ($candidatos as $c) {
            $partido = $movimientos[$c['mov']] ?? null;
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

        $this->command->info('Candidatos a presidente - vice 1 y vice 2 - plra cargados correctamente. Total: ' . count($candidatos));
    }
}
