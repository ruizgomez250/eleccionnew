{{-- resources/views/arbol/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Árbol de Candidaturas')

@section('content_header')
    <h1 class="m-0">
        <i class="fas fa-sitemap text-primary"></i> Árbol de Candidaturas por Distrito
    </h1>
@stop

@section('content')

    {{-- BUSCADOR --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-primary">
                        <i class="fas fa-search text-white"></i>
                    </span>
                </div>
                <input type="text" id="buscadorArbol" class="form-control"
                    placeholder="Buscar distrito, candidatura, intendente, concejal...">
            </div>
            <div class="mt-2">
                <button type="button" id="expandirTodos" class="btn btn-sm btn-success">
                    <i class="fas fa-expand-alt"></i> Expandir Todos
                </button>
                <button type="button" id="colapsarTodos" class="btn btn-sm btn-secondary">
                    <i class="fas fa-compress-alt"></i> Colapsar Todos
                </button>
            </div>
        </div>
    </div>

    {{-- ÁRBOL JERÁRQUICO --}}
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-sitemap"></i> Estructura Jerárquica por Distrito
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            @if (isset($arbolJerarquico) && count($arbolJerarquico) > 0)
                <div class="tree">
                    <ul class="tree-root">
                        @include('arbol.partials.tree-nodes', ['nodes' => $arbolJerarquico, 'nivel' => 0])
                    </ul>
                </div>
            @else
                <div class="alert alert-info text-center py-5">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <p>No hay candidaturas registradas para mostrar</p>
                </div>
            @endif
        </div>
    </div>

    {{-- MODALES (igual que antes) --}}
    <!-- Modal de Dirigentes -->
    <div class="modal fade" id="modalDirigentes" tabindex="-1" role="dialog" aria-labelledby="tituloDirigentes"
        aria-hidden="true">
        {{-- Modal que ocupa casi toda la pantalla --}}
        <div class="modal-dialog modal-xl" style="max-width: 98%; width: 98%; margin: 10px auto;">
            <div class="modal-content" style="height: 98vh; max-height: 98vh;">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="tituloDirigentes">
                        <i class="fas fa-users"></i> Dirigentes
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                {{-- Body con AMBOS scrolls (horizontal y vertical) --}}
                <div class="modal-body" id="contenidoDirigentes" style="overflow: auto; height: calc(98vh - 120px);">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando dirigentes...</p>
                    </div>
                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fas fa-arrow-left"></i>Volver Atras
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- MODAL PUNTEROS (lista completa) --}}
    <div class="modal fade" id="modalPunterosLista" tabindex="-1" role="dialog" aria-labelledby="tituloPunterosLista"
        aria-hidden="true">
        {{-- Modal que ocupa casi toda la pantalla --}}
        <div class="modal-dialog modal-xl" style="max-width: 98%; width: 98%; margin: 10px auto;">
            <div class="modal-content" style="height: 98vh; max-height: 98vh;">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="tituloPunterosLista">
                        <i class="fas fa-users"></i> Punteros
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                {{-- Body con AMBOS scrolls (horizontal y vertical) --}}
                <div class="modal-body" id="contenidoPunteros" style="overflow: auto; height: calc(98vh - 120px);">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Selecciona una opción para ver los punteros...</p>
                    </div>
                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fas fa-arrow-left"></i>Volver Atras
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- MODAL DE VEHÍCULOS DEL PUNTERO --}}
    <div class="modal fade" id="modalVehiculosPuntero" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-truck"></i> Vehículos del Puntero: <span id="vehiculo_puntero_nombre"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    {{-- FORMULARIO PARA CREAR NUEVO VEHÍCULO --}}
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-plus-circle"></i> Nuevo Vehículo
                        </div>
                        <div class="card-body">
                            <form id="formCrearVehiculoPuntero">
                                @csrf
                                <input type="hidden" name="id_puntero" id="vehiculo_id_puntero">

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Cédula del Chofer <span class="text-danger">*</span></label>
                                            <input type="text" name="cedulachofer" id="vehiculo_cedulachofer"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Nombre del Chofer <span class="text-danger">*</span></label>
                                            <input type="text" name="nombre" id="vehiculo_nombre"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Chapa <span class="text-danger">*</span></label>
                                            <input type="text" name="chapa" id="vehiculo_chapa"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Tipo Vehículo</label>
                                            <select name="tipovehiculo" id="vehiculo_tipovehiculo" class="form-control">
                                                <option value="AUTOMOVIL">AUTOMÓVIL</option>
                                                <option value="CAMIONETA">CAMIONETA</option>
                                                <option value="FURGONETA">FURGONETA</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Capacidad</label>
                                            <input type="number" name="capacidad" id="vehiculo_capacidad"
                                                class="form-control" value="5">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Teléfono Principal <span class="text-danger">*</span></label>
                                            <input type="text" name="telefono1" id="vehiculo_telefono1"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Teléfono Secundario</label>
                                            <input type="text" name="telefono2" id="vehiculo_telefono2"
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Monto a Pagar (Gs.)</label>
                                            <select name="montopagar" id="vehiculo_montopagar" class="form-control">
                                                <option value="0">0</option>
                                                <option value="200000">200.000</option>
                                                <option value="300000" selected>300.000</option>
                                                <option value="350000">350.000</option>
                                                <option value="400000">400.000</option>
                                                <option value="450000">450.000</option>
                                                <option value="500000">500.000</option>
                                                <option value="550000">550.000</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Cantidad Pagos</label>
                                            <input type="number" name="cantidadpagos" id="vehiculo_cantidadpagos"
                                                class="form-control" value="2">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Rol del Vehículo</label>
                                            <select name="rolvehiculo" id="vehiculo_rolvehiculo" class="form-control">
                                                <option value="PUNTERO">PUNTERO</option>
                                                <option value="LOGISTICA">LOGISTICA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- Espacio vacío --}}
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-save"></i> Guardar Vehículo
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- TABLA DE VEHÍCULOS ASIGNADOS --}}
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <i class="fas fa-list"></i> Vehículos Asignados
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="vehiculos-puntero-table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Chapa</th>
                                            <th>Chofer</th>
                                            <th>Cédula</th>
                                            <th>Teléfono</th>
                                            <th>Rol</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="7" class="text-center">Seleccione un puntero</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fas fa-arrow-left"></i> Volver Atrás
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- MODAL VOTANTES --}}
    <div class="modal fade" id="modalVotantes" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="tituloVotantes">
                        <i class="fas fa-users"></i> Votantes del Puntero
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="p-4">
                    <form id="formAgregarVotante">
                        @csrf
                        <input type="hidden" name="idpuntero" id="votante_id_puntero">
                        <input type="hidden" name="idusuario" value="{{ auth()->id() }}">

                        <div class="row mb-2">
                            <div class="col-md-3">
                                <input name="cedula" id="votante_cedula" class="form-control" placeholder="Cédula"
                                    required>
                            </div>
                            <div class="col-md-5">
                                <input name="nombre" id="votante_nombre" class="form-control" placeholder="Nombre"
                                    required readonly>
                            </div>
                            <div class="col-md-4">
                                <select name="tipo_votante" class="form-control" id="tipo_votante">
                                    <option value="seguro" selected>Seguro</option>
                                    <option value="dudoso">Dudoso</option>
                                    <option value="solo visita">Solo Visita</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-4">
                                <input name="direccion" id="direccion" class="form-control" placeholder="Dirección"
                                    readonly>
                            </div>
                            <div class="col-md-2">
                                <input name="mesa" id="mesa" class="form-control" placeholder="Mesa" readonly>
                            </div>
                            <div class="col-md-2">
                                <input name="orden" id="orden" class="form-control" placeholder="Orden" readonly>
                            </div>
                            <div class="col-md-4">
                                <input name="partido" id="partido" class="form-control" placeholder="Partido"
                                    readonly>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-4">
                                <input name="escuela" id="escuela" class="form-control" placeholder="Escuela"
                                    readonly>
                            </div>
                            <div class="col-md-4">
                                <input name="ciudad" id="ciudad" class="form-control" placeholder="Ciudad" readonly>
                            </div>
                            <div class="col-md-4">
                                <input name="departamento" id="departamento" class="form-control"
                                    placeholder="Departamento" readonly>
                            </div>
                        </div>

                        {{-- 👇 BOTONES ORGANIZADOS EN UNA FILA --}}
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Votante
                                </button>


                            </div>
                            <div class="col-md-6 text-right">


                                <button type="button" class="btn btn-danger ml-2" data-dismiss="modal">
                                    <i class="fas fa-arrow-left"></i> Volver a Punteros
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
                <div class="modal-body" id="contenidoVotantes">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando votantes...</p>
                    </div>
                </div>
                <div class="modal-footer">

                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fas fa-arrow-left"></i>Volver a Punteros
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* ============================================
                                                                                                                               ESTILOS BASE DEL ÁRBOL
                                                                                                                            ============================================ */
        .tree {
            min-height: 20px;
            padding: 19px;
            margin-bottom: 20px;
            background-color: #f5f5f5;
            border: 1px solid #e3e3e3;
            border-radius: 4px;
            position: relative;
        }

        /* Listas del árbol */
        .tree ul {
            padding-left: 30px;
            list-style: none;
            margin: 0;
        }

        /* Ocultar listas colapsadas */
        .tree ul.child-list {
            display: none;
        }

        /* Elementos de lista */
        .tree li {
            position: relative;
            padding-top: 5px;
            padding-bottom: 5px;
            padding-left: 20px;
            box-sizing: border-box;
            list-style: none;
        }

        /* Líneas verticales de conexión */
        .tree li:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 1px;
            border-left: 1px solid #ccc;
        }

        /* Líneas horizontales de conexión */
        .tree li:after {
            content: "";
            position: absolute;
            top: 25px;
            left: 0;
            width: 20px;
            height: 1px;
            border-top: 1px solid #ccc;
        }

        /* Último elemento - ajustar línea vertical */
        .tree li:last-child:before {
            height: 25px;
        }

        /* Nodos del árbol */
        .tree-node {
            cursor: pointer;
            margin: 5px 0;
            transition: all 0.3s ease;
            position: relative;
        }

        .tree-node:hover {
            transform: translateX(5px);
        }

        .tree-node .card {
            border-left: 4px solid #007bff;
            transition: all 0.3s ease;
            margin-bottom: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .tree-node .card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .tree-node .card-header {
            background-color: #fff;
            border-bottom: none;
            padding: 12px 15px;
        }

        /* ============================================
                                                                                                                               ICONOS DE EXPANDIR/COLAPSAR
                                                                                                                            ============================================ */
        .toggle-icon {
            cursor: pointer;
            margin-right: 10px;
            font-size: 14px;
            color: #6c757d;
            transition: all 0.2s ease;
            display: inline-block;
            width: 20px;
            text-align: center;
            font-weight: bold;
        }

        .toggle-icon:hover {
            color: #007bff;
            transform: scale(1.1);
        }

        .toggle-icon.expanded {
            color: #007bff;
        }

        .toggle-icon.collapsed {
            color: #6c757d;
        }

        .toggle-icon-placeholder {
            display: inline-block;
            width: 20px;
            margin-right: 10px;
        }

        /* ============================================
                                                                                                                               ESTILOS PARA DISTRITOS (NIVEL PRINCIPAL)
                                                                                                                            ============================================ */
        .level-distrito {
            margin-bottom: 20px;
        }

        .level-distrito .card {
            border-left-color: #007bff;
            border-left-width: 5px;
        }

        .level-distrito .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
        }

        .level-distrito .card-header * {
            color: white !important;
        }

        .level-distrito .card-header .badge {
            background-color: rgba(255, 255, 255, 0.2);
            color: white !important;
            border: none;
        }

        .level-distrito .card-header .border-top {
            border-top-color: rgba(255, 255, 255, 0.3) !important;
        }

        .level-distrito .toggle-icon {
            color: white !important;
        }

        .level-distrito .toggle-icon:hover {
            color: #ffc107 !important;
            transform: scale(1.1);
        }

        /* ============================================
                                                                                                                               ESTILOS POR TIPO DE CANDIDATURA
                                                                                                                            ============================================ */
        /* Intendente */
        .level-intendente .card {
            border-left-color: #dc3545;
            border-left-width: 4px;
        }

        .level-intendente .card-header {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
        }

        /* Concejal */
        .level-concejal .card {
            border-left-color: #28a745;
            border-left-width: 4px;
        }

        .level-concejal .card-header {
            background: linear-gradient(135deg, #f0fff4 0%, #e6ffe6 100%);
        }

        /* Convencional */
        .level-convencional .card {
            border-left-color: #17a2b8;
            border-left-width: 4px;
        }

        .level-convencional .card-header {
            background: linear-gradient(135deg, #e6f7ff 0%, #d4f0ff 100%);
        }

        /* Convencional Juventud */
        .level-convencional_juventud .card {
            border-left-color: #ffc107;
            border-left-width: 4px;
        }

        .level-convencional_juventud .card-header {
            background: linear-gradient(135deg, #fff9e6 0%, #fff3d4 100%);
        }

        /* Miembro de Comité */
        .level-miembro_comite .card {
            border-left-color: #fd7e14;
            border-left-width: 4px;
        }

        .level-miembro_comite .card-header {
            background: linear-gradient(135deg, #fff4e6 0%, #ffe8d4 100%);
        }

        /* Miembro de la Juventud */
        .level-miembro_juventud .card {
            border-left-color: #6f42c1;
            border-left-width: 4px;
        }

        .level-miembro_juventud .card-header {
            background: linear-gradient(135deg, #f4e6ff 0%, #e8d4ff 100%);
        }

        /* ============================================
                                                                                                                               BADGES Y ETIQUETAS
                                                                                                                            ============================================ */
        .badge-nivel {
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge-orange {
            background-color: #fd7e14;
            color: white;
        }

        .badge-purple {
            background-color: #6f42c1;
            color: white;
        }

        /* ============================================
                                                                                                                               ESTADÍSTICAS DEL DISTRITO
                                                                                                                            ============================================ */
        .stats-distrito {
            font-size: 0.75rem;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .stats-distrito .badge {
            font-size: 0.7rem;
            padding: 5px 10px;
        }

        /* ============================================
                                                                                                                               EFECTOS Y ANIMACIONES
                                                                                                                            ============================================ */
        /* Animación para expandir/colapsar */
        .tree ul.child-list {
            transition: all 0.3s ease-in-out;
            overflow: hidden;
        }

        /* Hover en tarjetas */
        .tree-node .card {
            transition: all 0.3s ease;
        }

        .tree-node:hover .card {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        /* Animación de entrada para nuevos nodos */
        @keyframes fadeInSlide {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tree li {
            animation: fadeInSlide 0.3s ease-out;
        }

        /* ============================================
                                                                                                                               RESALTADO DE BÚSQUEDA
                                                                                                                            ============================================ */
        .nodo-buscar {
            position: relative;
        }

        .nodo-buscar .card {
            background-color: #fff3cd !important;
            border-left-color: #ffc107 !important;
            box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.5);
            animation: pulse 0.5s ease-out;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
            }
        }

        /* ============================================
                                                                                                                               CLASES UTILITARIAS
                                                                                                                            ============================================ */
        .border-top {
            border-top: 1px solid #dee2e6 !important;
        }

        .mr-3 {
            margin-right: 1rem !important;
        }

        .mb-1 {
            margin-bottom: 0.25rem !important;
        }

        .mt-2 {
            margin-top: 0.5rem !important;
        }

        .mt-3 {
            margin-top: 1rem !important;
        }

        .pt-2 {
            padding-top: 0.5rem !important;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .text-warning-light {
            color: #ffc107 !important;
        }

        /* ============================================
                                                                                                                               RESPONSIVE
                                                                                                                            ============================================ */
        @media (max-width: 768px) {
            .tree {
                padding: 10px;
                overflow-x: auto;
            }

            .tree ul {
                padding-left: 15px;
            }

            .tree li {
                padding-left: 10px;
            }

            .stats-distrito {
                flex-direction: column;
                gap: 4px;
            }

            .stats-distrito .badge {
                display: inline-block;
                margin: 2px;
            }

            .tree-node .card-header {
                padding: 8px 10px;
            }

            .tree-node .card-title {
                font-size: 14px;
            }

            .toggle-icon {
                font-size: 12px;
                width: 16px;
            }
        }

        /* ============================================
                                                                                                                               SCROLLBAR PERSONALIZADA
                                                                                                                            ============================================ */
        .tree::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .tree::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .tree::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .tree::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* ============================================
                                                                                                                               ESTILOS PARA MODALES
                                                                                                                            ============================================ */
        .modal-xl {
            max-width: 98% !important;
            width: 98% !important;
            margin: 10px auto !important;
        }

        .modal-content {
            border-radius: 12px;
            overflow: hidden;
        }

        .modal-header {
            border-bottom: none;
        }

        .modal-footer {
            border-top: none;
        }

        /* ============================================
                                                                                                                               ESTILOS PARA TABLAS EN MODALES
                                                                                                                            ============================================ */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        .table tbody tr:hover {
            background-color: #f5f5f5;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            
            // Desactivar autofocus en modales para evitar warnings
            $.fn.modal.Constructor.Default.focusOnShow = false;

            // Función para expandir/colapsar un nodo específico
            function toggleNode($li) {
                var $ul = $li.children('ul.child-list');
                var $icon = $li.children('.tree-node').find('.toggle-icon');

                if ($ul.length === 0) return false;

                if ($ul.is(':visible')) {
                    $ul.slideUp(200);
                    $icon.text('▶').removeClass('expanded').addClass('collapsed');
                } else {
                    $ul.slideDown(200);
                    $icon.text('▼').removeClass('collapsed').addClass('expanded');
                }
                return true;
            }

            function collapseAllNodes() {
                $('.tree li > ul.child-list').each(function() {
                    var $thisUl = $(this);
                    if ($thisUl.is(':visible')) {
                        $thisUl.hide();
                    }
                });
                $('.toggle-icon').each(function() {
                    $(this).text('▶').removeClass('expanded').addClass('collapsed');
                });
            }

            function expandOnlyDistricts() {
                $('.tree-root > li').each(function() {
                    var $li = $(this);
                    var $ul = $li.children('ul.child-list');
                    var $icon = $li.children('.tree-node').find('.toggle-icon');

                    if ($ul.length > 0 && !$ul.is(':visible')) {
                        $ul.show();
                        $icon.text('▼').removeClass('collapsed').addClass('expanded');
                    }
                });
            }

            // Inicializar: colapsar todos los nodos
            collapseAllNodes();

            // Configurar eventos para cada nodo
            $('.tree li').each(function() {
                var $li = $(this);
                var $ul = $li.children('ul.child-list');
                var $node = $li.children('.tree-node');

                if ($ul.length > 0) {
                    var $icon = $node.find('.toggle-icon');
                    if ($icon.length === 0) {
                        $icon = $('<span class="toggle-icon collapsed">▶</span>');
                        $node.find('.card-title').before($icon);
                    } else {
                        $icon.text('▶').removeClass('expanded').addClass('collapsed');
                    }

                    $icon.off('click').on('click', function(e) {
                        e.stopPropagation();
                        toggleNode($li);
                        return false;
                    });

                    $node.off('click').on('click', function(e) {
                        if ($(e.target).hasClass('toggle-icon') || $(e.target).closest(
                                '.toggle-icon').length) {
                            return;
                        }
                        var sistemaId = $(this).data('id');
                        var sistemaTipo = $(this).data('tipo');

                        if ($ul.length > 0) {
                            toggleNode($li);
                        } else if (sistemaId && sistemaTipo !== 'Distrito') {
                            abrirModalDirigentes(sistemaId, $(this).find('.card-title').text(),
                                sistemaTipo);
                        }
                    });
                } else {
                    $node.off('click').on('click', function(e) {
                        e.stopPropagation();
                        var sistemaId = $(this).data('id');
                        var sistemaTipo = $(this).data('tipo');
                        if (sistemaId && sistemaTipo !== 'Distrito') {
                            abrirModalDirigentes(sistemaId, $(this).find('.card-title').text(),
                                sistemaTipo);
                        }
                    });
                }
            });

            $('#expandirTodos').off('click').on('click', function() {
                expandOnlyDistricts();
            });

            $('#colapsarTodos').off('click').on('click', function() {
                collapseAllNodes();
            });

            // Buscador
            $('#buscadorArbol').off('keyup').on('keyup', function() {
                let query = $(this).val().toLowerCase().trim();
                let encontrados = 0;

                if (query === '') {
                    collapseAllNodes();
                    expandOnlyDistricts();
                    $('.tree-node').show();
                    $('.tree-node').removeClass('nodo-buscar');
                    return;
                }

                collapseAllNodes();

                $('.tree-node').each(function() {
                    let nombre = $(this).find('.card-title').text().toLowerCase();
                    let tipo = $(this).find('.badge-nivel').text().toLowerCase();
                    let textoCompleto = $(this).text().toLowerCase();

                    if (nombre.includes(query) || tipo.includes(query) || textoCompleto.includes(
                            query)) {
                        $(this).show();
                        $(this).addClass('nodo-buscar');
                        encontrados++;

                        $(this).parents('li').each(function() {
                            var $parentLi = $(this);
                            var $parentUl = $parentLi.children('ul.child-list');
                            var $parentIcon = $parentLi.children('.tree-node').find(
                                '.toggle-icon');

                            if ($parentUl.length > 0 && !$parentUl.is(':visible')) {
                                $parentUl.show();
                                $parentIcon.text('▼').removeClass('collapsed').addClass(
                                    'expanded');
                            }
                        });
                    } else {
                        $(this).hide();
                        $(this).removeClass('nodo-buscar');
                    }
                });

                if (encontrados === 0 && query !== '') {
                    if ($('#sinResultadosArbol').length === 0) {
                        $('.tree').append(`
                            <div id="sinResultadosArbol" class="alert alert-warning text-center py-3 mt-3">
                                <i class="fas fa-search"></i> No se encontraron resultados que coincidan con "${query}"
                            </div>
                        `);
                    }
                } else {
                    $('#sinResultadosArbol').remove();
                }
            });

            
            // ==================== CARGAR VEHÍCULOS DEL PUNTERO ====================
            window.cargarVehiculosPuntero = function(punteroId) {
                $('#vehiculos-puntero-table tbody').html(
                    '<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando vehículos...</td></tr>'
                );

                $.get(`{{ url('/') }}/puntero/${punteroId}/vehiculos`, function(vehiculos) {
                    let tbody = '';

                    if (vehiculos.length === 0) {
                        tbody =
                            '<tr><td colspan="7" class="text-center">No hay vehículos asignados a este puntero</td></tr>';
                    } else {
                        vehiculos.forEach((v, i) => {
                            tbody += `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${v.chapa}</td>
                        <td>${v.nombre}</td>
                        <td>${v.cedulachofer}</td>
                        <td>${v.telefono1 || ''}</td>
                        <td><span class="badge badge-${v.rol === 'PUNTERO' ? 'primary' : 'secondary'}">${v.rol}</span></td>
                        <td class="text-center">
                            <button class="btn btn-danger btn-sm" onclick="eliminarVehiculoPuntero(${v.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                        });
                    }

                    $('#vehiculos-puntero-table tbody').html(tbody);

                    // Destruir DataTable si existe y volver a inicializar
                    if ($.fn.DataTable && $('#vehiculos-puntero-table').length) {
                        if ($.fn.DataTable.isDataTable('#vehiculos-puntero-table')) {
                            $('#vehiculos-puntero-table').DataTable().destroy();
                        }
                        $('#vehiculos-puntero-table').DataTable({
                            responsive: true,
                            language: {
                                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            },
                            pageLength: 10,
                            destroy: true
                        });
                    }
                }).fail(function() {
                    $('#vehiculos-puntero-table tbody').html(
                        '<tr><td colspan="7" class="text-center text-danger">Error al cargar los vehículos</td></tr>'
                    );
                });
            }
            // ==================== ELIMINAR VEHÍCULO DEL PUNTERO ====================
            window.eliminarVehiculoPuntero = function(vehiculoId) {
                let punteroId = $('#vehiculo_id_puntero').val();

                Swal.fire({
                    title: '¿Desvincular vehículo?',
                    text: 'El vehículo dejará de estar asignado a este puntero',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Sí, desvincular',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('/') }}/vehiculo/${vehiculoId}/puntero/${punteroId}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Desvinculado',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                cargarVehiculosPuntero(punteroId);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message ||
                                        'No se pudo desvincular el vehículo'
                                });
                            }
                        });
                    }
                });
            }
            // ============================================
            // FUNCIONES PARA MODALES
            // ============================================
            window.abrirModalCrearVehiculo = function(punteroId, punteroNombre) {
                $('#vehiculo_id_puntero').val(punteroId);
                $('#vehiculo_puntero_nombre').text(punteroNombre);

                // Limpiar formulario
                $('#formCrearVehiculoPuntero')[0].reset();
                $('#vehiculo_capacidad').val(5);
                $('#vehiculo_cantidadpagos').val(2);
                $('#vehiculo_montopagar').val('300000');
                $('#vehiculo_tipovehiculo').val('AUTOMOVIL');
                $('#vehiculo_rolvehiculo').val('PUNTERO');

                // Limpiar campos específicos
                $('#vehiculo_cedulachofer').val('');
                $('#vehiculo_nombre').val('');
                $('#vehiculo_telefono1').val('');
                $('#vehiculo_telefono2').val('');
                $('#vehiculo_chapa').val('');

                // Cargar vehículos del puntero
                cargarVehiculosPuntero(punteroId);

                // Enfocar primer campo
                setTimeout(function() {
                    $('#vehiculo_cedulachofer').focus();
                }, 500);

                $('#modalVehiculosPuntero').modal('show');
            }
            window.abrirModalDirigentes = function(sistemaId, nombre, tipo) {
                $('#tituloDirigentes').html(`
                    <i class="fas fa-users"></i> Dirigentes - ${tipo}: ${nombre}
                `);
                $('#contenidoDirigentes').html(`
                    <div class="text-center p-4">
                        <div class="spinner-border text-warning mb-3" style="width: 3rem; height: 3rem;"></div>
                        <p>Cargando dirigentes...</p>
                    </div>
                `);
                $('#modalDirigentes').modal('show');

                let url = `{{ url('/') }}/sistemas/${sistemaId}/dirigentes`;

                $.get(url, function(data) {
                    $('#contenidoDirigentes').html(data);
                    setTimeout(function() {
                        inicializarTablaDirigentes();
                    }, 200);
                }).fail(function() {
                    $('#contenidoDirigentes').html(`
                        <div class="alert alert-danger text-center p-4">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p>Error al cargar los dirigentes.</p>
                            <button class="btn btn-sm btn-outline-danger mt-2" onclick="abrirModalDirigentes(${sistemaId}, '${nombre}', '${tipo}')">
                                <i class="fas fa-sync"></i> Reintentar
                            </button>
                        </div>
                    `);
                });
            };

            window.abrirModalPunterosLista = function(sistemaId, nombreSistema) {
                $('#tituloPunterosLista').html(`
                    <i class="fas fa-users"></i> Punteros del Sistema: ${nombreSistema}
                `);
                $('#contenidoPunteros').html(`
                    <div class="text-center p-4">
                        <div class="spinner-border text-info mb-3" style="width: 3rem; height: 3rem;"></div>
                        <p>Cargando punteros...</p>
                    </div>
                `);
                $('#modalPunterosLista').modal('show');

                let url = `{{ url('/') }}/sistemas/${sistemaId}/punteros`;

                $.get(url, function(data) {
                    $('#contenidoPunteros').html(data);
                    setTimeout(function() {
                        inicializarTablaPunteros();
                    }, 200);
                }).fail(function() {
                    $('#contenidoPunteros').html(`
                        <div class="alert alert-danger text-center p-4">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p>Error al cargar los punteros.</p>
                            <button class="btn btn-sm btn-outline-danger mt-2" onclick="abrirModalPunterosLista(${sistemaId}, '${nombreSistema}')">
                                <i class="fas fa-sync"></i> Reintentar
                            </button>
                        </div>
                    `);
                });
            };

            window.abrirModalPunterosListapordir = function(idDir, nombreSistema) {
                $('#tituloPunterosLista').html(`
                    <i class="fas fa-users"></i> Punteros del Dirigente: ${nombreSistema}
                `);
                $('#contenidoPunteros').html(`
                    <div class="text-center p-4">
                        <div class="spinner-border text-info mb-3" style="width: 3rem; height: 3rem;"></div>
                        <p>Cargando punteros...</p>
                    </div>
                `);
                $('#modalPunterosLista').modal('show');

                let url = `{{ url('/') }}/dirigente/${idDir}/punteros`;

                $.get(url, function(data) {
                    $('#contenidoPunteros').html(data);
                    setTimeout(function() {
                        inicializarTablaPunteros();
                    }, 200);
                }).fail(function() {
                    $('#contenidoPunteros').html(`
                        <div class="alert alert-danger text-center p-4">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p>Error al cargar los punteros.</p>
                            <button class="btn btn-sm btn-outline-danger mt-2" onclick="abrirModalPunterosListapordir(${idDir}, '${nombreSistema}')">
                                <i class="fas fa-sync"></i> Reintentar
                            </button>
                        </div>
                    `);
                });
            };

            window.cargarVotantes = function(punteroId, nombrePuntero) {
                $('#tituloVotantes').html(`
                    <i class="fas fa-users"></i> Votantes del Puntero: ${nombrePuntero}
                `);
                $('#contenidoVotantes').html(`
                    <div class="text-center p-4">
                        <div class="spinner-border text-success mb-3" style="width: 3rem; height: 3rem;"></div>
                        <p>Cargando votantes...</p>
                    </div>
                `);
                $('#modalVotantes').modal('show');

                let url = `{{ url('puntero') }}/${punteroId}/votantes`;

                $.get(url, function(data) {
                    let html = `
                        <div class="table-responsive">
                            <table id="votantes-table" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cédula</th>
                                        <th>Nombre</th>
                                        <th>Tipo Votante</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    if (data.length === 0) {
                        html +=
                            `<tr><td colspan="5" class="text-center">No hay votantes registrados</td></tr>`;
                    } else {
                        data.forEach((v, i) => {
                            html += `
                                <tr>
                                    <td>${i + 1}</td>
                                    <td>${v.cedula}</td>
                                    <td>${v.nombre || ''}</td>
                                    <td><span class="badge badge-info">${v.tipo_votante || ''}</span></td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" onclick="eliminarVotante(${v.id}, '${nombrePuntero}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    }

                    html += `</tbody></table></div>`;
                    $('#contenidoVotantes').html(html);

                    setTimeout(function() {
                        inicializarTablaVotantes();
                    }, 200);
                }).fail(function() {
                    $('#contenidoVotantes').html(`
                        <div class="alert alert-danger text-center p-4">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p>Error al cargar los votantes.</p>
                            <button class="btn btn-sm btn-outline-danger mt-2" onclick="cargarVotantes(${punteroId}, '${nombrePuntero}')">
                                <i class="fas fa-sync"></i> Reintentar
                            </button>
                        </div>
                    `);
                });
            };

            window.eliminarVotante = function(id, nombrePuntero) {
                Swal.fire({
                    title: '¿Eliminar votante?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('votante/delete') }}/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire('Eliminado', 'Votante eliminado correctamente',
                                    'success');
                                let punteroId = $('#votante_id_puntero').val();
                                if (punteroId) {
                                    cargarVotantes(punteroId, nombrePuntero);
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo eliminar el votante',
                                    'error');
                            }
                        });
                    }
                });
            };

            // ============================================
            // FUNCIONES PARA INICIALIZAR DATATABLES
            // ============================================

            function inicializarTablaDirigentes() {
                setTimeout(function() {
                    var $tabla = $('#dirigentes-table');

                    if (!$tabla.length) return;
                    if ($tabla.find('thead').length === 0) return;
                    if (!$.fn.DataTable) return;

                    if ($.fn.DataTable.isDataTable('#dirigentes-table')) {
                        $('#dirigentes-table').DataTable().destroy();
                    }

                    try {
                        $('#dirigentes-table').DataTable({
                            responsive: true,
                            dom: "<'row'<'col-md-4'l><'col-md-4'f><'col-md-4 text-right'B>>" +
                                "<'row'<'col-sm-12'tr>>" +
                                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                            buttons: [{
                                extend: 'print',
                                text: '<i class="fas fa-print"></i> Imprimir',
                                className: 'btn btn-secondary',
                                autoPrint: true,
                                customize: function(win) {
                                    $(win.document.body).find('table').addClass(
                                        'table table-bordered');
                                }
                            }],
                            language: {
                                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            },
                            pageLength: 10,
                            lengthMenu: [
                                [5, 10, 25, 50, -1],
                                [5, 10, 25, 50, "Todos"]
                            ],
                            destroy: true
                        });
                    } catch (e) {
                        console.error('Error inicializando DataTable dirigentes:', e);
                    }
                }, 100);
            }

            function inicializarTablaPunteros() {
                setTimeout(function() {
                    var $tabla = $('#punteros-lista-table');

                    if (!$tabla.length) return;
                    if ($tabla.find('thead').length === 0) return;
                    if (!$.fn.DataTable) return;

                    if ($.fn.DataTable.isDataTable('#punteros-lista-table')) {
                        $('#punteros-lista-table').DataTable().destroy();
                    }

                    try {
                        $('#punteros-lista-table').DataTable({
                            responsive: true,
                            dom: "<'row'<'col-md-4'l><'col-md-4'f><'col-md-4 text-right'B>>" +
                                "<'row'<'col-sm-12'tr>>" +
                                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                            buttons: [{
                                extend: 'print',
                                text: '<i class="fas fa-print"></i> Imprimir',
                                className: 'btn btn-secondary'
                            }],
                            language: {
                                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            },
                            pageLength: 10,
                            destroy: true
                        });
                    } catch (e) {
                        console.error('Error inicializando DataTable punteros:', e);
                    }
                }, 100);
            }

            function inicializarTablaVotantes() {
                setTimeout(function() {
                    var $tabla = $('#votantes-table');

                    if (!$tabla.length) return;
                    if ($tabla.find('thead').length === 0) return;
                    if (!$.fn.DataTable) return;

                    if ($.fn.DataTable.isDataTable('#votantes-table')) {
                        $('#votantes-table').DataTable().destroy();
                    }

                    try {
                        $('#votantes-table').DataTable({
                            responsive: true,
                            dom: "<'row'<'col-md-4'l><'col-md-4'f><'col-md-4 text-right'B>>" +
                                "<'row'<'col-sm-12'tr>>" +
                                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                            buttons: [{
                                extend: 'print',
                                text: '<i class="fas fa-print"></i> Imprimir',
                                className: 'btn btn-secondary'
                            }],
                            language: {
                                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            },
                            pageLength: 10,
                            destroy: true
                        });
                    } catch (e) {
                        console.error('Error inicializando DataTable votantes:', e);
                    }
                }, 100);
            }

            // ============================================
            // LIMPIAR DATATABLES AL CERRAR MODALES
            // ============================================

            $('#modalDirigentes').on('hidden.bs.modal', function() {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#dirigentes-table')) {
                    $('#dirigentes-table').DataTable().destroy();
                }
                $('#contenidoDirigentes').empty();
            });

            $('#modalPunterosLista').on('hidden.bs.modal', function() {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#punteros-lista-table')) {
                    $('#punteros-lista-table').DataTable().destroy();
                }
                $('#contenidoPunteros').empty();
            });

            $('#modalVotantes').on('hidden.bs.modal', function() {
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#votantes-table')) {
                    $('#votantes-table').DataTable().destroy();
                }
                $('#contenidoVotantes').empty();
                limpiarFormularioVotante();
            });

            function limpiarFormularioVotante() {
                $('#votante_cedula').val('');
                $('#votante_nombre').val('');
                $('#direccion').val('');
                $('#mesa').val('');
                $('#orden').val('');
                $('#partido').val('');
                $('#escuela').val('');
                $('#ciudad').val('');
                $('#departamento').val('');
                $('#tipo_votante').val('seguro');
            }
        });
        

        // Eventos para búsqueda por cédula
        $(document).on('blur', '#vehiculo_cedulachofer', function() {
            buscarChoferPorCedula();
        });

        $(document).on('keypress', '#vehiculo_cedulachofer', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                buscarChoferPorCedula();
                $('#vehiculo_nombre').focus();
            }
        });
        // ==================== BÚSQUEDA POR CÉDULA EN VEHÍCULOS ====================
        function buscarChoferPorCedula() {
            let cedula = $('#vehiculo_cedulachofer').val().trim();
            if (cedula.length < 3) return;

            $.get("{{ url('dirigente/buscar-por-cedulap') }}/" + cedula, function(response) {
                if (response.encontrado) {
                    $('#vehiculo_nombre').val(response.data.nombre ?? '');
                    $('#vehiculo_telefono1').val(response.data.telefono ?? '');
                    $('#vehiculo_telefono2').val(response.data.telefono2 ?? '');
                } else {
                    // Limpiar campos si no se encuentra
                    $('#vehiculo_nombre').val('');
                    $('#vehiculo_telefono1').val('');
                    $('#vehiculo_telefono2').val('');
                }
            }).fail(function() {
                console.log('Error en la búsqueda de cédula');
            });
        }

        // Evento blur para búsqueda por cédula
        $(document).on('blur', '#vehiculo_cedulachofer', function() {
            buscarChoferPorCedula();
        });

        // Evento keypress para búsqueda por cédula con Enter
        $(document).on('keypress', '#vehiculo_cedulachofer', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                buscarChoferPorCedula();
                $('#vehiculo_nombre').focus();
            }
        });
        // ==================== GUARDAR VEHÍCULO DESDE MODAL ====================
        $(document).on('submit', '#formCrearVehiculoPuntero', function(e) {
            e.preventDefault();

            let btnSubmit = $(this).find('button[type="submit"]');
            let punteroId = $('#vehiculo_id_puntero').val();

            btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            let formData = {
                nombre: $('#vehiculo_nombre').val(),
                cedulachofer: $('#vehiculo_cedulachofer').val(),
                chapa: $('#vehiculo_chapa').val(),
                tipovehiculo: $('#vehiculo_tipovehiculo').val(),
                capacidad: $('#vehiculo_capacidad').val(),
                telefono1: $('#vehiculo_telefono1').val(),
                telefono2: $('#vehiculo_telefono2').val(),
                montopagar: $('#vehiculo_montopagar').val(),
                cantidadpagos: $('#vehiculo_cantidadpagos').val(),
                rolvehiculo: $('#vehiculo_rolvehiculo').val(),
                id_puntero: punteroId,
                _token: '{{ csrf_token() }}'
            };

            $.ajax({
                url: "{{ route('vehiculo.store.from.puntero') }}",
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // Limpiar formulario
                    $('#formCrearVehiculoPuntero')[0].reset();
                    $('#vehiculo_capacidad').val(5);
                    $('#vehiculo_cantidadpagos').val(2);
                    $('#vehiculo_montopagar').val('300000');
                    $('#vehiculo_tipovehiculo').val('AUTOMOVIL');
                    $('#vehiculo_rolvehiculo').val('PUNTERO');
                    $('#vehiculo_cedulachofer').focus();

                    // Recargar la lista de vehículos
                    cargarVehiculosPuntero(punteroId);

                    btnSubmit.prop('disabled', false).html(
                        '<i class="fas fa-save"></i> Guardar Vehículo');
                },
                error: function(xhr) {
                    btnSubmit.prop('disabled', false).html(
                        '<i class="fas fa-save"></i> Guardar Vehículo');

                    if (xhr.status === 422 && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = '';
                        $.each(errors, function(key, value) {
                            errorMessage += `${key}: ${value[0]}\n`;
                        });
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validación',
                            text: errorMessage
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Error al crear el vehículo'
                        });
                    }
                }
            });
        });
    </script>
@stop
