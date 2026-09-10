@extends('adminlte::page')

@section('title', 'Votó vs No Votó por Puntero')
@section('plugins.Datatables', true)

@section('content_header')
    <h1><i class="fas fa-bullseye"></i> Votó vs No Votó por Puntero</h1>
@stop

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form id="filterForm" class="form-inline">
                <div class="form-group mr-3">
                    <label class="mr-2"><strong>Candidato a concejal:</strong></label>
                    <select name="candidato_id" id="candidato_id" class="form-control select2" style="min-width: 280px;">
                        <option value="">Seleccionar candidato</option>
                        @foreach($candidatos as $candidato)
                            <option value="{{ $candidato->id }}">
                                {{ $candidato->numero_orden }}. {{ $candidato->nombre_completo }} ({{ $candidato->partido->sigla ?? $candidato->partido->nombre ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
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
        <span id="estado" role="status" aria-live="polite">Seleccioná un candidato para comenzar.</span>
    </div>

    <div id="error-reporte" class="alert alert-danger" role="alert" hidden></div>

    <div id="aviso-sin-carga" class="alert alert-warning" role="alert" hidden></div>

    <div id="reporte" hidden>
        <div class="row">
            <div class="col-sm-6 col-xl-3">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-anotados">0</h3>
                        <p>Votantes Anotados</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-votaron">0</h3>
                        <p>Votó (cédula en votos)</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-no_votaron">0</h3>
                        <p>No Votó</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-participacion">0%</h3>
                        <p>Participación</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-light border">
            <strong>Cómo se calcula:</strong> se compara por <em>número de cédula</em>.
            Un votante anotado <strong>Votó</strong> si su cédula aparece en la tabla <code>votos</code> de un colegio donde el candidato compite
            (mesas del candidato → colegios → cédulas cargadas por los miembros de mesa).
            El puntero con mayor participación se marca en <span class="badge badge-success">verde</span>.
            <br><strong>Mesas Compartidas:</strong> mesas donde votan votantes de 2 o más punteros, comparando cuántos de cada puntero votaron ahí.
        </div>

        <div class="card">
            <div class="card-body">
                <table id="tabla-punteros" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Puntero</th>
                            <th>Dirigente</th>
                            <th>Anotados</th>
                            <th>Votó</th>
                            <th>No Votó</th>
                            <th>Mesas</th>
                            <th>Participación</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-share-alt"></i> Mesas Compartidas (2 o más punteros)
                    <span class="badge badge-secondary badge-pill" id="metrica-mesas_compartidas" data-toggle="tooltip" title="Mesas donde votan votantes de 2 o más punteros" style="vertical-align: baseline;">0</span>
                </h3>
            </div>
            <div class="card-body">
                <table id="tabla-mesas-compartidas" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Mesa</th>
                            <th>Colegio</th>
                            <th>Punteros</th>
                            <th>Comparación por Puntero</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@push('css')
<style>
    .participacion-barrera { min-width:110px; height:12px; background:#dee2e6; border-radius:6px; overflow:hidden; }
    .participacion-barrera span { display:block; height:100%; background:#28a745; }
    .mejor-participacion { font-weight: bold; }
</style>
@endpush

@push('js')
<script>
$(function () {
    var tabla = null;
    var tablaMesas = null;
    var numero = new Intl.NumberFormat('es-PY');
    function texto(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }
    var idioma = {
        search: 'Buscar:', lengthMenu: 'Mostrar _MENU_ punteros', info: '_START_ a _END_ de _TOTAL_ punteros',
        infoEmpty: 'Sin punteros', infoFiltered: '(de _MAX_ punteros)', zeroRecords: 'No se encontraron punteros',
        emptyTable: 'No hay punteros para mostrar', paginate: {first:'Primero', last:'Último', next:'Siguiente', previous:'Anterior'}
    };
    var idiomaMesas = Object.assign({}, idioma, {
        lengthMenu: 'Mostrar _MENU_ mesas', info: '_START_ a _END_ de _TOTAL_ mesas',
        infoEmpty: 'Sin mesas compartidas', infoFiltered: '(de _MAX_ mesas)', zeroRecords: 'Sin mesas compartidas',
        emptyTable: 'No hay mesas compartidas'
    });

    function badgeParticipacion(valor, esMejor) {
        if (esMejor) {
            return '<span class="badge badge-success badge-pill">' + numero.format(valor) + '%</span>';
        }
        var clase = valor >= 80 ? 'badge-success' : (valor >= 60 ? 'badge-warning' : 'badge-danger');
        return '<span class="badge ' + clase + ' badge-pill">' + numero.format(valor) + '%</span>';
    }

    function buildComparacion(punteros) {
        return punteros.map(function (p) {
            var icono = p.movilizo
                ? '<i class="fas fa-check-circle text-success"></i>'
                : '<i class="fas fa-times-circle text-danger"></i>';
            var color = p.movilizo ? 'success' : 'danger';
            return '<div class="mb-1">' + icono + ' <strong>' + texto(p.nombre) + '</strong> '
                + '<span class="badge badge-info badge-pill" data-toggle="tooltip" title="Votantes en esta mesa">'
                + numero.format(p.votantes) + '</span> '
                + '<span class="badge badge-primary badge-pill" data-toggle="tooltip" title="De ellos, votaron en esta mesa">'
                + numero.format(p.votaron) + '</span> '
                + '<span class="badge badge-' + color + ' badge-pill" data-toggle="tooltip" title="¿Movilizó votantes que votaron en esta mesa?">'
                + (p.movilizo ? 'movilizó' : 'sin voto') + '</span></div>';
        }).join('');
    }

    function buildTabla(filas) {
        var columnas = [
            {data:'nombre', render: function (value, type, row) {
                if (type !== 'display') return value;
                return row.es_mejor
                    ? '<span class="text-success mejor-participacion">' + texto(value) + ' <i class="fas fa-trophy"></i></span>'
                    : texto(value);
            }},
            {data:'dirigente', render: texto},
            {data:'anotados', render: function (value, type) {
                if (type !== 'display') return value;
                return '<span class="badge badge-secondary badge-pill">' + numero.format(value) + '</span>';
            }},
            {data:'votaron', render: function (value, type) {
                if (type !== 'display') return value;
                return '<span class="badge badge-success badge-pill">' + numero.format(value) + '</span>';
            }},
            {data:'no_votaron', render: function (value, type) {
                if (type !== 'display') return value;
                return '<span class="badge badge-danger badge-pill">' + numero.format(value) + '</span>';
            }},
            {data:'mesas'},
            {data:'participacion', render: function (value, type, row) {
                var n = Math.max(0, Math.min(100, Number(value) || 0));
                if (type !== 'display') return n;
                return '<span class="participacion-barrera"><span style="width:' + n + '%"></span></span> '
                    + badgeParticipacion(n, row.es_mejor);
            }}
        ];

        if (tabla) {
            tabla.clear().rows.add(filas).draw();
            return;
        }
        tabla = $('#tabla-punteros').DataTable({
            data: filas, columns: columnas, language: idioma,
            pageLength: 25, lengthMenu: [10, 25, 50, 100],
            deferRender: true, scrollX: true, order: [[6, 'desc']]
        });
    }

    function buildTablaMesas(mesas) {
        var columnas = [
            {data:'codigo', render: function (value) {
                return '<span class="font-weight-bold">' + texto(value) + '</span>';
            }},
            {data:'colegio', render: texto},
            {data:'num_punteros', render: function (value, type) {
                if (type !== 'display') return value;
                return '<span class="badge badge-secondary badge-pill">' + numero.format(value) + '</span>';
            }},
            {data:'punteros', render: function (value, type, row) {
                if (type !== 'display') return value;
                return buildComparacion(row.punteros);
            }}
        ];

        if (tablaMesas) {
            tablaMesas.clear().rows.add(mesas).draw();
            return;
        }
        tablaMesas = $('#tabla-mesas-compartidas').DataTable({
            data: mesas, columns: columnas, language: idiomaMesas,
            pageLength: 10, lengthMenu: [10, 25, 50, 100],
            deferRender: true, scrollX: true, order: [[2, 'desc']]
        });
    }

    async function cargar() {
        var candidatoId = $('#candidato_id').val();
        if (!candidatoId) {
            $('#reporte').prop('hidden', true);
            $('#estado').text('Seleccioná un candidato para comenzar.');
            return;
        }

        $('#btnRefresh, #filterForm button[type=submit]').prop('disabled', true);
        $('#estado').text('Cargando resumen…');
        $('#error-reporte').prop('hidden', true);

        try {
            var params = new URLSearchParams({ candidato_id: candidatoId });
            var response = await fetch('{{ route("reportes.efectividad-puntero.data") }}?' + params.toString(), {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                var detalle = await response.json().catch(function () { return {}; });
                throw new Error(detalle.message || 'No se pudo cargar el reporte (HTTP ' + response.status + ').');
            }

            var data = await response.json();
            var r = data.resumen;

            $('#metrica-anotados').text(numero.format(r.anotados));
            $('#metrica-votaron').text(numero.format(r.votaron));
            $('#metrica-no_votaron').text(numero.format(r.no_votaron));
            $('#metrica-participacion').text(numero.format(r.participacion) + '%');
            $('#metrica-mesas_compartidas').text(numero.format(r.mesas_compartidas));

            $('#reporte').prop('hidden', false);
            $('#aviso-sin-carga').prop('hidden', data.tiene_carga)
                .text(data.tiene_carga ? '' : (data.mensaje_sin_carga || ''));
            buildTabla(data.punteros);
            buildTablaMesas(data.mesas_compartidas || []);
            $('[data-toggle=tooltip]').tooltip();
            $('#estado').text((data.candidato.nombre + (data.candidato.partido ? ' (' + data.candidato.partido + ')' : ''))
                + ' — Datos al ' + new Date(data.generado_en).toLocaleString('es-PY'));
        } catch (error) {
            $('#reporte').prop('hidden', true);
            $('#error-reporte').text(error.message).prop('hidden', false);
            $('#estado').text('Carga incompleta');
        } finally {
            $('#btnRefresh, #filterForm button[type=submit]').prop('disabled', false);
        }
    }

    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        cargar();
    });

    $('#btnRefresh').on('click', cargar);
});
</script>
@endpush