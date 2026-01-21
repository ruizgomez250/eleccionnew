<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculo', function (Blueprint $table) {
            $table->id(); // id

            $table->string('cedulachofer', 20)->index();
            $table->string('chapa', 20)->index();

            $table->string('tipovehiculo', 50)->nullable();
            $table->integer('capacidad')->nullable();

            $table->string('telefono1', 20)->nullable();
            $table->string('telefono2', 20)->nullable();
            $table->string('telefono3', 20)->nullable();

            $table->decimal('montopagar', 12, 2)->default(0);
            $table->integer('cantidadpagos')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo');
    }
};

