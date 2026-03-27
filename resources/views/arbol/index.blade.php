{{-- resources/views/arbol/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Árbol de Candidaturas')

@section('content_header')
    <h1 class="m-0">
        <i class="fas fa-sitemap text-primary"></i> Árbol de Candidaturas
    </h1>
@stop

@section('content')

    {{-- BUSCADOR DE CANDIDATURAS --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-primary">
                        <i class="fas fa-search text-white"></i>
                    </span>
                </div>
                <input type="text" id="buscadorArbol" class="form-control" placeholder="Buscar candidatura por nombre, tipo o ciudad...">
            </div>
        </div>
    </div>

    {{-- ÁRBOL JERÁRQUICO --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-sitemap"></i> Estructura Jerárquica por Usuario
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body" id="arbolContainer">
            @if(isset($arbolJerarquico) && count($arbolJerarquico) > 0)
                <div class="tree">
                    <ul>
                        {{-- IMPORTANTE: Aquí pasamos la variable correctamente --}}
                        @include('arbol.partials.tree-nodes', ['nodes' => $arbolJerarquico])
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

    {{-- LISTA DISTRITOS --}}
    <div class="card">
        <div class="card-header bg-secondary text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-map-marker-alt"></i> Totales por Distrito
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row" id="listaDistritos">
                @foreach ($totalesDistritos as $distrito => $totales)
                    <div class="col-md-3 mb-3">
                        <div class="card distrito-card h-100 shadow-sm border-primary"
                            style="cursor:pointer; transition: transform 0.2s;"
                            data-ciudad-id="{{ $totales['id_ciudad_electoral'] }}" data-distrito="{{ $distrito }}">
                            <div class="card-body text-center">
                                <div class="row mb-2 justify-content-center align-items-center">
                                    <div class="col-12">
                                        <h5 class="card-title font-weight-bold">
                                            <i class="fas fa-map-marker-alt fa-2x text-primary"></i> {{ $distrito }}
                                        </h5>
                                    </div>
                                </div>
                                <div class="row mb-1 justify-content-center">
                                    <div class="col-12">
                                        <p class="mb-0">
                                            <i class="fas fa-user-tie text-warning"></i>
                                            <strong>Dirigentes:</strong>
                                            <span class="badge badge-warning badge-pill">
                                                {{ number_format($totales['dirigentes'], 0, '', '.') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="row mb-1 justify-content-center">
                                    <div class="col-12">
                                        <p class="mb-0">
                                            <i class="fas fa-user-friends text-success"></i>
                                            <strong>Punteros:</strong>
                                            <span class="badge badge-success badge-pill">
                                                {{ number_format($totales['punteros'], 0, '', '.') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                <div class="row justify-content-center">
                                    <div class="col-12">
                                        <p class="mb-0">
                                            <i class="fas fa-vote-yea text-primary"></i>
                                            <strong>Votantes:</strong>
                                            <span class="badge badge-primary badge-pill">
                                                {{ number_format($totales['votantes'], 0, '', '.') }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- MODALES --}}
    <div class="modal fade" id="modalSistemas" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-map-marker-alt"></i> Sistemas del Distrito
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalSistemasBody">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-hand-pointer fa-3x mb-3"></i>
                        <p>Selecciona un distrito para ver sus sistemas</p>
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

    <div class="modal fade" id="modalDirigentes" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" style="max-width: 98%; width: 98%; margin: 10px auto;">
            <div class="modal-content" style="height: 98vh; max-height: 98vh;">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="tituloDirigentes">
                        <i class="fas fa-users"></i> Dirigentes
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="contenidoDirigentes" style="overflow: auto; height: calc(98vh - 120px);">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando dirigentes...</p>
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

    <div class="modal fade" id="modalPunterosLista" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" style="max-width: 98%; width: 98%; margin: 10px auto;">
            <div class="modal-content" style="height: 98vh; max-height: 98vh;">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="tituloPunterosLista">
                        <i class="fas fa-users"></i> Punteros
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="contenidoPunteros" style="overflow: auto; height: calc(98vh - 120px);">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando punteros...</p>
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

    <div class="modal fade" id="modalVotantes" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="tituloVotantes">
                        <i class="fas fa-users"></i> Votantes
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="contenidoVotantes">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando votantes...</p>
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
@stop
{{-- Agrega esto justo después de @section('content') --}}
@if(isset($debug_sistemas))
<div class="alert alert-info mb-3">
    <h5>Información de depuración:</h5>
    <p><strong>Total sistemas:</strong> {{ $debug_sistemas->count() }}</p>
    <p><strong>Total candidaturas filtradas:</strong> {{ isset($debug_candidaturas) ? $debug_candidaturas->count() : 0 }}</p>
    <p><strong>Tipos encontrados:</strong> 
        @foreach($debug_sistemas->groupBy('tipo') as $tipo => $items)
            <span class="badge badge-info">{{ $tipo }}: {{ $items->count() }}</span>
        @endforeach
    </p>
    <p><strong>Árbol jerárquico nodos:</strong> {{ count($arbolJerarquico) }}</p>
    <details>
        <summary>Ver detalles de sistemas</summary>
        <pre>{{ json_encode($debug_sistemas->map(function($s) { return ['id' => $s->id, 'nombre' => $s->nombre, 'tipo' => $s->tipo, 'idusuario' => $s->idusuario]; })->toArray(), JSON_PRETTY_PRINT) }}</pre>
    </details>
</div>
@endif
@section('css')
    <style>
        .tree {
            min-height: 20px;
            padding: 19px;
            margin-bottom: 20px;
            background-color: #f5f5f5;
            border: 1px solid #e3e3e3;
            border-radius: 4px;
        }
        
        .tree ul {
            padding-left: 30px;
            list-style: none;
        }
        
        .tree li {
            position: relative;
            padding-top: 5px;
            padding-bottom: 5px;
            padding-left: 20px;
            box-sizing: border-box;
        }
        
        .tree li:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            width: 1px;
            border-left: 1px solid #ccc;
        }
        
        .tree li:after {
            content: "";
            position: absolute;
            top: 25px;
            left: 0;
            width: 20px;
            height: 1px;
            border-top: 1px solid #ccc;
        }
        
        .tree li:last-child:before {
            height: 25px;
        }
        
        .tree-node {
            cursor: pointer;
            margin: 5px 0;
            transition: all 0.3s;
        }
        
        .tree-node:hover {
            transform: translateX(5px);
        }
        
        .tree-node .card {
            border-left: 4px solid #007bff;
            transition: all 0.3s;
        }
        
        .tree-node .card-header {
            background-color: #fff;
            border-bottom: none;
            padding: 12px 15px;
        }
        
        .level-intendente .card { border-left-color: #dc3545; }
        .level-intendente_virtual .card { border-left-color: #6c757d; }
        .level-concejal .card { border-left-color: #28a745; }
        .level-convencional .card { border-left-color: #17a2b8; }
        .level-convencional_juventud .card { border-left-color: #ffc107; }
        .level-miembro_comite .card { border-left-color: #fd7e14; }
        .level-miembro_juventud .card { border-left-color: #6f42c1; }
        
        .badge-nivel {
            font-size: 0.7rem;
            padding: 4px 8px;
        }
        
        .badge-orange {
            background-color: #fd7e14;
            color: white;
        }
        
        .badge-purple {
            background-color: #6f42c1;
            color: white;
        }
        
        .distrito-card:hover {
            transform: scale(1.03);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .nodo-buscar {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107 !important;
        }
        
        .text-orange {
            color: #fd7e14;
        }
        
        .text-purple {
            color: #6f42c1;
        }
    </style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Buscador de árbol
    $('#buscadorArbol').on('keyup', function() {
        let query = $(this).val().toLowerCase().trim();
        let encontrados = 0;
        
        $('.tree-node').each(function() {
            let nombre = $(this).find('.card-title').text().toLowerCase();
            let tipo = $(this).find('.badge-nivel').text().toLowerCase();
            let ciudad = $(this).find('.text-muted').text().toLowerCase();
            
            if (nombre.includes(query) || tipo.includes(query) || ciudad.includes(query)) {
                $(this).show();
                $(this).addClass('nodo-buscar');
                encontrados++;
                $(this).parents('li, ul').show();
            } else {
                $(this).hide();
                $(this).removeClass('nodo-buscar');
            }
        });
        
        if (encontrados === 0 && query !== '') {
            if ($('#sinResultadosArbol').length === 0) {
                $('#treeView').append(`
                    <div id="sinResultadosArbol" class="alert alert-warning text-center py-3 mt-3">
                        <i class="fas fa-search"></i> No se encontraron candidaturas que coincidan con "${query}"
                    </div>
                `);
            }
        } else {
            $('#sinResultadosArbol').remove();
        }
    });
    
    // Click en nodo del árbol
    $(document).on('click', '.tree-node', function(e) {
        e.stopPropagation();
        let sistemaId = $(this).data('id');
        let sistemaNombre = $(this).find('.card-title').text();
        let sistemaTipo = $(this).find('.badge-nivel').text();
        
        if (!sistemaId) return;
        
        $('#tituloDirigentes').html(`
            <i class="fas fa-users"></i> Dirigentes - ${sistemaTipo}: ${sistemaNombre}
        `);
        $('#contenidoDirigentes').html(`
            <div class="text-center p-4">
                <div class="spinner-border text-warning mb-3" style="width: 3rem; height: 3rem;"></div>
                <p>Cargando dirigentes...</p>
            </div>
        `);
        $('#modalDirigentes').modal('show');
        
        let url = `/sistemas/${sistemaId}/dirigentes`;
        
        $.get(url, function(data) {
            $('#contenidoDirigentes').html(data);
        }).fail(function() {
            $('#contenidoDirigentes').html(`
                <div class="alert alert-danger text-center p-4">
                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                    <p>Error al cargar los dirigentes.</p>
                </div>
            `);
        });
    });
    
    // Click en tarjeta de distrito
    $('.distrito-card').click(function() {
        let ciudadId = $(this).data('ciudad-id');
        let distritoNombre = $(this).data('distrito');
        
        $('#modalSistemas .modal-title').html(`
            <i class="fas fa-map-marker-alt"></i> Sistemas del Distrito: ${distritoNombre}
        `);
        $('#modalSistemasBody').html(`
            <div class="text-center text-muted py-5">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                <p>Cargando sistemas...</p>
            </div>
        `);
        $('#modalSistemas').modal('show');
        
        let url = `/distritos/${ciudadId}/sistemas`;
        
        $.get(url, function(data) {
            $('#modalSistemasBody').html(data);
        }).fail(function() {
            $('#modalSistemasBody').html(`
                <div class="alert alert-danger text-center py-5">
                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                    <p>Error al cargar los sistemas.</p>
                </div>
            `);
        });
    });
    
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
        
        let url = `/sistemas/${sistemaId}/punteros`;
        
        $.get(url, function(data) {
            $('#contenidoPunteros').html(data);
        }).fail(function() {
            $('#contenidoPunteros').html(`
                <div class="alert alert-danger text-center p-4">
                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                    <p>Error al cargar los punteros.</p>
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
        
        let url = `/puntero/${punteroId}/votantes`;
        
        $.get(url, function(data) {
            let html = `
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Tipo Votante</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            if (data.length === 0) {
                html += `<tr><td colspan="4" class="text-center">No hay votantes registrados</td></tr>`;
            } else {
                data.forEach((v, i) => {
                    html += `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${v.cedula}</td>
                            <td>${v.nombre || ''}</td>
                            <td><span class="badge badge-info">${v.tipo_votante || ''}</span></td>
                        </tr>
                    `;
                });
            }
            
            html += `</tbody></table></div>`;
            $('#contenidoVotantes').html(html);
        }).fail(function() {
            $('#contenidoVotantes').html(`
                <div class="alert alert-danger text-center p-4">
                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                    <p>Error al cargar los votantes.</p>
                </div>
            `);
        });
    };
});
</script>
@stop