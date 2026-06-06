<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            if (!Schema::hasColumn('mesas', 'equipo_id')) {
                $table->foreignId('equipo_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('equipo')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mesas', function (Blueprint $table) {
            if (Schema::hasColumn('mesas', 'equipo_id')) {
                $table->dropForeign(['equipo_id']);
                $table->dropColumn('equipo_id');
            }
        });
    }
};
