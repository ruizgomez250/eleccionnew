<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visita_puntero', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idpuntero')->nullable()->constrained('puntero')->nullOnDelete();
            $table->string('cedula', 20)->index();
            $table->string('nombre_votante', 150);
            $table->string('apellido_votante', 150)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('casa_de', 150)->nullable()->comment('Ej: Casa de Juan Perez');
            $table->string('cedula_votante', 20)->nullable();
            $table->string('observacion', 500)->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->dateTime('fecha_visita');
            $table->string('resultado', 100)->default('neutro')->comment('Texto libre: positivo, negativo, neutro, etc');
            $table->dateTime('proxima_visita')->nullable();
            $table->decimal('precision_gps', 10, 2)->nullable();
            $table->string('referencia', 255)->nullable()->comment('Referencia de la casa/zona');
            $table->integer('idusuario')->nullable()->comment('Usuario que carga el dato');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visita_puntero');
    }
};
