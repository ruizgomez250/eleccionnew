<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE candidatos MODIFY COLUMN cargo ENUM('intendente', 'Concejal Municipal', 'presidente - vice 1 y vice 2 - plra')");
        DB::statement("ALTER TABLE votos_mesa MODIFY COLUMN cargo ENUM('intendente', 'Concejal Municipal', 'presidente - vice 1 y vice 2 - plra')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE candidatos MODIFY COLUMN cargo ENUM('presidente', 'vicepresidente', 'senador', 'diputado', 'gobernador', 'intendente', 'concejal', 'junta_departamental')");
        DB::statement("ALTER TABLE votos_mesa MODIFY COLUMN cargo ENUM('presidente', 'vicepresidente', 'senador', 'diputado', 'gobernador', 'intendente', 'concejal', 'junta_departamental')");
    }
};
