@extends('adminlte::page')

@section('title', 'Carga de Votos')
@section('plugins.Datatables', true)

@section('content_header')
    <h1><i class="fas fa-check-double"></i> Carga de Votos</h1>
@stop

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form id="filterForm" class="form-inline">
                @if($esSuperAdmin)
                    <div class="form-group mr-3">
                        <label class="mr-2"><strong>Candidato:</strong></label>
                        <select name="candidato_id" id="candidato_id" class="form-control">
                            <option value="">Todos los candidatos</option>
                            @foreach($candidatos as $candidato)
                                <option value="{{ $candidato->id }}">
                                    {{ $candidato->nombre }} ({{ ucfirst($candidato->tipo) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Generar
                </button>
                <button type="button" class="btn btn-success ml-2" id="btnRefresh">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </form>
        </div>
    </div>

    <div class="d-flex align-items-center flex-wrap mb-3">
        <span id="estado" role="status" aria-live="polite">Cargando resumen…</span>
    </div>

    <div id="error-reporte" class="alert alert-danger" role="alert" hidden></div>

    <div id="reporte" hidden>
        <div class="row">
            @foreach(['total' => 'Total Votantes', 'votaron' => 'Votaron', 'sin_voto' => 'No Votaron', 'porcentaje' => 'Participación'] as $key => $label)
                <div class="col-sm-6 col-xl-3">
                    <div class="small-box bg-white border shadow-sm">
                        <div class="inner">
                            <h3 id="metrica-{{ $key }}">0</h3>
                            <p>{{ $label }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="alert alert-light border">
            Hacé clic en los números de la columna <strong>Voto</strong> o <strong>No Voto</strong> para ver la lista de personas.
            Los datos pueden tener hasta un minuto de antigüedad.
        </div>

        <div class="card">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#panel-punteros" role="tab">
                            <i class="fas fa-users"></i> Por puntero
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body tab-content">
                <div id="panel-punteros" class="tab-pane fade show active" role="tabpanel">
                    <p class="text-muted">La barra muestra la proporción de participación. Podés buscar y ordenar los punteros.</p>
                    <table id="tabla-punteros" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Puntero</th>
                                <th>Dirigente</th>
                                <th>Total Votantes</th>
                                <th>Voto</th>
                                <th>No Voto</th>
                                <th>Participación</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detalleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Detalle</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="modalBodyContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
<style>
    .voto-link { cursor: pointer; text-decoration: none; }
    .voto-link:hover { text-decoration: underline; }
    .badge-voto { font-size: 1rem; padding: 0.4em 0.65em; }
    .participacion-barra { min-width:130px; height:12px; background:#dee2e6; border-radius:6px; overflow:hidden; }
    .participacion-barra span { display:block; height:100%; background:#28a745; }
</style>
@endpush

@push('js')
<script>
$(function () {
    var tabla = null;
    var tablaDetalle = null;
    var numero = new Intl.NumberFormat('es-PY');
    var texto = $.fn.dataTable.render.text();
    var idioma = {
        search: 'Buscar:', lengthMenu: 'Mostrar _MENU_ punteros', info: '_START_ a _END_ de _TOTAL_ punteros',
        infoEmpty: 'Sin punteros', infoFiltered: '(de _MAX_ punteros)', zeroRecords: 'No se encontraron punteros',
        emptyTable: 'No hay punteros para mostrar', paginate: {first:'Primero', last:'Último', next:'Siguiente', previous:'Anterior'}
    };

    function buildTabla(filas) {
        var columnas = [
            {data:'nombre', render: texto},
            {data:'dirigente', render: texto},
            {data:'total'},
            {data:'votaron', render: function (value, type, row) {
                if (type !== 'display') return value;
                if (value > 0) {
                    return '<a href="#" class="voto-link btn-detalle" data-puntero="'+row.puntero_id+'" data-tipo="votaron" data-nombre="'+row.nombre+'">'
                        + '<span class="badge badge-success badge-voto">'+numero.format(value)+'</span></a>';
                }
                return '<span class="badge badge-secondary badge-voto">0</span>';
            }},
            {data:'sin_voto', render: function (value, type, row) {
                if (type !== 'display') return value;
                if (value > 0) {
                    return '<a href="#" class="voto-link btn-detalle" data-puntero="'+row.puntero_id+'" data-tipo="no_votaron" data-nombre="'+row.nombre+'">'
                        + '<span class="badge badge-danger badge-voto">'+numero.format(value)+'</span></a>';
                }
                return '<span class="badge badge-secondary badge-voto">0</span>';
            }},
            {data:'porcentaje', render: function (value, type) {
                var n = Math.max(0, Math.min(100, Number(value) || 0));
                if (type !== 'display') return n;
                return '<span>'+numero.format(n)+'%</span><div class="participacion-barra" aria-hidden="true"><span style="width:'+n+'%"></span></div>';
            }}
        ];

        if (tabla) {
            tabla.clear().rows.add(filas).draw();
            return;
        }
        tabla = $('#tabla-punteros').DataTable({
            data: filas, columns: columnas, language: idioma,
            pageLength: 25, lengthMenu: [10, 25, 50, 100],
            deferRender: true, scrollX: true, order: [[0, 'asc']]
        });
    }

    function getFilters() {
        return {
            candidato_id: $('#candidato_id').length ? ($('#candidato_id').val() || '') : ''
        };
    }

    async function cargar() {
        $('#actualizar, #btnRefresh, #filterForm button[type=submit]').prop('disabled', true);
        $('#estado').text('Cargando resumen…');
        $('#error-reporte').prop('hidden', true);

        try {
            var params = new URLSearchParams(getFilters());
            var response = await fetch('{{ route("reportes.carga-votos.data") }}?' + params.toString(), {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                var detalle = await response.json().catch(function () { return {}; });
                var mensaje = response.status === 403
                    ? 'No tenés permiso Reportes o un sistema asignado.'
                    : (detalle.message || 'No se pudo cargar el reporte (HTTP ' + response.status + ').');
                throw new Error(mensaje);
            }

            var data = await response.json();

            $('#metrica-total').text(numero.format(data.resumen.total));
            $('#metrica-votaron').text(numero.format(data.resumen.votaron));
            $('#metrica-sin_voto').text(numero.format(data.resumen.sin_voto));
            $('#metrica-porcentaje').text(numero.format(data.resumen.porcentaje) + '%');

            $('#reporte').prop('hidden', false);
            buildTabla(data.punteros);
            $('#estado').text('Datos al ' + new Date(data.generado_en).toLocaleString('es-PY'));
        } catch (error) {
            $('#reporte').prop('hidden', true);
            $('#error-reporte').text(error.message).prop('hidden', false);
            $('#estado').text('Carga incompleta');
        } finally {
            $('#actualizar, #btnRefresh, #filterForm button[type=submit]').prop('disabled', false);
        }
    }

    $(document).on('click', '.btn-detalle', function (e) {
        e.preventDefault();
        var punteroId = $(this).data('puntero');
        var tipo = $(this).data('tipo');
        var nombre = $(this).data('nombre');
        var label = tipo === 'votaron' ? 'Votaron' : 'No Votaron';

        $('#modalTitulo').text(label + ' — ' + nombre);
        $('#modalBodyContent').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div></div>');
        $('#detalleModal').modal('show');

        var params = {
            puntero_id: punteroId,
            tipo: tipo,
            candidato_id: $('#candidato_id').length ? ($('#candidato_id').val() || '') : ''
        };

        $.ajax({
            url: '{{ route("reportes.carga-votos.detalle") }}',
            type: 'GET',
            data: params,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#modalBodyContent').html(response.html);
                    if (tablaDetalle) {
                        tablaDetalle.destroy();
                        tablaDetalle = null;
                    }
                    var $tbl = $('#modalDetalleTable');
                    if ($tbl.length) {
                        tablaDetalle = $tbl.DataTable({
                            language: idioma,
                            pageLength: 25,
                            lengthMenu: [10, 25, 50],
                            order: [[1, 'asc']],
                            deferRender: true
                        });
                    }
                } else {
                    $('#modalBodyContent').html('<div class="alert alert-danger">' + (response.message || 'Error al cargar detalle') + '</div>');
                }
            },
            error: function () {
                $('#modalBodyContent').html('<div class="alert alert-danger">Error al cargar el detalle</div>');
            }
        });
    });

    $('#detalleModal').on('hidden.bs.modal', function () {
        if (tablaDetalle) {
            tablaDetalle.destroy();
            tablaDetalle = null;
        }
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        if (tabla) tabla.columns.adjust();
    });

    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        cargar();
    });

    $('#btnRefresh').on('click', cargar);

    cargar();
});
</script>
@endpush
