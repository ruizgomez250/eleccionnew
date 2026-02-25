<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfiguracionMonto;
use App\Models\MiembroDeMesa;
use App\Models\Puntero;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\Auth;

class ConfiguracionMontoController extends Controller
{
    public function __construct()
    {
        // Solo usuarios autenticados
        $this->middleware('auth');

        // Permisos específicos: solo quienes tengan 'Configuracion de montos' pueden acceder a estas acciones
        $this->middleware('permission:Configuracion de montos', [
            'only' => ['index', 'store', 'createWithEquipo', 'destroy', 'reporteGeneral']
        ]);
    }

    /**
     * Mostrar la lista de montos por sistema
     */
    public function index(Request $request)
    {
        $sistemaUsuario = Auth::user()->sistema;

        // Trae solo montos de su sistema o globales (sistema_id = null)
        $montos = ConfiguracionMonto::where('sistema_id', $sistemaUsuario)
            ->orWhereNull('sistema_id')
            ->orderBy('concepto')
            ->get();

        return view('configuracion_montos', compact('montos'));
    }

    /**
     * Guardar o actualizar un monto
     */
    public function store(Request $request)
    {
        $request->validate([
            'monto_id' => 'required|exists:configuracion_montos,id',
            'monto' => 'required|numeric|min:0',
        ]);

        $sistemaUsuario = Auth::user()->sistema;

        // Solo puede editar montos de su sistema o montos globales
        $monto = ConfiguracionMonto::where('id', $request->monto_id)
            ->where(function ($q) use ($sistemaUsuario) {
                $q->where('sistema_id', $sistemaUsuario)
                    ->orWhereNull('sistema_id');
            })
            ->firstOrFail();

        $monto->monto = $request->monto;
        $monto->save();

        return redirect()->back()->with('successAlert', 'Monto actualizado correctamente');
    }
    public function reporteGeneral()
    {
        $sistemaUsuario = Auth::user()->sistema;

        // Obtener configuraciones activas
        $configMontos = ConfiguracionMonto::activos()
            ->where(function ($q) use ($sistemaUsuario) {
                $q->where('sistema_id', $sistemaUsuario)
                    ->orWhereNull('sistema_id');
            })
            ->get()
            ->keyBy('concepto'); // Para acceder por concepto

        // Calcular cantidades por concepto
        $cantidadPunteros = Puntero::whereHas('equipo', fn($q) => $q->where('sist', $sistemaUsuario))->count();
        $cantidadVehiculos = Vehiculo::whereHas('equipo', fn($q) => $q->where('sist', $sistemaUsuario))->count();
        $cantidadMiembros = MiembroDeMesa::whereHas('equipo', fn($q) => $q->where('sist', $sistemaUsuario))->count();

        $reporte = [];

        // Punteros
        if (isset($configMontos['Punteros'])) {
            $montoPuntero = $configMontos['Punteros']->monto;
            $reporte[] = [
                'sistema' => $sistemaUsuario,
                'concepto' => 'Punteros',
                'cantidad' => $cantidadPunteros,
                'monto_unitario' => $montoPuntero,
                'total_presupuestado' => $montoPuntero * $cantidadPunteros,
            ];
        }

        // Vehículos
        $totalMontoVehiculos = Vehiculo::whereHas('equipo', fn($q) => $q->where('sist', $sistemaUsuario))
            ->sum('montopagar');
        $reporte[] = [
            'sistema' => $sistemaUsuario,
            'concepto' => 'Vehiculos',
            'cantidad' => $cantidadVehiculos,
            'monto_unitario' => null,
            'total_presupuestado' => $totalMontoVehiculos,
        ];

        // Miembros de Mesa
        if (isset($configMontos['Miembros de Mesa'])) {
            $montoMiembro = $configMontos['Miembros de Mesa']->monto;
            $reporte[] = [
                'sistema' => $sistemaUsuario,
                'concepto' => 'Miembros de Mesa',
                'cantidad' => $cantidadMiembros,
                'monto_unitario' => $montoMiembro,
                'total_presupuestado' => $montoMiembro * $cantidadMiembros,
            ];
        }

        return response()->json($reporte);
    }
}
