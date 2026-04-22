<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('miembros_de_mesa', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 150);
            $table->string('telefono', 30)->nullable();
            $table->string('cedula', 20)->unique();
            $table->string('funcion', 150);
            $table->unsignedTinyInteger('mesa');
            $table->string('cedulaproponente', 20)->nullable();
            $table->string('nombreproponente', 100)->nullable();
            $table->string('telefonoproponente', 30)->nullable();
            // Relación con tabla equipo
            $table->unsignedBigInteger('idequipo');
            $table->foreign('idequipo')
                  ->references('id')
                  ->on('equipo')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('miembros_equipo');
    }
};