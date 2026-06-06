<?php
// database/migrations/2025_06_05_000001_add_user_origen_to_votos_mesa_table.php

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
        // Verificar si la tabla existe
        if (!Schema::hasTable('votos_mesa')) {
            return;
        }
        
        Schema::table('votos_mesa', function (Blueprint $table) {
            // === AGREGAR COLUMNA user_id ===
            if (!Schema::hasColumn('votos_mesa', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('veedor_id')
                    ->constrained('users')
                    ->onDelete('set null');
            }
            
            // === AGREGAR COLUMNA origen ===
            if (!Schema::hasColumn('votos_mesa', 'origen')) {
                $table->enum('origen', ['apk', 'web'])
                    ->default('apk')
                    ->after('tipo_voto');
            }
        });
        
        // === MODIFICAR veedor_id para que sea nullable (usando SQL directo) ===
        // Esto es más seguro cuando la columna ya tiene foreign key
        
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // Para MySQL
            DB::statement('ALTER TABLE `votos_mesa` MODIFY `veedor_id` BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            // Para PostgreSQL
            DB::statement('ALTER TABLE "votos_mesa" ALTER COLUMN "veedor_id" DROP NOT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite no soporta modificar columnas fácilmente, se requiere recrear la tabla
            // En SQLite, es mejor dejar como está
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('votos_mesa', function (Blueprint $table) {
            // Eliminar columna user_id
            if (Schema::hasColumn('votos_mesa', 'user_id')) {
                // Primero eliminar la foreign key constraint
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // Si no existe la FK, continuamos
                }
                $table->dropColumn('user_id');
            }
            
            // Eliminar columna origen
            if (Schema::hasColumn('votos_mesa', 'origen')) {
                $table->dropColumn('origen');
            }
        });
    }
};