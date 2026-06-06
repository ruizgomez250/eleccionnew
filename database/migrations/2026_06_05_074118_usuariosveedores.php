<?php
// database/migrations/2025_06_05_000005_create_veedores_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('documento', 20)->unique();
            $table->string('email', 150)->unique();
            $table->string('telefono', 20)->nullable();
            $table->foreignId('partido_id')->constrained()->onDelete('cascade');
            $table->string('api_token', 80)->unique()->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Agregar foreign key a votos_mesa
        Schema::table('votos_mesa', function (Blueprint $table) {
            $table->foreignId('veedor_id')
                ->nullable()
                ->after('escaneado_por')
                ->constrained('veedores')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('votos_mesa', function (Blueprint $table) {
            $table->dropForeign(['veedor_id']);
            $table->dropColumn('veedor_id');
        });
        Schema::dropIfExists('veedores');
    }
};
