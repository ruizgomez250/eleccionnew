<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('puntero_vehiculo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('puntero_id')->constrained('puntero')->onDelete('cascade');
            $table->foreignId('vehiculo_id')->constrained('vehiculo')->onDelete('cascade');
            $table->date('fecha_asignacion')->nullable(); // opcional: para saber cuándo se asignó
            $table->timestamps();

            $table->unique(['puntero_id', 'vehiculo_id']); // Evita duplicados
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puntero_vehiculo');
    }
};
