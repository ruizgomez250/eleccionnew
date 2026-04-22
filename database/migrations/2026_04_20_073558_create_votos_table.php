<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votos', function (Blueprint $table) {
            $table->id();
            $table->string('cedula', 20);
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('localvotacion')->nullable();
            $table->string('distrito')->nullable();
            $table->unsignedBigInteger('idmiembrodemesa')->nullable();
            $table->timestamps();

            // Si luego querés relacionar con otra tabla:
            // $table->foreign('idmiembrodemesa')->references('id')->on('miembro_de_mesas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votos');
    }
};
