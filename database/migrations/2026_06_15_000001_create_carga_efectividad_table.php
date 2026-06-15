<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carga_efectividad', function (Blueprint $table) {
            $table->id();
            $table->string('mesa', 255);
            $table->integer('intendente')->default(0);
            for ($i = 1; $i <= 12; $i++) {
                $table->integer("c{$i}")->default(0);
            }
            for ($i = 1; $i <= 12; $i++) {
                $table->integer("com{$i}")->default(0);
            }
            for ($i = 1; $i <= 12; $i++) {
                $table->integer("juv{$i}")->default(0);
            }
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carga_efectividad');
    }
};
