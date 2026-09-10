@extends('adminlte::page')

@section('title', 'Participación general')
@section('plugins.Datatables', true)

@section('content_header')
    <h1>Participación general</h1>
@stop

@section('content')
    <p class="text-muted">Resumen del sistema asignado a tu usuario. Sólo muestra cifras agrupadas, sin detalle de votantes.</p>
    <div class="d-flex align-items-center flex-wrap mb-3">
        <button id="actualizar" class="btn btn-primary mr-3" type="button"><i class="fas fa-sync-alt" aria-hidden="true"></i> Actualizar</button>
        <span id="estado" role="status" aria-live="polite">Cargando resumen…</span>
    </div>
    <div id="error-reporte" class="alert alert-danger" role="alert" hidden></div>
    <div id="reporte" hidden>
        <div class="row">
            @foreach(['total' => 'Personas únicas registradas', 'registrados' => 'Con participación registrada', 'sin_registro' => 'Sin participación registrada', 'porcentaje' => 'Participación registrada'] as $key => $label)
                <div class="col-sm-6 col-xl-3">
                    <div class="small-box bg-white border shadow-sm"><div class="inner">
                        <h3 id="metrica-{{ $key }}">0</h3><p>{{ $label }}</p>
                    </div></div>
                </div>
            @endforeach
        </div>
        <div class="alert alert-light border">
            “Sin participación registrada” significa que no hay una carga coincidente; no confirma que la persona no haya votado.
            Cada persona se cuenta una vez dentro de cada grupo. Si figura en varios grupos, las filas no suman necesariamente el total general.
            Los datos pueden tener hasta un minuto de antigüedad.
        </div>
        <div class="card">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" role="tablist">
                    @foreach(['general' => 'Resumen', 'dirigentes' => 'Por dirigente', 'punteros' => 'Por puntero'] as $key => $label)
                        <li class="nav-item"><a id="tab-{{ $key }}" class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab" href="#panel-{{ $key }}" role="tab" aria-controls="panel-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="card-body tab-content">
                <div id="panel-general" class="tab-pane fade show active" role="tabpanel" aria-labelledby="tab-general">
                    <div class="row align-items-center">
                        <div class="col-md-5 text-center mb-3">
                            <div id="grafico-general" class="participacion-donut mx-auto" role="img" aria-label="Participación registrada"><strong id="donut-porcentaje">0%</strong></div>
                        </div>
                        <div class="col-md-7">
                            <h4>Participación registrada</h4>
                            <p><span class="leyenda bg-primary"></span> Con registro de participación</p>
                            <p><span class="leyenda" style="background:#dee2e6"></span> Sin registro de participación</p>
                            <p id="resumen-grupos" class="text-muted"></p>
                            <p id="sin-datos" hidden>No hay personas registradas para este sistema.</p>
                        </div>
                    </div>
                </div>
                @foreach(['dirigentes' => 'Dirigente', 'punteros' => 'Puntero'] as $key => $label)
                    <div id="panel-{{ $key }}" class="tab-pane fade" role="tabpanel" aria-labelledby="tab-{{ $key }}">
                        <p class="text-muted">La barra azul muestra la proporción con participación registrada. Podés buscar y ordenar los grupos.</p>
                        <table id="tabla-{{ $key }}" class="table table-striped table-bordered w-100">
                            <thead><tr><th>{{ $label }}</th>@if($key === 'punteros')<th>Dirigente</th>@endif<th>Personas únicas</th><th>Con registro</th><th>Sin registro</th><th>Participación</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop

@push('css')
<style>
    .participacion-donut { width:220px; height:220px; border-radius:50%; background:conic-gradient(#007bff 0%, #dee2e6 0%); display:flex; align-items:center; justify-content:center; }
    .participacion-donut strong { display:flex; align-items:center; justify-content:center; width:160px; height:160px; background:white; border-radius:50%; font-size:2rem; }
    .leyenda { display:inline-block; width:14px; height:14px; border-radius:3px; margin-right:6px; }
    .participacion-barra { min-width:130px; height:12px; background:#dee2e6; border-radius:6px; overflow:hidden; }
    .participacion-barra span { display:block; height:100%; background:#007bff; }
</style>
@endpush

@push('js')
<script>
$(function () {
    const tablas = {};
    const numero = new Intl.NumberFormat('es-PY');
    const texto = $.fn.dataTable.render.text();
    const idioma = {
        search: 'Buscar:', lengthMenu: 'Mostrar _MENU_ grupos', info: '_START_ a _END_ de _TOTAL_ grupos',
        infoEmpty: 'Sin grupos', infoFiltered: '(de _MAX_ grupos)', zeroRecords: 'No se encontraron grupos',
        emptyTable: 'No hay grupos para mostrar', paginate: {first:'Primero', last:'Último', next:'Siguiente', previous:'Anterior'}
    };
    function tabla(tipo, filas) {
        if (tablas[tipo]) { tablas[tipo].clear().rows.add(filas).draw(); return; }
        const columnas = [{data:'nombre', render:texto}];
        if (tipo === 'punteros') columnas.push({data:'dirigente', render:texto});
        ['total', 'registrados', 'sin_registro'].forEach(key => columnas.push({data:key}));
        columnas.push({data:'porcentaje', render: function (value, type) {
            const n = Math.max(0, Math.min(100, Number(value) || 0));
            if (type !== 'display') return n;
            return '<span>'+numero.format(n)+'%</span><div class="participacion-barra" aria-hidden="true"><span style="width:'+n+'%"></span></div>';
        }});
        tablas[tipo] = $('#tabla-'+tipo).DataTable({data:filas, columns:columnas, language:idioma,
            pageLength:10, lengthMenu:[10,25,50], deferRender:true, scrollX:true, order:[[0,'asc']]});
    }
    async function cargar() {
        $('#actualizar').prop('disabled', true);
        $('#estado').text('Cargando resumen…');
        $('#error-reporte').prop('hidden', true);
        try {
            const response = await fetch(@json(route('reportes.participacion-general.data')), {headers:{Accept:'application/json'}, credentials:'same-origin'});
            if (!response.ok) {
                const detalle = await response.json().catch(() => ({}));
                const mensaje = response.status === 403 ? 'No tenés permiso Reportes o un sistema asignado.'
                    : (typeof detalle.message === 'string' && detalle.message.startsWith('No se pudo generar el reporte. Referencia:')
                        ? detalle.message : 'No se pudo cargar el reporte (HTTP '+response.status+'). Intentá nuevamente.');
                throw new Error(mensaje);
            }
            const data = await response.json();
            Object.entries(data.resumen).forEach(([key,value]) => $('#metrica-'+key).text(numero.format(value)+(key === 'porcentaje' ? '%' : '')));
            const porcentaje = Math.max(0, Math.min(100, Number(data.resumen.porcentaje) || 0));
            $('#grafico-general').css('background', 'conic-gradient(#007bff '+porcentaje+'%, #dee2e6 '+porcentaje+'%)')
                .attr('aria-label', numero.format(porcentaje)+'% con participación registrada');
            $('#donut-porcentaje').text(numero.format(porcentaje)+'%');
            $('#resumen-grupos').text(numero.format(data.dirigentes.length)+' dirigentes con punteros y '+numero.format(data.punteros.length)+' punteros en el resumen.');
            $('#sin-datos').prop('hidden', data.resumen.total !== 0);
            $('#reporte').prop('hidden', false);
            tabla('dirigentes', data.dirigentes);
            tabla('punteros', data.punteros);
            $('#estado').text('Datos al '+new Date(data.generado_en).toLocaleString('es-PY'));
        } catch (error) {
            $('#reporte').prop('hidden', true);
            $('#error-reporte').text(error.message).prop('hidden', false);
            $('#estado').text('Carga incompleta');
        } finally { $('#actualizar').prop('disabled', false); }
    }
    $('a[data-toggle="tab"]').on('shown.bs.tab', function () { Object.values(tablas).forEach(t => t.columns.adjust()); });
    $('#actualizar').on('click', cargar);
    cargar();
});
</script>
@endpush
