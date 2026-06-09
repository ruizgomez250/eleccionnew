<?php

namespace App\Console\Commands;

use App\Models\Partido;
use App\Models\Candidato;
use Illuminate\Console\Command;

class CargarCandidaturas extends Command
{
    protected $signature = 'candidaturas:cargar {--delete-existing : Eliminar candidatos existentes primero}';
    protected $description = 'Carga candidaturas desde candidaturas.txt';

    public function handle()
    {
        $file = database_path('seeders/candidaturas.txt');
        if (!file_exists($file)) {
            $file = app_path('Models/candidaturas.txt');
        }
        if (!file_exists($file)) {
            $this->error("Archivo candidaturas.txt no encontrado");
            return 1;
        }

        if ($this->option('delete-existing')) {
            $this->warn("Eliminando candidatos existentes...");
            Candidato::query()->delete();
            $this->info("Candidatos existentes eliminados.");
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $cargoActual = null;
        $partidoActual = null;
        $sectionCounts = [];
        $ordenEsperado = 1;
        $contador = 0;

        foreach ($lines as $line) {
            $line = trim($line);

            // Secciones: varias formas
            if (preg_match('/^(?:candidatos?\s+a|listas?\s+.*?cargo\s+de)\s+(.+)$/i', $line, $m)) {
                $baseCargo = trim($m[1]);
                // Si ya tiene número (comite 1 local, convencional 3), mantener
                // Si no (solo "convencional"), auto-numerar
                if (preg_match('/^(?:comite\s+\d+\s+local|convencional\s+\d+)$/i', $baseCargo)) {
                    $cargoActual = $baseCargo;
                } else {
                    if (!isset($sectionCounts[$baseCargo])) {
                        $sectionCounts[$baseCargo] = 1;
                    } else {
                        $sectionCounts[$baseCargo]++;
                    }
                    $cargoActual = $baseCargo . ' ' . $sectionCounts[$baseCargo];
                }
                $this->info("Sección: {$cargoActual}");
                $ordenEsperado = 1;
                continue;
            }

            // Definición de lista con nombre: "lista N nombre [sigla]"
            if (preg_match('/^lista\s+(\S+)\s+(.+)$/i', $line, $m)) {
                $numeroLista = $m[1];
                $rest = trim($m[2]);

                // Detectar "3 meza" como "3meza" (sin espacio)
                if (preg_match('/^meza\s+(.+)/i', $rest, $mRest)) {
                    $numeroLista = '3meza';
                    $rest = $mRest[1];
                }

                $nombrePartido = ucwords($rest);
                $sigla = null;

                if (preg_match('/^(.+)\s+(\S+)$/', $rest, $m2)) {
                    $restName = $m2[1];
                    $lastWord = $m2[2];
                    if (strlen($lastWord) <= 6
                        && preg_match('/\s/', $restName)
                        && preg_match('/^[a-zA-Záéíóú]+$/u', $lastWord)) {
                        $sigla = strtoupper($lastWord);
                        $nombrePartido = ucwords($restName);
                    }
                }

                $partidoActual = Partido::updateOrCreate(
                    ['numero_lista' => $numeroLista],
                    [
                        'nombre' => $nombrePartido,
                        'sigla' => $sigla,
                        'activo' => true,
                    ]
                );
                $this->line("  Lista {$numeroLista}: {$nombrePartido}" . ($sigla ? " ({$sigla})" : ""));
                $ordenEsperado = 1;
                continue;
            }

            // Lista sin nombre (ej: "lista 100")
            if (preg_match('/^lista\s+(\S+)$/i', $line, $m)) {
                $numeroLista = $m[1];
                $partidoActual = Partido::where('numero_lista', $numeroLista)->first();
                if ($partidoActual) {
                    $this->line("  Lista {$numeroLista}: (existente)");
                } else {
                    $this->warn("  Lista {$numeroLista} no encontrada, buscando por nombre...");
                    $partidoActual = Partido::where('nombre', 'like', "%{$numeroLista}%")->first();
                    if ($partidoActual) {
                        $this->line("  -> Asociado a Lista {$partidoActual->numero_lista}: {$partidoActual->nombre}");
                    } else {
                        $this->warn("  Lista {$numeroLista} omitida");
                    }
                }
                $ordenEsperado = 1;
                continue;
            }

            // Opción con formato "opcion N nombre"
            if (preg_match('/^opcion\s+(\d+)\s*(.+)$/i', $line, $m)) {
                $orden = (int) $m[1];
                $nombre = trim(ucwords(mb_strtolower($m[2])));
                $ordenEsperado = $orden + 1;

                if ($this->guardarCandidato($partidoActual, $cargoActual, $orden, $nombre, $line)) {
                    $contador++;
                }
                continue;
            }

            // Línea suelta sin "opcion" (ej: nombre solo) - asignar orden correlativo
            if ($partidoActual && $cargoActual && !preg_match('/^(?:lista|candidatos|listas)/i', $line)) {
                $nombre = trim(ucwords(mb_strtolower($line)));
                if ($this->guardarCandidato($partidoActual, $cargoActual, $ordenEsperado, $nombre, $line)) {
                    $contador++;
                    $ordenEsperado++;
                }
                continue;
            }

            $this->warn("Línea no reconocida: {$line}");
        }

        $this->info("Candidaturas cargadas correctamente. Total: {$contador}");
        return 0;
    }

    private function guardarCandidato($partido, $cargo, $orden, $nombre, $lineOriginal)
    {
        if (!$partido || !$cargo) {
            $this->warn("  Ignorado (sin lista o cargo): {$lineOriginal}");
            return false;
        }

        if (!$nombre) {
            $this->warn("  Ignorado (sin nombre): {$lineOriginal}");
            return false;
        }

        Candidato::updateOrCreate(
            [
                'partido_id' => $partido->id,
                'numero_orden' => $orden,
                'cargo' => $cargo,
            ],
            [
                'nombre_completo' => $nombre,
                'activo' => true,
            ]
        );
        return true;
    }
}
