<?php
// database/migrations/2025_06_05_000002_create_partidos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_lista', 20)->unique();
            $table->string('nombre', 150);
            $table->string('sigla', 20)->nullable();
            $table->string('color_hex', 10)->nullable(); // Para UI, ej: "#FF0000"
            $table->string('logo_url')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('numero_lista');
            $table->index('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidos');
    }
};