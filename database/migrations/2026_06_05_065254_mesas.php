<?php
// database/migrations/2025_06_05_000001_create_mesas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_mesa', 100)->unique();
            $table->string('departamento', 100);
            $table->string('distrito', 100);
            $table->string('zona', 100)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->integer('numero_mesa')->nullable();
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index('codigo_mesa');
            $table->index(['departamento', 'distrito']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};