<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PartidoSeeder::class);
        $this->call(CandidatosIntendentesSeeder::class);
        $this->call(CandidatosConcejalesSeeder::class);
        $this->call(CandidatosNacionalesPLRASeeder::class);
        $this->call(CandidatosDirectorioNacionalSeeder::class);
        $this->call(CandidatosDirectorioDepartamentalSeeder::class);

        $this->command->info('Todos los seeders se ejecutaron correctamente.');
    }
}
