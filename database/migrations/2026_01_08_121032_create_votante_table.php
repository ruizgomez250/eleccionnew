<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votante', function (Blueprint $table) {
            $table->id(); // id (primary key)

            $table->string('cedula', 20)->index();
            $table->string('tipo_votante', 50)->nullable();
            $table->string('voto', 50)->nullable();

            $table->foreignId('idequipo')
                ->nullable()
                ->constrained('equipo')
                ->nullOnDelete();

            $table->foreignId('idpuntero')
                ->nullable()
                ->constrained('puntero')
                ->nullOnDelete();
            $table->foreignId('iddirigente')
                ->nullable()
                ->constrained('dirigente')
                ->nullOnDelete();
            $table->integer('idusuario')->nullable();

            $table->string('nombre', 150);
            $table->string('direccion', 255)->nullable();

            $table->string('mesa', 20)->nullable();
            $table->integer('orden')->nullable();

            $table->string('partido', 100)->nullable();
            $table->string('escuela', 150)->nullable();

            $table->string('ciudad', 100)->nullable();
            $table->string('departamento', 100)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votante');
    }
};
