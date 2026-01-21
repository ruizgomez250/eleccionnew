<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dirigente', function (Blueprint $table) {
            $table->id();

            $table->string('cedula', 20)->index();
            $table->string('nombre', 150);
            $table->string('telefono', 20)->nullable();
            $table->string('telefono1', 20)->nullable();
            $table->string('telefono2', 20)->nullable();

            $table->foreignId('id_equipo')
                  ->nullable()
                  ->constrained('equipo')
                  ->nullOnDelete();

            $table->string('barrio', 100)->nullable();
            $table->integer('idusuario')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dirigente');
    }
};
