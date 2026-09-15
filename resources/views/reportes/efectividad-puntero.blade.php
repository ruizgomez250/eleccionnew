@extends('adminlte::page')

@section('title', 'Efectividad Puntero')
@section('plugins.Datatables', true)

@section('content_header')
    <h1><i class="fas fa-bullseye"></i> Efectividad Puntero</h1>
@stop

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form id="filterForm" class="form-inline">
                <div class="form-group mr-3">
                    <label class="mr-2"><strong>Candidato a concejal (certificado de resultado):</strong></label>
                    <select name="candidato_id" id="candidato_id" class="form-control select2" style="min-width: 300px;">
                        <option value="">Seleccionar candidato</option>
                        @foreach($candidatos as $candidato)
                            <option value="{{ $candidato->id }}">
                                {{ $candidato->numero_orden }}. {{ $candidato->nombre_completo }} ({{ $candidato->partido->sigla ?? $candidato->partido->nombre ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @if($esAdmin)
                    <div class="form-group mr-3">
                        <label class="mr-2"><strong>Sistema:</strong></label>
                        <select name="sistema_id" id="sistema_id" class="form-control select2" style="min-width: 260px;">
                            <option value="">Seleccionar sistema</option>
                            @foreach($sistemas as $sistema)
                                <option value="{{ $sistema->id }}" @if((int) optional(auth()->user())->sistema === (int) $sistema->id) selected @endif>
                                    {{ $sistema->nombre }}
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
            <small class="text-muted d-block mt-2">
                Solo se muestran candidatos con <strong>certificados de resultados cargados</strong> (tabla de votos por mesa).
                @if($esAdmin)
                    Elegís contra qué <strong>sistema</strong> se consulta la estructura.
                @else
                    El cálculo usa únicamente <strong>tus punteros y votantes</strong> (tu estructura), comparados contra los votos reales del certificado.
                @endif
            </small>
        </div>
    </div>

    <div class="d-flex align-items-center flex-wrap mb-3">
        <span id="estado" role="status" aria-live="polite">Seleccioná un candidato para comenzar.</span>
    </div>

    <div id="error-reporte" class="alert alert-danger" role="alert" hidden></div>

    <div id="aviso-sin-carga" class="alert alert-warning" role="alert" hidden></div>

    <div id="reporte" hidden>
        <div class="row">
            <div class="col-sm-6 col-xl-2">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-anotados">0</h3>
                        <p>Asignados (N)</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-votaron">0</h3>
                        <p>Fueron a votar (F)</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-ausentes">0</h3>
                        <p>No votaron / Ausentes</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-efectivos">0</h3>
                        <p>Votos Efectivos estimados</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-perdidos">0</h3>
                        <p>Votos Perdidos estimados</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-2">
                <div class="small-box bg-white border shadow-sm">
                    <div class="inner">
                        <h3 id="metrica-ieg">0</h3>
                        <p>IEG global (0–100)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-light border">
            <strong>Metodología (estimación probabilística, el voto es secreto):</strong>
            <ul class="mb-0">
                <li><strong>Tasa de conversión de la mesa</strong> <code>Tasa_M = min(1, V_C,M / F_C,M)</code>: votos reales del candidato en la mesa (certificado) entre la gente de tu estructura que fue a votar ahí.</li>
                <li><strong>Votos efectivos del puntero</strong> = Σ(F_P,M × Tasa_M); <strong>votos perdidos</strong> = F_P − efectivos; <strong>ausentes</strong> = N_P − F_P.</li>
                <li><strong>Ef. movilización</strong> = F_P / N_P &middot; <strong>Ef. real</strong> = Votos_efectivos / N_P.</li>
                <li><strong>IEG</strong> = 100 × Ef. movilización × Ef. real (0–100). Clic en un puntero para ver la efectividad por votante.</li>
            </ul>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-users"></i> Efectividad por Puntero y por Votante</h3>
                <div class="d-flex flex-wrap">
                    <button type="button" class="btn btn-outline-info btn-sm" id="btnVerTodosVotantes" disabled title="Ver listado completo de votantes">
                        <i class="fas fa-list"></i> Ver Todos los Votantes
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm ml-2" id="btnExportarDetalles" disabled title="Exportar los detalles de los punteros marcados con el checkbox">
                        <i class="fas fa-file-export"></i> Exportar Detalles
                    </button>
                    <span class="align-self-center ml-2 font-weight-bold" id="infoSeleccionPunteros" title="Punteros marcados para exportar"></span>
                </div>
            </div>
            <div class="card-body">
                <table id="tabla-punteros" class="table table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th style="width:30px;" class="text-center">
                                <input type="checkbox" id="chkMarcarTodosPunteros" title="Marcar / desmarcar todos">
                            </th>
                            <th>Puntero</th>
                            <th>Dirigente</th>
                            <th>Asignados</th>
                            <th>Fueron</th>
                            <th>Ausentes</th>
                            <th>Efectivos</th>
                            <th>Perdidos</th>
                            <th>Ef. Mov.</th>
                            <th>Ef. Real</th>
                            <th>Tasa Fuga</th>
                            <th>IEG</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-signal"></i> Interpretación del IEG</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr><th>Rango</th><th>Interpretación</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><span class="badge badge-success">80–100</span></td><td>Puntero estrella: moviliza y convence.</td></tr>
                        <tr><td><span class="badge badge-info">60–79</span></td><td>Bueno: fiel, con algo de fuga.</td></tr>
                        <tr><td><span class="badge badge-warning">40–59</span></td><td>Regular: moviliza pero no retiene.</td></tr>
                        <tr><td><span class="badge badge-danger">20–39</span></td><td>Débil: poca movilización o mucha fuga.</td></tr>
                        <tr><td><span class="badge badge-danger">0–19</span></td><td>Inefectivo: revisar si es voto prestado o abandono.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalVotantes" tabindex="-1" role="dialog" aria-labelledby="modalVotantesLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVotantesLabel"><i class="fas fa-list"></i> Votantes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table id="tabla-votantes-modal" class="table table-sm table-striped table-bordered w-100" style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th>Puntero</th>
                                <th>Dirigente</th>
                                <th>Votante</th>
                                <th>Cédula</th>
                                <th>Mesa</th>
                                <th>Escuela</th>
                                <th>Votó</th>
                                <th>Tasa_M</th>
                                <th>P. Fidelidad</th>
                                <th>P. Fuga</th>
                                <th>Clasificación</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalles" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesLabel"><i class="fas fa-file-export"></i> Detalles por Puntero</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-2">
                        <i class="fas fa-info-circle"></i>
                        Marcá con el checkbox los <strong>punteros</strong> cuyos detalles querés exportar. Los votantes del puntero marcado se incluyen automáticamente.
                        <span class="font-weight-bold ml-2" id="infoSeleccionDetalles"></span>
                    </div>
                    <div class="mb-2">
                        <button type="button" class="btn btn-xs btn-outline-primary mr-1" id="btnMarcarTodos">
                            <i class="fas fa-check-double"></i> Marcar todos
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" id="btnDesmarcarTodos">
                            <i class="fas fa-undo"></i> Desmarcar todos
                        </button>
                    </div>
                    <table id="tabla-detalles-modal" class="table table-sm table-striped table-bordered w-100" style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th style="width:30px;" class="text-center">
                                    <input type="checkbox" id="chkMarcarTodosDetalles" title="Marcar / desmarcar todos">
                                </th>
                                <th>Puntero</th>
                                <th>Dirigente</th>
                                <th>Votante</th>
                                <th>Cédula</th>
                                <th>Mesa</th>
                                <th>Escuela</th>
                                <th>Votó</th>
                                <th>Tasa_M</th>
                                <th>P. Fidelidad</th>
                                <th>P. Fuga</th>
                                <th>Clasificación</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
<style>
    .ieg-barrera { min-width:90px; height:12px; background:#dee2e6; border-radius:6px; overflow:hidden; }
    .ieg-barrera span { display:block; height:100%; }
    .votante-col { min-width: 120px; }
    .tabla-detalle-puntero td { background-color: #e9ecef !important; font-weight: bold; }
</style>
@endpush

@push('js')
<script>
$(function () {
    var tabla = null;
    var tablaVotantes = null;
    var tablaDetalles = null;
    var datosGlobales = null;
    var seleccionDetalles = {};
    var numero = new Intl.NumberFormat('es-PY', { maximumFractionDigits: 1 });
    function texto(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }
    var idioma = {
        search: 'Buscar:', lengthMenu: 'Mostrar _MENU_ punteros', info: '_START_ a _END_ de _TOTAL_ punteros',
        infoEmpty: 'Sin punteros', infoFiltered: '(de _MAX_ punteros)', zeroRecords: 'No se encontraron punteros',
        emptyTable: 'No hay punteros para mostrar', paginate: {first:'Primero', last:'Último', next:'Siguiente', previous:'Anterior'}
    };

    function badgePorcentaje(valor, max) {
        var n = Math.max(0, Math.min(max, Number(valor) || 0));
        var clase = n >= 0.8 * max ? 'badge-success'
            : (n >= 0.6 * max ? 'badge-info'
            : (n >= 0.4 * max ? 'badge-warning' : 'badge-danger'));
        return '<span class="badge ' + clase + ' badge-pill">' + numero.format(n) + (max === 100 ? '' : '%') + '</span>';
    }

    function barraIEG(valor, color) {
        var n = Math.max(0, Math.min(100, Number(valor) || 0));
        var bg = color === 'success' ? '#28a745' : (color === 'info' ? '#17a2b8' : (color === 'warning' ? '#ffc107' : '#dc3545'));
        return '<span class="ieg-barrera"><span style="width:' + n + '%;background:' + bg + '"></span></span> '
            + badgePorcentaje(n, 100);
    }

    function buildChildRow(row) {
        var html = '<td colspan="12" class="p-0">';
        if (!row.votantes || row.votantes.length === 0) {
            html += '<div class="p-3 text-muted">Sin votantes para este puntero.</div></td>';
            return html;
        }
        html += '<div class="p-2"><table class="table table-sm table-bordered table-striped mb-0">'
            + '<thead class="thead-light"><tr>'
            + '<th>Votante</th><th>Cédula</th><th>Mesa</th><th>Escuela</th>'
            + '<th>Votó</th><th>Tasa_M</th><th>P. Fidelidad</th><th>P. Fuga</th><th>Clasificación</th>'
            + '</tr></thead><tbody>';
        row.votantes.forEach(function (v) {
            var icono = v.voto
                ? '<i class="fas fa-check-circle text-success"></i>'
                : '<i class="fas fa-times-circle text-danger"></i>';
            html += '<tr>'
                + '<td>' + texto(v.nombre) + '</td>'
                + '<td>' + texto(v.cedula) + '</td>'
                + '<td>' + texto(v.mesa) + '</td>'
                + '<td>' + texto(v.escuela) + '</td>'
                + '<td class="text-center">' + icono + '</td>'
                + '<td class="text-right">' + (v.tasa * 100).toFixed(0) + '%</td>'
                + '<td class="text-right"><strong>' + (v.p_fidelidad * 100).toFixed(1) + '%</strong></td>'
                + '<td class="text-right">' + (v.p_fuga * 100).toFixed(1) + '%</td>'
                + '<td>' + clasBadge(v.clasificacion) + '</td>'
                + '</tr>';
        });
        html += '</tbody></table></div></td>';
        return html;
    }

    function clasBadge(clas) {
        var map = {
            'Fiel probable': 'badge-success',
            'Dudoso': 'badge-warning',
            'Probable fuga': 'badge-danger',
            'Ausente': 'badge-secondary',
            'Sin mesa': 'badge-dark'
        };
        var clase = map[clas] || 'badge-info';
        return '<span class="badge ' + clase + '">' + texto(clas) + '</span>';
    }

    function buildTabla(filas) {
        filas.forEach(function (puntero, i) { puntero.grupo = i; });
        var nombreCandidato = datosGlobales ? (datosGlobales.candidato.nombre + (datosGlobales.candidato.partido ? ' ' + datosGlobales.candidato.partido : '')) : 'Reporte';
        var columnas = [
            {
                data: null, orderable: false, searchable: false, className: 'text-center', width: '30px',
                render: function (value, type, row) {
                    if (type !== 'display' || row.grupo === undefined) return '';
                    var marcado = seleccionDetalles[String(row.grupo)] !== false;
                    return '<input type="checkbox" class="chx-puntero-principal" data-grupo="' + row.grupo + '"' + (marcado ? ' checked' : '') + '>';
                }
            },
            {data:'nombre', render: function (value, type, row) {
                if (type !== 'display') return value;
                return '<a href="javascript:void(0)" class="toggle-votantes" data-puntero="' + row.puntero_id + '" title="Ver votantes">'
                    + '<i class="fas fa-plus-circle text-primary mr-1"></i>' + texto(value) + '</a>';
            }},
            {data:'dirigente', render: texto},
            {data:'anotados', render: function (value) { return '<span class="badge badge-secondary badge-pill">' + numero.format(value) + '</span>'; }},
            {data:'votaron', render: function (value) { return '<span class="badge badge-success badge-pill">' + numero.format(value) + '</span>'; }},
            {data:'ausentes', render: function (value) { return '<span class="badge badge-secondary badge-pill">' + numero.format(value) + '</span>'; }},
            {data:'votos_efectivos', render: function (value) { return '<span class="badge badge-primary badge-pill">' + numero.format(value) + '</span>'; }},
            {data:'votos_perdidos', render: function (value) { return '<span class="badge badge-danger badge-pill">' + numero.format(value) + '</span>'; }},
            {data:'ef_mov', render: function (value) { return badgePorcentaje(Number(value) * 100, 100); }},
            {data:'ef_real', render: function (value) { return badgePorcentaje(Number(value) * 100, 100); }},
            {data:'tasa_fuga', render: function (value) { return badgePorcentaje(Number(value) * 100, 100); }},
            {data:'ieg', render: function (value, type, row) { return barraIEG(value, row.color); }, className: 'votante-col'}
        ];

        if (tabla) {
            tabla.clear().rows.add(filas).draw();
            return;
        }
        tabla = $('#tabla-punteros').DataTable({
            data: filas, columns: columnas, language: idioma,
            pageLength: 25, lengthMenu: [10, 25, 50, 100],
            deferRender: true, scrollX: true, order: [[11, 'desc']],
            dom: "<'row'<'col-md-6'l><'col-md-6 text-right'B>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Efectividad por Puntero - ' + nombreCandidato,
                    filename: 'Efectividad_Puntero_' + nombreCandidato.replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0,10),
                    exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11] }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Efectividad por Puntero - ' + nombreCandidato,
                    filename: 'Efectividad_Puntero_' + nombreCandidato.replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0,10),
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11] }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn btn-secondary btn-sm',
                    exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11] }
                }
            ]
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
            var sistemaId = $('#sistema_id').val();
            if (sistemaId) params.set('sistema_id', sistemaId);
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
            datosGlobales = data;

            $('#metrica-anotados').text(numero.format(r.anotados));
            $('#metrica-votaron').text(numero.format(r.votaron));
            $('#metrica-ausentes').text(numero.format(r.ausentes));
            $('#metrica-efectivos').text(numero.format(r.votos_efectivos));
            $('#metrica-perdidos').text(numero.format(r.votos_perdidos));
            $('#metrica-ieg').text(numero.format(r.ieg));

            $('#reporte').prop('hidden', false);
            $('#aviso-sin-carga').prop('hidden', data.tiene_carga)
                .text(data.tiene_carga ? '' : (data.mensaje_sin_carga || ''));
            buildTabla(data.punteros);
            actualizarEstadoSelectAllPunteros();
            actualizarInfoPunteros();
            $('#btnVerTodosVotantes').prop('disabled', false);
            $('#btnExportarDetalles').prop('disabled', false);
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

    // Expandir/contraer el detalle de votantes de un puntero
    $('body').on('click', '.toggle-votantes', function (e) {
        e.preventDefault();
        if (!tabla) return;
        var $el = $(this);
        var $tr = $el.closest('tr');
        var row = tabla.row($tr);
        var icono = $el.find('i');
        if (row.child.isShown()) {
            row.child.hide();
            $tr.removeClass('shown');
            icono.removeClass('fa-minus-circle text-warning').addClass('fa-plus-circle text-primary');
        } else {
            row.child(buildChildRow(row.data())).show();
            $tr.addClass('shown');
            icono.removeClass('fa-plus-circle text-primary').addClass('fa-minus-circle text-warning');
        }
    });

    $('#filterForm').on('submit', function (e) {
        e.preventDefault();
        cargar();
    });

    $('#btnRefresh').on('click', cargar);

    // ── Modal "Ver / Exportar Todos los Votantes" ──
    function aplanarVotantes() {
        if (!datosGlobales) return [];
        var filas = [];
        (datosGlobales.punteros || []).forEach(function (p) {
            (p.votantes || []).forEach(function (v) {
                filas.push({
                    puntero: p.nombre,
                    dirigente: p.dirigente || '',
                    votante: v.nombre,
                    cedula: v.cedula,
                    mesa: v.mesa || '',
                    escuela: v.escuela || '',
                    voto: v.voto ? 'Sí' : 'No',
                    tasa: (v.tasa * 100).toFixed(0) + '%',
                    fidelidad: (v.p_fidelidad * 100).toFixed(1) + '%',
                    fuga: (v.p_fuga * 100).toFixed(1) + '%',
                    clasificacion: v.clasificacion || ''
                });
            });
        });
        return filas;
    }

    function abrirModalVotantes() {
        var filas = aplanarVotantes();
        var nombreCandidato = datosGlobales ? (datosGlobales.candidato.nombre + (datosGlobales.candidato.partido ? ' ' + datosGlobales.candidato.partido : '')) : 'Reporte';
        $('#modalVotantesLabel').html('<i class="fas fa-list"></i> Votantes - ' + nombreCandidato);

        if (!tablaVotantes) {
            tablaVotantes = $('#tabla-votantes-modal').DataTable({
                data: filas,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                deferRender: true,
                scrollX: true,
                language: idioma,
                columns: [
                    {data: 'puntero'},
                    {data: 'dirigente'},
                    {data: 'votante'},
                    {data: 'cedula'},
                    {data: 'mesa'},
                    {data: 'escuela'},
                    {data: 'voto', className: 'text-center'},
                    {data: 'tasa', className: 'text-right'},
                    {data: 'fidelidad', className: 'text-right'},
                    {data: 'fuga', className: 'text-right'},
                    {data: 'clasificacion'}
                ],
                dom: "<'row'<'col-md-6'f><'col-md-6 text-right'B>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Votantes - ' + nombreCandidato,
                        filename: 'Votantes_' + nombreCandidato.replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0,10),
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Votantes - ' + nombreCandidato,
                        filename: 'Votantes_' + nombreCandidato.replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0,10),
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-secondary btn-sm'
                    }
                ]
            });
        } else {
            tablaVotantes.clear().rows.add(filas).draw();
        }

        $('#modalVotantes').modal('show');
    }

    $('#btnVerTodosVotantes').on('click', function () {
        abrirModalVotantes();
    });

    // ── Modal "Exportar Detalles": cada puntero con sus votantes ──
    function aplanarDetalles() {
        if (!datosGlobales) return [];
        var filas = [];
        (datosGlobales.punteros || []).forEach(function (p, i) {
            var tieneVotantes = p.votantes && p.votantes.length > 0;
            filas.push({
                grupo: i,
                esPuntero: true,
                puntero: tieneVotantes ? p.nombre : (p.nombre + ' (sin votantes)'),
                dirigente: p.dirigente || ''
            });
            (tieneVotantes ? p.votantes : []).forEach(function (v) {
                filas.push({
                    grupo: i,
                    esPuntero: false,
                    puntero: '',
                    dirigente: '',
                    votante: v.nombre,
                    cedula: v.cedula,
                    mesa: v.mesa || '',
                    escuela: v.escuela || '',
                    voto: v.voto ? 'Sí' : 'No',
                    tasa: (v.tasa * 100).toFixed(0) + '%',
                    fidelidad: (v.p_fidelidad * 100).toFixed(1) + '%',
                    fuga: (v.p_fuga * 100).toFixed(1) + '%',
                    clasificacion: v.clasificacion || ''
                });
            });
        });
        return filas;
    }

    function gruposSeleccionados(dt) {
        var grupos = [];
        dt.rows().every(function () {
            var data = this.data();
            if (data.esPuntero && seleccionDetalles[String(data.grupo)] !== false) {
                grupos.push(String(data.grupo));
            }
        });
        return grupos;
    }

    function indicesDeGrupos(dt, grupos) {
        var set = {};
        grupos.forEach(function (g) { set[g] = true; });
        var indices = [];
        dt.rows().every(function (idx) {
            if (set[String(this.data().grupo)]) {
                indices.push(idx);
            }
        });
        return indices;
    }

    function actualizarInfoSeleccion() {
        if (!tablaDetalles) return;
        var total = 0;
        var marcados = 0;
        tablaDetalles.rows().every(function () {
            var data = this.data();
            if (data.esPuntero) {
                total++;
                if (seleccionDetalles[String(data.grupo)] !== false) marcados++;
            }
        });
        $('#infoSeleccionDetalles').text(marcados + ' / ' + total + ' punteros marcados');
    }

    function exportOptionsSeleccion(dt) {
        var grupos = gruposSeleccionados(dt);
        if (!grupos.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin selección',
                text: 'Marcá al menos un puntero para exportar.',
                confirmButtonColor: '#3085d6'
            });
            return null;
        }
        return {
            rows: indicesDeGrupos(dt, grupos),
            columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
            modifier: { search: 'none', order: 'applied' }
        };
    }

    function abrirModalDetalles() {
        var filas = aplanarDetalles();
        filas.forEach(function (fila) {
            if (fila.esPuntero && seleccionDetalles[String(fila.grupo)] === undefined) {
                seleccionDetalles[String(fila.grupo)] = true;
            }
        });
        var nombreCandidato = datosGlobales ? (datosGlobales.candidato.nombre + (datosGlobales.candidato.partido ? ' ' + datosGlobales.candidato.partido : '')) : 'Reporte';
        $('#modalDetallesLabel').html('<i class="fas fa-file-export"></i> Detalles por Puntero - ' + nombreCandidato);

        var botonExcel = '<i class="fas fa-file-excel"></i> Excel';
        var botonPdf = '<i class="fas fa-file-pdf"></i> PDF';
        var botonPrint = '<i class="fas fa-print"></i> Imprimir';
        var nombreArchivo = 'Detalles_Efectividad_' + nombreCandidato.replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0, 10);
        var tituloExport = 'Detalles por Puntero - ' + nombreCandidato;

        if (!tablaDetalles) {
            tablaDetalles = $('#tabla-detalles-modal').DataTable({
                data: filas,
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                deferRender: true,
                scrollX: true,
                order: [],
                ordering: false,
                language: idioma,
                columns: [
                    {
                        data: null, orderable: false, searchable: false, className: 'text-center', width: '30px',
                        render: function (data, type, row) {
                            if (type !== 'display' || !row.esPuntero) return '';
                            var marcado = seleccionDetalles[String(row.grupo)] !== false;
                            return '<input type="checkbox" class="chx-puntero" data-grupo="' + row.grupo + '"' + (marcado ? ' checked' : '') + '>';
                        }
                    },
                    {data: 'puntero', defaultContent: ''},
                    {data: 'dirigente', defaultContent: ''},
                    {data: 'votante', defaultContent: ''},
                    {data: 'cedula', defaultContent: ''},
                    {data: 'mesa', defaultContent: ''},
                    {data: 'escuela', defaultContent: ''},
                    {data: 'voto', className: 'text-center', defaultContent: ''},
                    {data: 'tasa', className: 'text-right', defaultContent: ''},
                    {data: 'fidelidad', className: 'text-right', defaultContent: ''},
                    {data: 'fuga', className: 'text-right', defaultContent: ''},
                    {data: 'clasificacion', defaultContent: ''}
                ],
                createdRow: function (row, data) {
                    if (data.esPuntero) {
                        $(row).addClass('tabla-detalle-puntero');
                    }
                },
                dom: "<'row'<'col-md-6'f><'col-md-6 text-right'B>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: botonExcel,
                        className: 'btn btn-success btn-sm',
                        title: tituloExport,
                        filename: nombreArchivo,
                        action: function (e, dt, node, config) {
                            var opts = exportOptionsSeleccion(dt);
                            if (!opts) return;
                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, node, $.extend({}, config, { exportOptions: opts }));
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: botonPdf,
                        className: 'btn btn-danger btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: tituloExport,
                        filename: nombreArchivo,
                        action: function (e, dt, node, config) {
                            var opts = exportOptionsSeleccion(dt);
                            if (!opts) return;
                            $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, node, $.extend({}, config, { exportOptions: opts }));
                        }
                    },
                    {
                        extend: 'print',
                        text: botonPrint,
                        className: 'btn btn-secondary btn-sm',
                        autoPrint: true,
                        action: function (e, dt, node, config) {
                            var opts = exportOptionsSeleccion(dt);
                            if (!opts) return;
                            $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, node, $.extend({}, config, { exportOptions: opts }));
                        }
                    }
                ],
                drawCallback: function () {
                    actualizarInfoSeleccion();
                }
            });
        } else {
            tablaDetalles.clear().rows.add(filas).draw();
        }

        $('#modalDetalles').modal('show');
        actualizarInfoSeleccion();
    }

    $('#btnExportarDetalles').on('click', function () {
        abrirModalDetalles();
    });

    $('#chkMarcarTodosDetalles').on('change', function () {
        if (!tablaDetalles) return;
        var marcar = this.checked;
        tablaDetalles.rows().every(function () {
            var data = this.data();
            if (data.esPuntero) {
                seleccionDetalles[String(data.grupo)] = marcar;
                $(this.node()).find('.chx-puntero').prop('checked', marcar);
            }
        });
        actualizarInfoSeleccion();
    });

    $('#btnMarcarTodos').on('click', function () {
        $('#chkMarcarTodosDetalles').prop('checked', true).trigger('change');
    });

    $('#btnDesmarcarTodos').on('click', function () {
        $('#chkMarcarTodosDetalles').prop('checked', false).trigger('change');
    });

    $(document).on('change', '.chx-puntero', function () {
        var grupo = String($(this).data('grupo'));
        seleccionDetalles[grupo] = $(this).is(':checked');

        var total = tablalengthPunteros();
        if (total > 0) {
            var marcados = 0;
            tablaDetalles.rows().every(function () {
                var data = this.data();
                if (data.esPuntero && seleccionDetalles[String(data.grupo)] !== false) marcados++;
            });
            $('#chkMarcarTodosDetalles').prop('indeterminate', marcados > 0 && marcados < total);
            $('#chkMarcarTodosDetalles').prop('checked', marcados === total);
        }
        actualizarInfoSeleccion();
    });

    // ── Marcado de punteros directamente en la tabla principal ──
    function actualizarInfoPunteros() {
        if (!tabla) {
            $('#infoSeleccionPunteros').text('');
            return;
        }
        var total = 0;
        var marcados = 0;
        tabla.rows().every(function () {
            var data = this.data();
            if (data.grupo !== undefined) {
                total++;
                if (seleccionDetalles[String(data.grupo)] !== false) marcados++;
            }
        });
        $('#infoSeleccionPunteros').text(total ? (marcados + ' de ' + total + ' punteros marcados') : '');
    }

    function actualizarEstadoSelectAllPunteros() {
        if (!tabla) return;
        var total = 0;
        var marcados = 0;
        tabla.rows().every(function () {
            var data = this.data();
            if (data.grupo !== undefined) {
                total++;
                if (seleccionDetalles[String(data.grupo)] !== false) marcados++;
            }
        });
        $('#chkMarcarTodosPunteros').prop('indeterminate', marcados > 0 && marcados < total);
        $('#chkMarcarTodosPunteros').prop('checked', total > 0 && marcados === total);
    }

    $('#chkMarcarTodosPunteros').on('change', function () {
        if (!tabla) return;
        var marcar = this.checked;
        tabla.rows().every(function () {
            var data = this.data();
            if (data.grupo !== undefined) {
                seleccionDetalles[String(data.grupo)] = marcar;
                $(this.node()).find('.chx-puntero-principal').prop('checked', marcar);
            }
        });
        actualizarEstadoSelectAllPunteros();
        actualizarInfoPunteros();
    });

    $(document).on('change', '.chx-puntero-principal', function () {
        var grupo = String($(this).data('grupo'));
        seleccionDetalles[grupo] = $(this).is(':checked');
        actualizarEstadoSelectAllPunteros();
        actualizarInfoPunteros();
    });

    function tablalengthPunteros() {
        if (!tablaDetalles) return 0;
        var total = 0;
        tablaDetalles.rows().every(function () {
            if (this.data().esPuntero) total++;
        });
        return total;
    }
});
</script>
@endpush