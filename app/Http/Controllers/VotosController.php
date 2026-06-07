<?php

namespace App\Http\Controllers;

use App\Models\Voto;
use App\Models\Padron;
use App\Models\MiembroDeMesa;
use App\Models\Equipo;
use App\Models\LocalInterna;
use App\Models\Sistema;
use App\Models\CiudadElectoral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VotosController extends Controller
{
    // IMPORTANTE: Deshabilita el CSRF para pruebas (temporal)
    // public function __construct()
    // {
    //     $this->middleware('guest')->except(['cargarVotos', 'buscarPorCedula', 'buscarPorMesaYOrden', 'guardarVoto']);
    // }

    public function cargarVotos($cedula_encriptada)
    {
        try {
            $cedula = base64_decode(strtr($cedula_encriptada, '-_', '+/'));
            if ($cedula === false) {
                return view('cargar-votos-error', ['error' => 'No se puede abrir la página. El enlace no es válido.']);
            }
            // dd('paso');
            $miembro = MiembroDeMesa::where('cedula', $cedula)->with('equipo')->firstOrFail();
            $equipo = $miembro->equipo;

            $sistema = Sistema::find($equipo->sist);
            $ciudadElectoral = $sistema ? CiudadElectoral::find($sistema->id_ciudad_electoral) : null;
            $cantidadMesas = 0;

            $nombreLocal = $equipo->descripcion;

            if ($ciudadElectoral) {
                $localInterna = LocalInterna::where('distrito_nombre', $ciudadElectoral->descripcion)
                    ->where('departamento_nombre', $ciudadElectoral->departamento)
                    ->where('local_interna', $equipo->descripcion)
                    ->first();
                if ($localInterna) {
                    $cantidadMesas = (int) $localInterna->cantmesa;
                    $nombreLocal = $localInterna->local_interna;
                }
            }

            $maxMesaPadron = Padron::where('local_interna', $nombreLocal)
                ->max('mesa');
            if ($maxMesaPadron && $maxMesaPadron > $cantidadMesas) {
                $cantidadMesas = (int) $maxMesaPadron;
            }
            $votosCargados = Voto::where('idmiembrodemesa', $miembro->id)->count();

            $votosCargadosLista = Voto::where('idmiembrodemesa', $miembro->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return view('cargar-votos', compact(
                'miembro',
                'equipo',
                'cantidadMesas',
                'votosCargados',
                'cedula_encriptada',
                'votosCargadosLista'
            ));
        } catch (\Exception $e) {
            dd($e);
            Log::error('Error en cargarVotos: ' . $e->getMessage());
            return view('cargar-votos-error', ['error' => 'No se puede abrir la página. El enlace no es válido o ha expirado.']);
        }
    }

    public function buscarPorCedula(Request $request)
    {
        try {
            Log::info('Buscando por cédula: ' . $request->cedula);

            $request->validate([
                'cedula' => 'required|string',
                'miembro_id' => 'required|exists:miembros_de_mesa,id'
            ]);

            $votante = Padron::where('cedula', $request->cedula)->first();

            if (!$votante) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un votante con esa cédula'
                ]);
            }

            // Verificar si ya votó (solo como advertencia, no bloquea)
            $yaVoto = Voto::where('cedula', $votante->cedula)
                ->where('idmiembrodemesa', $request->miembro_id)
                ->exists();

            $message = $yaVoto ? 'Este votante ya registró su voto anteriormente' : null;

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'cedula' => $votante->cedula,
                    'nombres' => $votante->nombre ?? '',
                    'apellidos' => $votante->apellido ?? '',
                    'localvotacion' => $votante->local_interna ?? '',
                    'distrito' => $votante->distrito_nombre ?? '',
                    'mesa' => $votante->mesa ?? '',
                    'orden' => $votante->orden ?? ''
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en buscarPorCedula: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buscarPorMesaYOrden(Request $request)
    {
        try {
            Log::info('Buscando por mesa: ' . $request->mesa . ', orden: ' . $request->orden);

            $request->validate([
                'mesa' => 'required|integer',
                'orden' => 'required|integer',
                'miembro_id' => 'required|exists:miembros_de_mesa,id'
            ]);

            $miembro = MiembroDeMesa::with('equipo')->find($request->miembro_id);
            $equipo = $miembro->equipo;

            $nombreLocal = $equipo->descripcion;

            $sistema = Sistema::find($equipo->sist);
            $ciudadElectoral = $sistema ? CiudadElectoral::find($sistema->id_ciudad_electoral) : null;
            if ($ciudadElectoral) {
                $localInterna = LocalInterna::where('distrito_nombre', $ciudadElectoral->descripcion)
                    ->where('departamento_nombre', $ciudadElectoral->departamento)
                    ->where('local_interna', $equipo->descripcion)
                    ->first();
                if ($localInterna) {
                    $nombreLocal = $localInterna->local_interna;
                }
            }

            $query = Padron::where('mesa', 22)
                ->where('orden', 9)
                ->where('local_interna', 'ESC. Nº859 HEROES DE LA PATRIA');

            $sql = $query->toSql();
            $bindings = $query->getBindings();

            $votante = $query->first();

            if (!$votante) {
                return response()->json([
                    'success' => false,
                    'message' => "No se encontró un votante en la mesa {$request->mesa} con el orden {$request->orden}",
                    'debug' => [
                        'sql' => $sql,
                        'bindings' => $bindings,
                        'local_buscado' => $nombreLocal,
                        'mesa' => $request->mesa,
                        'orden' => $request->orden,
                    ]
                ]);
            }

            // Verificar si ya votó (solo como advertencia, no bloquea)
            $yaVoto = Voto::where('cedula', $votante->cedula)
                ->where('idmiembrodemesa', $request->miembro_id)
                ->exists();

            $message = $yaVoto ? 'Este votante ya registró su voto anteriormente' : null;

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'cedula' => $votante->cedula,
                    'nombres' => $votante->nombre ?? '',
                    'apellidos' => $votante->apellido ?? '',
                    'localvotacion' => $votante->local_interna ?? '',
                    'distrito' => $votante->distrito_nombre ?? '',
                    'mesa' => $votante->mesa ?? '',
                    'orden' => $votante->orden ?? ''
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en buscarPorMesaYOrden: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function guardarVoto(Request $request)
    {
        try {
            Log::info('Guardando voto - Datos recibidos:', $request->all());

            $request->validate([
                'cedula' => 'required|string',
                'nombres' => 'required|string',
                'apellidos' => 'required|string',
                'localvotacion' => 'required|string',
                'distrito' => 'required|string',
                'idmiembrodemesa' => 'required|exists:miembros_de_mesa,id',
                'mesa' => 'required|integer'
            ]);

            // Verificar si ya existe un voto con la misma cédula para este miembro
            $existe = Voto::where('cedula', $request->cedula)
                ->where('idmiembrodemesa', $request->idmiembrodemesa)
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este votante ya registró su voto anteriormente. No se permiten duplicados.'
                ]);
            }

            DB::beginTransaction();

            // Crear el voto
            $voto = Voto::create([
                'cedula' => $request->cedula,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'localvotacion' => $request->localvotacion,
                'distrito' => $request->distrito,
                'idmiembrodemesa' => $request->idmiembrodemesa
            ]);

            $totalVotos = Voto::where('idmiembrodemesa', $request->idmiembrodemesa)->count();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Voto registrado exitosamente',
                'total_votos' => $totalVotos,
                'voto' => [
                    'id' => $voto->id,
                    'cedula' => $voto->cedula,
                    'nombres' => $voto->nombres,
                    'apellidos' => $voto->apellidos,
                    'localvotacion' => $voto->localvotacion,
                    'distrito' => $voto->distrito,
                    'mesa' => $request->mesa,
                    'created_at' => $voto->created_at->format('d/m/Y H:i')
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en guardarVoto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el voto: ' . $e->getMessage()
            ], 500);
        }
    }

    // Agrega este método si falta
    public function estadisticasMesa($miembro_id)
    {
        try {
            $totalVotos = Voto::where('idmiembrodemesa', $miembro_id)->count();
            $votosPorMesa = Voto::where('idmiembrodemesa', $miembro_id)
                ->select('mesa', DB::raw('count(*) as total'))
                ->groupBy('mesa')
                ->get();

            return response()->json([
                'success' => true,
                'total_votos' => $totalVotos,
                'votos_por_mesa' => $votosPorMesa
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ]);
        }
    }
    public function eliminarVoto($id)
    {
        try {
            $voto = Voto::findOrFail($id);
            $miembroId = $voto->idmiembrodemesa;
            $voto->delete();

            $totalVotos = Voto::where('idmiembrodemesa', $miembroId)->count();

            return response()->json([
                'success' => true,
                'message' => 'Voto eliminado exitosamente',
                'total_votos' => $totalVotos
            ]);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el voto: ' . $e->getMessage()
            ], 500);
        }
    }
}
