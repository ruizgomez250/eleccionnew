@extends('adminlte::page')

@section('title', 'Efectividad del Puntero por Candidato')
@section('plugins.Datatables', true)

@section('content_header')
    <h1><i class="fas fa-bullseye"></i> Efectividad del Puntero por Candidato</h1>
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

    <div id="reporte" hidden>
        <div class="row">
            <div class="col-sm-6 col-xl-3">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-punteros">0</h3>
                        <p>Punteros</p>
                    </div>
                </div>
            </div>
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
                        <h3 id="metrica-se_fueron">0</h3>
                        <p>Se Fueron a Votar</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-no_se_fueron">0</h3>
                        <p>No Se Fueron</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-votos_reales">0</h3>
                        <p>Votos Reales del Candidato</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-debio_tener">0</h3>
                        <p>Debió Haber Tenido (Se Fueron)</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-efectividad">0%</h3>
                        <p>Efectividad General</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-fallas_reiteradas">0</h3>
                        <p>Punteros con Fallas Reiteradas</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-light border">
            <strong>Interpretación:</strong> para cada puntero, <em>Debió Tener</em> = la cantidad de sus votantes que se fueron a votar.
            <em>Votos Reales</em> = votos que obtuvo el candidato en las mesas donde votan esos votantes.
            La <strong>efectividad</strong> es Votos Reales / Debió Tener. El puntero con mayor efectividad se marca en <span class="badge badge-success">verde</span>.
            <br><strong>Fallas:</strong> una mesa falla cuando el puntero movilizó votantes ahí pero el candidato sacó <em>menos</em> votos que los que movilizó ese puntero.
            Se marcan como <strong>reiteradas</strong> cuando el puntero falla en la mayoría de sus mesas con gente movilizada.
            <br><strong>Mesas Compartidas:</strong> mesas con votantes de 2 o más punteros, donde se compara cuántos votos obtuvo el candidato vs. la movilización de cada puntero.
        </div>

        <div class="card">
            <div class="card-body">
                <table id="tabla-punteros" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Puntero</th>
                            <th>Dirigente</th>
                            <th>Anotados</th>
                            <th>Se Fueron</th>
                            <th>No Se Fueron</th>
                            <th>Mesas</th>
                            <th>Debió Tener</th>
                            <th>Votos Reales</th>
                            <th>Fallas</th>
                            <th>Efectividad</th>
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
                            <th>Votos del Candidato</th>
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
    .efectividad-barrera { min-width:110px; height:12px; background:#dee2e6; border-radius:6px; overflow:hidden; }
    .efectividad-barrera span { display:block; height:100%; background:#28a745; }
    .mejor-efectividad { font-weight: bold; }
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

    function badgeEfectividad(valor, esMejor) {
        if (esMejor) {
            return '<span class="badge badge-success badge-pill">' + numero.format(valor) + '%</span>';
        }
        var clase = valor >= 80 ? 'badge-success' : (valor >= 60 ? 'badge-warning' : 'badge-danger');
        return '<span class="badge ' + clase + ' badge-pill">' + numero.format(valor) + '%</span>';
    }

    function buildDetalleFallas(row) {
        var fallas = Number(row.fallas) || 0;
        if (fallas === 0) {
            return '<span class="badge badge-success badge-pill">0</span>';
        }
        var detalle = (row.mesas_falladas || []).map(function (m) {
            return m.codigo + ' (' + m.colegio + '): fueron ' + m.se_fueron + ', votos ' + m.votos;
        }).join('\n');
        var icono = row.fallas_reiteradas ? ' <i class="fas fa-exclamation-triangle" data-toggle="tooltip" title="Fallas reiteradas"></i>' : '';
        return '<span class="badge badge-danger badge-pill" data-toggle="tooltip" title="' + detalle + '">'
            + numero.format(fallas) + '</span>' + icono;
    }

    function buildComparacion(punteros) {
        return punteros.map(function (p) {
            var icono = p.cubrio
                ? '<i class="fas fa-check-circle text-success"></i>'
                : '<i class="fas fa-times-circle text-danger"></i>';
            var color = p.cubrio ? 'success' : 'danger';
            return '<div class="mb-1">' + icono + ' <strong>' + texto(p.nombre) + '</strong> '
                + '<span class="badge badge-info badge-pill" data-toggle="tooltip" title="Votantes en esta mesa">'
                + numero.format(p.votantes) + '</span> '
                + '<span class="badge badge-primary badge-pill" data-toggle="tooltip" title="Se fueron a votar en esta mesa">'
                + numero.format(p.se_fueron) + '</span> '
                + ' (movilizó ' + numero.format(p.se_fueron) + ', candidato obtuvo <strong>' + numero.format(p.votos_candidato) + '</strong>) '
                + '<span class="badge badge-' + color + ' badge-pill">' + numero.format(p.efectividad_mesa) + '%</span></div>';
        }).join('');
    }

    function buildTabla(filas) {
        var columnas = [
            {data:'nombre', render: function (value, type, row) {
                if (type !== 'display') return value;
                return row.es_mejor
                    ? '<span class="text-success mejor-efectividad">' + texto(value) + ' <i class="fas fa-trophy"></i></span>'
                    : texto(value);
            }},
            {data:'dirigente', render: texto},
            {data:'anotados'},
            {data:'se_fueron', render: function (value, type, row) {
                if (type !== 'display') return value;
                return '<span class="badge badge-info badge-pill">' + numero.format(value) + '</span>';
            }},
            {data:'no_se_fueron', render: function (value, type) {
                if (type !== 'display') return value;
                return '<span class="badge badge-secondary badge-pill">' + numero.format(value) + '</span>';
            }},
            {data:'mesas'},
            {data:'debio_tener', render: function (value, type) {
                if (type !== 'display') return value;
                return '<span class="badge badge-primary badge-pill">' + numero.format(value) + '</span>';
            }},
            {data:'votos_reales', render: function (value, type) {
                if (type !== 'display') return value;
                return '<span class="badge badge-success badge-pill">' + numero.format(value) + '</span>';
            }},
            {data:'fallas', render: buildDetalleFallas},
            {data:'efectividad', render: function (value, type, row) {
                var n = Math.max(0, Number(value) || 0);
                if (type !== 'display') return n;
                return '<span class="efectividad-barrera"><span style="width:' + Math.min(100, n) + '%"></span></span> '
                    + badgeEfectividad(n, row.es_mejor);
            }}
        ];

        if (tabla) {
            tabla.clear().rows.add(filas).draw();
            return;
        }
        tabla = $('#tabla-punteros').DataTable({
            data: filas, columns: columnas, language: idioma,
            pageLength: 25, lengthMenu: [10, 25, 50, 100],
            deferRender: true, scrollX: true, order: [[9, 'desc']]
        });
    }

    function buildTablaMesas(mesas) {
        var columnas = [
            {data:'codigo', render: function (value) {
                return '<span class="font-weight-bold">' + texto(value) + '</span>';
            }},
            {data:'colegio', render: texto},
            {data:'votos_candidato', render: function (value, type) {
                if (type !== 'display') return value;
                return '<span class="badge badge-success badge-pill">' + numero.format(value) + '</span>';
            }},
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
            deferRender: true, scrollX: true, order: [[3, 'desc']]
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

            $('#metrica-punteros').text(numero.format(r.punteros));
            $('#metrica-anotados').text(numero.format(r.anotados));
            $('#metrica-se_fueron').text(numero.format(r.se_fueron));
            $('#metrica-no_se_fueron').text(numero.format(r.no_se_fueron));
            $('#metrica-votos_reales').text(numero.format(r.votos_reales));
            $('#metrica-debio_tener').text(numero.format(r.debio_tener));
            $('#metrica-efectividad').text(numero.format(r.efectividad) + '%');
            $('#metrica-fallas_reiteradas').text(numero.format(r.fallas_reiteradas_punteros));
            $('#metrica-mesas_compartidas').text(numero.format(r.mesas_compartidas));

            $('#reporte').prop('hidden', false);
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