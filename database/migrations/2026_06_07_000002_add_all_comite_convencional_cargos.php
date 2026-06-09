<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $cargos = "'intendente', 'Concejal Municipal', 'presidente - vice 1 y vice 2 - plra', 'directorio nacional', 'directorio departamental', 'comite 1 local', 'comite 2 local', 'comite 3 local', 'comite 4 local', 'convencional', 'convencional 1', 'convencional 2', 'convencional 3', 'convencional 4'";

        DB::statement("ALTER TABLE candidatos MODIFY COLUMN cargo ENUM({$cargos})");
        DB::statement("ALTER TABLE votos_mesa MODIFY COLUMN cargo ENUM({$cargos})");
    }

    public function down(): void
    {
        $cargos = "'intendente', 'Concejal Municipal', 'presidente - vice 1 y vice 2 - plra', 'directorio nacional', 'directorio departamental', 'comite 1 local', 'convencional'";
        DB::statement("ALTER TABLE candidatos MODIFY COLUMN cargo ENUM({$cargos})");
        DB::statement("ALTER TABLE votos_mesa MODIFY COLUMN cargo ENUM({$cargos})");
    }
};
