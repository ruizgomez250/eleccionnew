<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['equipo', 'locales_internas', 'partidos', 'mesas', 'candidatos', 'veedores', 'votos_mesa'];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            throw new RuntimeException('La estructura de internas requiere MySQL/MariaDB.');
        }
        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table) || Schema::hasTable('internas_'.$table)) {
                throw new RuntimeException("Revisar estructura: se requiere $table y un destino internas_$table inexistente.");
            }
        }
        $created = [];
        try {
            foreach ($this->tables as $table) {
                DB::statement("CREATE TABLE `internas_$table` LIKE `$table`");
                $created[] = 'internas_'.$table;
                DB::statement("ALTER TABLE `internas_$table` ENGINE=InnoDB");
            }
            Schema::table('internas_votos_mesa', function (Blueprint $table) {
                $table->unsignedBigInteger('usuario_origen_id')->nullable();
            });
            foreach ([
                ['mesas', 'equipo_id', 'equipo'],
                ['candidatos', 'partido_id', 'partidos'],
                ['veedores', 'partido_id', 'partidos'],
                ['votos_mesa', 'mesa_id', 'mesas'],
                ['votos_mesa', 'partido_id', 'partidos'],
                ['votos_mesa', 'candidato_id', 'candidatos'],
                ['votos_mesa', 'veedor_id', 'veedores'],
            ] as [$child, $column, $parent]) {
                Schema::table('internas_'.$child, function (Blueprint $table) use ($child, $column, $parent) {
                    $table->foreign($column, 'int_'.$child.'_'.$column.'_fk')
                        ->references('id')->on('internas_'.$parent)->restrictOnDelete();
                });
            }
            Schema::table('internas_votos_mesa', function (Blueprint $table) {
                $table->foreign('user_id', 'int_votos_user_fk')->references('id')->on('users')->nullOnDelete();
            });
        } catch (Throwable $e) {
            foreach (array_reverse($created) as $table) {
                Schema::dropIfExists($table);
            }
            throw $e;
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            Schema::dropIfExists('internas_'.$table);
        }
    }
};
