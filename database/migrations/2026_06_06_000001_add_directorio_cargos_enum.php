<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE candidatos MODIFY COLUMN cargo ENUM('intendente', 'Concejal Municipal', 'presidente - vice 1 y vice 2 - plra', 'directorio nacional', 'directorio departamental')");
        DB::statement("ALTER TABLE votos_mesa MODIFY COLUMN cargo ENUM('intendente', 'Concejal Municipal', 'presidente - vice 1 y vice 2 - plra', 'directorio nacional', 'directorio departamental')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE candidatos MODIFY COLUMN cargo ENUM('intendente', 'Concejal Municipal', 'presidente - vice 1 y vice 2 - plra')");
        DB::statement("ALTER TABLE votos_mesa MODIFY COLUMN cargo ENUM('intendente', 'Concejal Municipal', 'presidente - vice 1 y vice 2 - plra')");
    }
};
