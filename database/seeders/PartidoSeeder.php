<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Partido;

class PartidoSeeder extends Seeder
{
    public function run(): void
    {
        $partidos = [
            [
                'numero_lista' => '1',
                'nombre' => 'Coherencia Liberal',
                'sigla' => 'CL',
                'color_hex' => '#FF69B4',
                'activo' => true,
            ],
            [
                'numero_lista' => '3',
                'nombre' => 'Nuevo Liberalismo Unidad para la Victoria',
                'sigla' => 'NLUPLV',
                'color_hex' => '#0000FF',
                'activo' => true,
            ],
            [
                'numero_lista' => '3M',
                'nombre' => 'Nuevo Liberalismo Unidad para la Victoria 3M',
                'sigla' => 'NLUPLV',
                'color_hex' => '#0033CC',
                'activo' => true,
            ],
            [
                'numero_lista' => '9',
                'nombre' => 'Frente Radical',
                'sigla' => 'FR',
                'color_hex' => '#FF4500',
                'activo' => true,
            ],
            [
                'numero_lista' => '13',
                'nombre' => 'Cambio para el Cambio',
                'sigla' => 'CPC',
                'color_hex' => '#00CED1',
                'activo' => true,
            ],
            [
                'numero_lista' => '21',
                'nombre' => 'Siglo 21',
                'sigla' => 'S21',
                'color_hex' => '#228B22',
                'activo' => true,
            ],
            [
                'numero_lista' => '25',
                'nombre' => 'Oñondivepa',
                'sigla' => 'MLO',
                'color_hex' => '#FFD700',
                'activo' => true,
            ],
            [
                'numero_lista' => '45',
                'nombre' => 'La Derecha Liberal',
                'sigla' => 'LDL',
                'color_hex' => '#8B4513',
                'activo' => true,
            ],
            [
                'numero_lista' => '100',
                'nombre' => 'Diálogo Azul',
                'sigla' => 'DA',
                'color_hex' => '#1E90FF',
                'activo' => true,
            ],
            [
                'numero_lista' => '9998',
                'nombre' => 'VOTO EN BLANCO',
                'sigla' => 'BLANCO',
                'color_hex' => '#CCCCCC',
                'activo' => true,
            ],
        ];

        foreach ($partidos as $partido) {
            Partido::updateOrCreate(
                ['numero_lista' => $partido['numero_lista']],
                $partido
            );
        }

        $this->command->info('Movimientos y partidos cargados correctamente.');
    }
}
