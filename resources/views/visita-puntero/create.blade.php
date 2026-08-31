@extends('adminlte::page')

@section('title', 'Registrar Visita')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-clipboard-check"></i> Registrar Visita de Puntero
        </h4>
        <a href="{{ route('visita-puntero.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @if(session('errorAlert'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-triangle"></i> {{ session('errorAlert') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('visita-puntero.store') }}" method="POST" id="formVisita">
        @csrf
        <div class="row">
            {{-- DATOS DEL PUNTERO --}}
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h6 class="card-title"><i class="fas fa-user"></i> Puntero que realiza la visita</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><strong>Seleccionar Puntero *</strong></label>
                            <select name="idpuntero" id="idpuntero" class="form-control select2" required>
                                <option value="">Seleccione un puntero...</option>
                                @foreach($punteros as $puntero)
                                    <option value="{{ $puntero->id }}" {{ old('idpuntero', $punteroSeleccionado) == $puntero->id ? 'selected' : '' }}>
                                        {{ $puntero->nombre }} - C.I. {{ $puntero->cedula }} ({{ $puntero->dirigente->nombre ?? 'Sin dirigente' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DATOS DE LA CASA --}}
            <div class="col-md-6">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h6 class="card-title"><i class="fas fa-home"></i> Datos de la Casa</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><strong>Casa de</strong></label>
                            <input type="text" name="casa_de" class="form-control" placeholder="Ej: Casa de Juan Pérez" value="{{ old('casa_de') }}">
                        </div>
                        <div class="form-group">
                            <label><strong>Cédula del Votante</strong></label>
                            <input type="text" name="cedula_votante" class="form-control" placeholder="Cédula del votante" value="{{ old('cedula_votante') }}">
                        </div>
                        <div class="form-group">
                            <label><strong>Dirección</strong></label>
                            <input type="text" name="direccion" class="form-control" placeholder="Dirección de la casa" value="{{ old('direccion') }}">
                        </div>
                        <div class="form-group">
                            <label><strong>Referencia</strong></label>
                            <input type="text" name="referencia" class="form-control" placeholder="Referencia de ubicación" value="{{ old('referencia') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- DATOS DEL VOTANTE --}}
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h6 class="card-title"><i class="fas fa-user-check"></i> Datos del Votante</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><strong>Cédula *</strong></label>
                            <input type="text" name="cedula" id="cedulaVotante" class="form-control" placeholder="Cédula del votante" value="{{ old('cedula') }}" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Nombre *</strong></label>
                            <input type="text" name="nombre_votante" class="form-control" placeholder="Nombre del votante" value="{{ old('nombre_votante') }}" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Apellido</strong></label>
                            <input type="text" name="apellido_votante" class="form-control" placeholder="Apellido del votante" value="{{ old('apellido_votante') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- VISITA --}}
            <div class="col-md-6">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h6 class="card-title"><i class="fas fa-clipboard-list"></i> Datos de la Visita</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><strong>Fecha de Visita *</strong></label>
                            <input type="datetime-local" name="fecha_visita" class="form-control" value="{{ old('fecha_visita', now()->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Resultado de la Visita *</strong></label>
                            <input type="text" name="resultado" class="form-control" placeholder="Ej: positivo, negativo, neutro, más o menos..." value="{{ old('resultado', 'neutro') }}" required>
                            <small class="text-muted">Escriba libremente el resultado (ej: positivo, negativo, neutro, etc.)</small>
                        </div>
                        <div class="form-group">
                            <label><strong>Observación</strong></label>
                            <textarea name="observacion" class="form-control" rows="3" placeholder="Observaciones sobre la visita...">{{ old('observacion') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label><strong>Próxima Visita Agendada</strong></label>
                            <input type="datetime-local" name="proxima_visita" class="form-control" value="{{ old('proxima_visita') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- GPS --}}
            <div class="col-md-6">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h6 class="card-title"><i class="fas fa-map-marker-alt"></i> Ubicación GPS</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <button type="button" id="btnObtenerGPS" class="btn btn-primary btn-lg">
                                <i class="fas fa-crosshairs"></i> Obtener Mi Ubicación
                            </button>
                            <div id="gpsStatus" class="mt-2"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Latitud</strong></label>
                                    <input type="text" name="latitud" id="latitud" class="form-control" placeholder="Latitud" value="{{ old('latitud') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Longitud</strong></label>
                                    <input type="text" name="longitud" id="longitud" class="form-control" placeholder="Longitud" value="{{ old('longitud') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><strong>Precisión GPS (metros)</strong></label>
                            <input type="text" name="precision_gps" id="precision_gps" class="form-control" placeholder="Precisión en metros" value="{{ old('precision_gps') }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 text-center mb-4">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Guardar Visita
                </button>
                <a href="{{ route('visita-puntero.index') }}" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

        // GPS Automático
        $('#btnObtenerGPS').on('click', function() {
            let btn = $(this);
            let status = $('#gpsStatus');

            if (!navigator.geolocation) {
                status.html('<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> GPS no soportado en este navegador</div>');
                return;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Obteniendo ubicación...');
            status.html('<div class="text-info"><i class="fas fa-info-circle"></i> Detectando ubicación...</div>');

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    $('#latitud').val(position.coords.latitude.toFixed(7));
                    $('#longitud').val(position.coords.longitude.toFixed(7));
                    $('#precision_gps').val(position.coords.accuracy ? position.coords.accuracy.toFixed(2) : '');

                    btn.prop('disabled', false).html('<i class="fas fa-crosshairs"></i> Obtener Mi Ubicación');
                    status.html('<div class="text-success"><i class="fas fa-check-circle"></i> Ubicación obtenida correctamente</div>');

                    setTimeout(function() { status.html(''); }, 3000);
                },
                function(error) {
                    let msg = 'Error al obtener ubicación';
                    if (error.code === 1) msg = 'Permiso de ubicación denegado';
                    else if (error.code === 2) msg = 'Ubicación no disponible';
                    else if (error.code === 3) msg = 'Tiempo de espera agotado';

                    btn.prop('disabled', false).html('<i class="fas fa-crosshairs"></i> Obtener Mi Ubicación');
                    status.html('<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + msg + '</div>');
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });

        // Autocompletar por cédula (opcional)
        $('#cedulaVotante').on('blur', function() {
            let cedula = $(this).val();
            if (cedula.length >= 4) {
                $.ajax({
                    url: '/puntero/buscar-personas-padron?nombre=&apellido=',
                    type: 'GET',
                    data: { cedula: cedula },
                    success: function(response) {
                        if (response.success && response.data && response.data.length > 0) {
                            let persona = response.data[0];
                            if (!$('input[name="nombre_votante"]').val()) {
                                $('input[name="nombre_votante"]').val(persona.nombre || '');
                            }
                            if (!$('input[name="apellido_votante"]').val()) {
                                $('input[name="apellido_votante"]').val(persona.apellido || '');
                            }
                            if (!$('input[name="direccion"]').val() && persona.direccion) {
                                $('input[name="direccion"]').val(persona.direccion);
                            }
                        }
                    }
                });
            }
        });
    });
</script>
@stop
