<?php
// database/migrations/2025_06_05_000004_create_votos_mesa_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votos_mesa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained()->onDelete('cascade');
            $table->foreignId('partido_id')->constrained()->onDelete('cascade');
            $table->foreignId('candidato_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('cargo', [
                'intendente',
                'Concejal Municipal',
                'presidente - vice 1 y vice 2 - plra',
            ]);
            $table->integer('cantidad_votos')->default(0)->unsigned();
            $table->enum('tipo_voto', ['lista', 'preferencia'])->default('lista');
            $table->timestamp('escaneado_en')->useCurrent();
            $table->string('escaneado_por', 100)->nullable(); // Usuario/veedor que escaneó
            $table->string('dispositivo_id', 100)->nullable(); // ID del dispositivo que escaneó
            $table->timestamps();
            
            // Índices compuestos para consultas rápidas
            $table->index(['mesa_id', 'partido_id', 'cargo']);
            $table->index(['cargo', 'tipo_voto']);
            $table->index('escaneado_en');
            
            // Evitar duplicados: misma mesa, partido, cargo y tipo_voto
            $table->unique(['mesa_id', 'partido_id', 'cargo', 'tipo_voto'], 'unique_voto_mesa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votos_mesa');
    }
};