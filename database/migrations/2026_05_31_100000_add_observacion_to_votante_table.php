<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('votante', function (Blueprint $table) {
            $table->string('observacion', 500)->nullable()->after('departamento');
        });
    }

    public function down(): void
    {
        Schema::table('votante', function (Blueprint $table) {
            $table->dropColumn('observacion');
        });
    }
};
