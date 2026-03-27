<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sistemaspadres', function (Blueprint $table) {
            $table->id(); // Campo id autoincrementable
            $table->unsignedBigInteger('idsistema'); // ID del sistema
            $table->unsignedBigInteger('idsistemapadre')->nullable(); // ID del sistema padre, puede ser NULL
            $table->timestamps(); // Campos created_at y updated_at (opcional)
            
            // Opcional: agregar índices para mejorar el rendimiento
            $table->index('idsistema');
            $table->index('idsistemapadre');
            
            // Opcional: si quieres agregar una clave foránea (asumiendo que existe una tabla sistemas)
            // $table->foreign('idsistema')->references('id')->on('sistemas')->onDelete('cascade');
            // $table->foreign('idsistemapadre')->references('id')->on('sistemas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sistemaspadres');
    }
};