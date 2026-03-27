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
        Schema::create('candidatura_niveles', function (Blueprint $table) {
            $table->id(); // Campo id autoincrementable
            $table->string('descripcion', 255); // Campo descripcion tipo string
            $table->integer('nivel'); // Campo nivel tipo numérico
            $table->timestamps(); // Campos created_at y updated_at (opcional)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatura_niveles');
    }
};