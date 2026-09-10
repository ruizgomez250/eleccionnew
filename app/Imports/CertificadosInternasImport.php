<?php

namespace App\Imports;

use Illuminate\Database\Connection;
use RuntimeException;

class CertificadosInternasImport
{
    public const TABLES = ['equipo', 'locales_internas', 'partidos', 'mesas', 'candidatos', 'veedores', 'votos_mesa'];

    public function run(Connection $source, Connection $target, bool $checkOnly = false): array
    {
        if ($source->getDatabaseName() === $target->getDatabaseName()) {
            throw new RuntimeException('El origen debe ser una base separada de la base actual.');
        }
        $counts = [];
        foreach (self::TABLES as $table) {
            $destination = 'internas_'.$table;
            if (!$source->getSchemaBuilder()->hasTable($table) || !$target->getSchemaBuilder()->hasTable($destination)) {
                throw new RuntimeException("Falta la tabla de origen $table o el destino $destination.");
            }
            if ($target->table($destination)->exists()) {
                throw new RuntimeException("$destination ya contiene datos. La importación no reemplaza ni mezcla cargas existentes.");
            }
            $sourceColumns = $source->getSchemaBuilder()->getColumnListing($table);
            $targetColumns = array_diff($target->getSchemaBuilder()->getColumnListing($destination), ['usuario_origen_id']);
            if (array_diff($sourceColumns, $targetColumns) || array_diff($targetColumns, $sourceColumns)) {
                throw new RuntimeException("Las columnas de $table no coinciden con $destination.");
            }
            $counts[$table] = $source->table($table)->count();
        }
        // Verificar relaciones antes de escribir, también con --check.
        foreach ([
            ['mesas', 'equipo_id', 'equipo'], ['candidatos', 'partido_id', 'partidos'],
            ['veedores', 'partido_id', 'partidos'], ['votos_mesa', 'mesa_id', 'mesas'],
            ['votos_mesa', 'partido_id', 'partidos'], ['votos_mesa', 'candidato_id', 'candidatos'],
            ['votos_mesa', 'veedor_id', 'veedores'],
        ] as [$child, $column, $parent]) {
            if ($source->table($child.' as c')->leftJoin($parent.' as p', 'c.'.$column, '=', 'p.id')
                ->whereNotNull('c.'.$column)->whereNull('p.id')->exists()) {
                throw new RuntimeException("Existen referencias sin destino: $child.$column hacia $parent.");
            }
        }
        if ($checkOnly) {
            return $counts;
        }
        $target->transaction(function () use ($source, $target, $counts) {
            foreach (self::TABLES as $table) {
                $destination = 'internas_'.$table;
                // Una segunda comprobación evita sobreescribir una carga iniciada entretanto.
                if ($target->table($destination)->exists()) {
                    throw new RuntimeException("$destination dejó de estar vacía.");
                }
                $source->table($table)->orderBy('id')->chunkById(250, function ($rows) use ($table, $target, $destination) {
                    $values = $rows->map(function ($row) use ($table) {
                        $data = (array) $row;
                        if ($table === 'votos_mesa') {
                            $data['usuario_origen_id'] = $data['user_id'];
                            $data['user_id'] = null;
                        }
                        if ($table === 'veedores') {
                            $data['api_token'] = null;
                        }
                        return $data;
                    })->all();
                    $target->table($destination)->insert($values);
                });
                if ($target->table($destination)->count() !== $counts[$table]) {
                    throw new RuntimeException("El origen cambió durante la importación de $table. Usar un respaldo sin modificaciones.");
                }
            }
        });

        return $counts;
    }
}
