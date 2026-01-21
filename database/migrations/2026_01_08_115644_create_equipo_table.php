<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 150);
            $table->string('sist', 50)->nullable();
            $table->string('colegio', 150)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo');
    }
};
