<?php

namespace App\Console\Commands;

use App\Imports\CertificadosInternasImport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportarCertificadosInternas extends Command
{
    protected $signature = 'internas:importar {base_origen : Base separada con el respaldo de internas} {--check : Verificar sin copiar datos}';
    protected $description = 'Importa certificados en tablas internas_ vacías, conservando IDs y relaciones';

    public function handle(CertificadosInternasImport $importer): int
    {
        $target = DB::connection();
        if ($target->getDriverName() !== 'mysql') {
            $this->error('Se requiere una conexión MySQL/MariaDB.');
            return self::FAILURE;
        }
        $sourceConfig = $target->getConfig();
        $sourceConfig['database'] = $this->argument('base_origen');
        $sourceConfig['url'] = null;
        config(['database.connections.internas_import_source' => $sourceConfig]);
        DB::purge('internas_import_source');
        try {
            $counts = $importer->run(DB::connection('internas_import_source'), $target, (bool) $this->option('check'));
            $this->table(['Tabla de origen', 'Destino', 'Registros'], collect($counts)->map(fn ($count, $table) => [$table, 'internas_'.$table, $count])->values()->all());
            $this->info($this->option('check') ? 'Comprobación correcta. No se copiaron datos.' : 'Importación completada. Las tablas actuales no fueron modificadas.');
            return self::SUCCESS;
        } catch (\Illuminate\Database\QueryException $e) {
            $this->error('Importación cancelada por la base de datos (SQLSTATE '.$e->getCode().'). Revisar permisos, tipos de columnas y claves; no se publica el contenido de los registros.');
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        } finally {
            DB::disconnect('internas_import_source');
        }
    }
}
