<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $cargos = "'intendente', 'Concejal Municipal', 'presidente - vice 1 y vice 2 - plra', 'directorio nacional', 'directorio departamental', 'comite 1 local', 'comite 2 local', 'comite 3 local', 'comite 4 local', 'convencional', 'convencional 1', 'convencional 2', 'convencional 3', 'convencional 4', 'comite 1', 'comite 2', 'comite 3', 'comite 4', 'comite 5', 'comite 6', 'comite 7', 'comite 8', 'comite 9', 'comite 10', 'comite 11', 'comite 12', 'juventud 1', 'juventud 2', 'juventud 3', 'juventud 4', 'juventud 5', 'juventud 6', 'juventud 7', 'juventud 8', 'juventud 9', 'juventud 10', 'juventud 11', 'juventud 12'";

        DB::statement("ALTER TABLE candidatos MODIFY COLUMN cargo ENUM({$cargos})");
        DB::statement("ALTER TABLE votos_mesa MODIFY COLUMN cargo ENUM({$cargos})");
    }

    public function down(): void
    {
        $cargos = "'intendente', 'Concejal Municipal', 'presidente - vice 1 y vice 2 - plra', 'directorio nacional', 'directorio departamental', 'comite 1 local', 'comite 2 local', 'comite 3 local', 'comite 4 local', 'convencional', 'convencional 1', 'convencional 2', 'convencional 3', 'convencional 4'";
        DB::statement("ALTER TABLE candidatos MODIFY COLUMN cargo ENUM({$cargos})");
        DB::statement("ALTER TABLE votos_mesa MODIFY COLUMN cargo ENUM({$cargos})");
    }
};
