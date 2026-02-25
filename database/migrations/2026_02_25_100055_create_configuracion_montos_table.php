<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuracion_montos', function (Blueprint $table) {
            $table->id();
            $table->string('concepto')->unique(); // Punteros, Vehiculos, Miembros de Mesa
            $table->decimal('monto', 15, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('sistema_id')->nullable(); // null = aplica a todos los sistemas
            $table->foreign('sistema_id')->references('id')->on('sistemas')->onDelete('cascade');
            $table->timestamps();
        });

        // Insertar conceptos fijos iniciales
        DB::table('configuracion_montos')->insert([
            [
                'concepto' => 'Punteros',
                'monto' => 0,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'concepto' => 'Miembros de Mesa',
                'monto' => 0,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_montos');
    }
};
