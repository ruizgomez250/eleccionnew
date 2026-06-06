<?php
// database/migrations/2025_06_05_000003_create_candidatos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidatos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partido_id')->constrained()->onDelete('cascade');
            $table->string('nombre_completo', 150);
            $table->string('documento', 20)->nullable();
            $table->integer('numero_orden'); // Posición en la lista (1,2,3...)
            $table->enum('cargo', [
                'intendente',
                'Concejal Municipal',
                'presidente - vice 1 y vice 2 - plra',
            ]);
            $table->string('foto_url')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            // Un candidato no puede repetirse en la misma lista y orden
            $table->unique(['partido_id', 'numero_orden', 'cargo']);
            
            $table->index('cargo');
            $table->index('partido_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidatos');
    }
};