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
                <input type="text" id="buscadorArbol" class="form-control" placeholder="Buscar distrito, candidatura, intendente, concejal...">
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
            @if(isset($arbolJerarquico) && count($arbolJerarquico) > 0)
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

    {{-- MODALES --}}
    <!-- Modal de Dirigentes -->
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

    <!-- Modal de Punteros -->
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

    <!-- Modal de Votantes -->
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
@section('css')
    <style>
        /* Estilos base del árbol */
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
        
        /* Estilos para ramas colapsadas */
        .tree ul.collapsed {
            display: none;
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
        
        /* Estilo específico para los distritos - MEJORADO */
        .level-distrito .card {
            border-left-color: #007bff;
        }
        
        .level-distrito .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
        }
        
        /* Texto blanco para todo el contenido del distrito */
        .level-distrito .card-header,
        .level-distrito .card-header .card-title,
        .level-distrito .card-header .text-muted,
        .level-distrito .card-header small,
        .level-distrito .card-header .badge,
        .level-distrito .card-header .stats-distrito,
        .level-distrito .card-header .stats-distrito span,
        .level-distrito .card-header .d-flex,
        .level-distrito .card-header .row,
        .level-distrito .card-header .col-12 {
            color: white !important;
        }
        
        /* Estilos para los badges dentro del distrito */
        .level-distrito .card-header .badge {
            background-color: rgba(255, 255, 255, 0.2);
            color: white !important;
        }
        
        .level-distrito .card-header .badge-orange,
        .level-distrito .card-header .badge-purple {
            background-color: rgba(255, 255, 255, 0.2);
            color: white !important;
        }
        
        /* Texto de las estadísticas en el distrito */
        .level-distrito .card-header .text-warning,
        .level-distrito .card-header .text-success,
        .level-distrito .card-header .text-primary,
        .level-distrito .card-header .text-info,
        .level-distrito .card-header .text-danger {
            color: white !important;
            font-weight: 500;
        }
        
        /* Iconos en el distrito */
        .level-distrito .card-header i {
            color: white !important;
        }
        
        /* Línea separadora en el distrito */
        .level-distrito .card-header .border-top {
            border-top-color: rgba(255, 255, 255, 0.3) !important;
        }
        
        /* Icono de expandir/colapsar */
        .toggle-icon {
            cursor: pointer;
            margin-right: 10px;
            font-size: 14px;
            color: #6c757d;
            transition: transform 0.3s;
            display: inline-block;
            width: 20px;
            text-align: center;
        }
        
        .toggle-icon:hover {
            color: #007bff;
        }
        
        .toggle-icon.expanded {
            transform: rotate(90deg);
        }
        
        /* Estilos para el icono dentro del distrito */
        .level-distrito .toggle-icon {
            color: white !important;
        }
        
        .level-distrito .toggle-icon:hover {
            color: #ffc107 !important;
        }
        
        /* Niveles jerárquicos para otros nodos */
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
        
        /* Badges personalizados */
        .badge-orange {
            background-color: #fd7e14;
            color: white;
        }
        
        .badge-purple {
            background-color: #6f42c1;
            color: white;
        }
        
        /* Clases utilitarias */
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
        
        /* Otros estilos */
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
        
        .stats-distrito {
            font-size: 0.75rem;
        }
        
        .stats-distrito span {
            margin-right: 10px;
        }
        
        .cursor-pointer {
            cursor: pointer;
        }
    </style>
@stop
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
        
        /* Estilos para ramas colapsadas */
        .tree ul.collapsed {
            display: none;
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
        
        /* Icono de expandir/colapsar */
        .toggle-icon {
            cursor: pointer;
            margin-right: 10px;
            font-size: 14px;
            color: #6c757d;
            transition: transform 0.3s;
            display: inline-block;
            width: 20px;
            text-align: center;
        }
        
        .toggle-icon:hover {
            color: #007bff;
        }
        
        .toggle-icon.expanded {
            transform: rotate(90deg);
        }
        
        /* Niveles jerárquicos */
        .level-distrito .card { border-left-color: #007bff; }
        .level-distrito .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
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
        
        .stats-distrito {
            font-size: 0.75rem;
        }
        
        .stats-distrito span {
            margin-right: 10px;
        }
        
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
    </style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Inicializar: todos los nodos hijos colapsados (excepto el primer nivel)
    $('.tree li > ul').each(function() {
        $(this).addClass('collapsed');
    });
    
    // Agregar iconos de expandir/colapsar solo a los nodos que tienen hijos
    $('.tree li').each(function() {
        var $li = $(this);
        if ($li.children('ul').length > 0) {
            var $node = $li.children('.tree-node');
            var $icon = $('<span class="toggle-icon">▶</span>');
            $icon.prependTo($node.find('.card-title').parent());
            $node.addClass('has-children');
            
            // Al hacer clic en el icono o en el nodo
            $icon.click(function(e) {
                e.stopPropagation();
                toggleNode($li);
            });
            
            $node.click(function(e) {
                e.stopPropagation();
                var sistemaId = $(this).data('id');
                var sistemaTipo = $(this).data('tipo');
                
                // Si es un sistema (tiene ID y no es distrito) abrir modal
                if (sistemaId && sistemaTipo !== 'Distrito') {
                    abrirModalDirigentes(sistemaId, $(this).find('.card-title').text(), sistemaTipo);
                } else {
                    // Si es distrito, expandir/colapsar
                    toggleNode($li);
                }
            });
        }
    });
    
    function toggleNode($li) {
        var $ul = $li.children('ul');
        var $icon = $li.find('.toggle-icon');
        
        if ($ul.hasClass('collapsed')) {
            $ul.removeClass('collapsed');
            $icon.text('▼');
            $icon.addClass('expanded');
        } else {
            $ul.addClass('collapsed');
            $icon.text('▶');
            $icon.removeClass('expanded');
        }
    }
    
    // Expandir todos los nodos
    $('#expandirTodos').click(function() {
        $('.tree li > ul').removeClass('collapsed');
        $('.toggle-icon').text('▼').addClass('expanded');
    });
    
    // Colapsar todos los nodos (excepto el primer nivel)
    $('#colapsarTodos').click(function() {
        $('.tree li > ul').addClass('collapsed');
        $('.toggle-icon').text('▶').removeClass('expanded');
    });
    
    // Buscador
    $('#buscadorArbol').on('keyup', function() {
        let query = $(this).val().toLowerCase().trim();
        let encontrados = 0;
        
        $('.tree-node').each(function() {
            let nombre = $(this).find('.card-title').text().toLowerCase();
            let tipo = $(this).find('.badge-nivel').text().toLowerCase();
            let textoCompleto = $(this).text().toLowerCase();
            
            if (nombre.includes(query) || tipo.includes(query) || textoCompleto.includes(query)) {
                $(this).show();
                $(this).addClass('nodo-buscar');
                encontrados++;
                // Expandir los ancestros para mostrar el nodo encontrado
                $(this).parents('li').each(function() {
                    var $li = $(this);
                    var $ul = $li.children('ul');
                    if ($ul.hasClass('collapsed')) {
                        $ul.removeClass('collapsed');
                        $li.find('.toggle-icon').text('▼').addClass('expanded');
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
    
    // Funciones para modales
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