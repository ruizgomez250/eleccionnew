<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('votos_mesa', function (Blueprint $table) {
            $table->dropUnique('unique_voto_mesa');
            $table->unique(['mesa_id', 'partido_id', 'cargo', 'candidato_id', 'tipo_voto'], 'unique_voto_mesa_full');
        });
    }

    public function down(): void
    {
        Schema::table('votos_mesa', function (Blueprint $table) {
            $table->dropUnique('unique_voto_mesa_full');
            $table->unique(['mesa_id', 'partido_id', 'cargo', 'tipo_voto'], 'unique_voto_mesa');
        });
    }
};
