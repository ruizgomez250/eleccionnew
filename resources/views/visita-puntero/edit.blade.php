@extends('adminlte::page')

@section('title', 'Editar Visita')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-edit"></i> Editar Visita #{{ $visita->id }}
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

    <form action="{{ route('visita-puntero.update', $visita->id) }}" method="POST" id="formVisita">
        @csrf
        @method('PUT')
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
                                    <option value="{{ $puntero->id }}" {{ old('idpuntero', $visita->idpuntero) == $puntero->id ? 'selected' : '' }}>
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
                            <input type="text" name="casa_de" class="form-control" value="{{ old('casa_de', $visita->casa_de) }}">
                        </div>
                        <div class="form-group">
                            <label><strong>Cédula del Votante</strong></label>
                            <input type="text" name="cedula_votante" class="form-control" value="{{ old('cedula_votante', $visita->cedula_votante) }}">
                        </div>
                        <div class="form-group">
                            <label><strong>Dirección</strong></label>
                            <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $visita->direccion) }}">
                        </div>
                        <div class="form-group">
                            <label><strong>Referencia</strong></label>
                            <input type="text" name="referencia" class="form-control" value="{{ old('referencia', $visita->referencia) }}">
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
                            <input type="text" name="cedula" class="form-control" value="{{ old('cedula', $visita->cedula) }}" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Nombre *</strong></label>
                            <input type="text" name="nombre_votante" class="form-control" value="{{ old('nombre_votante', $visita->nombre_votante) }}" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Apellido</strong></label>
                            <input type="text" name="apellido_votante" class="form-control" value="{{ old('apellido_votante', $visita->apellido_votante) }}">
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
                            <input type="datetime-local" name="fecha_visita" class="form-control"
                                value="{{ old('fecha_visita', $visita->fecha_visita ? $visita->fecha_visita->format('Y-m-d\TH:i') : '') }}" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Resultado de la Visita *</strong></label>
                            <input type="text" name="resultado" class="form-control" value="{{ old('resultado', $visita->resultado) }}" required>
                        </div>
                        <div class="form-group">
                            <label><strong>Observación</strong></label>
                            <textarea name="observacion" class="form-control" rows="3">{{ old('observacion', $visita->observacion) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label><strong>Próxima Visita Agendada</strong></label>
                            <input type="datetime-local" name="proxima_visita" class="form-control"
                                value="{{ old('proxima_visita', $visita->proxima_visita ? $visita->proxima_visita->format('Y-m-d\TH:i') : '') }}">
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
                                <i class="fas fa-crosshairs"></i> Actualizar Ubicación
                            </button>
                            <div id="gpsStatus" class="mt-2"></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Latitud</strong></label>
                                    <input type="text" name="latitud" id="latitud" class="form-control" value="{{ old('latitud', $visita->latitud) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><strong>Longitud</strong></label>
                                    <input type="text" name="longitud" id="longitud" class="form-control" value="{{ old('longitud', $visita->longitud) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><strong>Precisión GPS (metros)</strong></label>
                            <input type="text" name="precision_gps" id="precision_gps" class="form-control" value="{{ old('precision_gps', $visita->precision_gps) }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 text-center mb-4">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Actualizar Visita
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

        $('#btnObtenerGPS').on('click', function() {
            let btn = $(this);
            let status = $('#gpsStatus');

            if (!navigator.geolocation) {
                status.html('<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> GPS no soportado</div>');
                return;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Obteniendo...');
            status.html('<div class="text-info"><i class="fas fa-info-circle"></i> Detectando ubicación...</div>');

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    $('#latitud').val(position.coords.latitude.toFixed(7));
                    $('#longitud').val(position.coords.longitude.toFixed(7));
                    $('#precision_gps').val(position.coords.accuracy ? position.coords.accuracy.toFixed(2) : '');
                    btn.prop('disabled', false).html('<i class="fas fa-crosshairs"></i> Actualizar Ubicación');
                    status.html('<div class="text-success"><i class="fas fa-check-circle"></i> Ubicación actualizada</div>');
                    setTimeout(function() { status.html(''); }, 3000);
                },
                function(error) {
                    let msg = 'Error al obtener ubicación';
                    if (error.code === 1) msg = 'Permiso denegado';
                    else if (error.code === 2) msg = 'Ubicación no disponible';
                    else if (error.code === 3) msg = 'Tiempo agotado';
                    btn.prop('disabled', false).html('<i class="fas fa-crosshairs"></i> Actualizar Ubicación');
                    status.html('<div class="text-danger"><i class="fas fa-exclamation-triangle"></i> ' + msg + '</div>');
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });
    });
</script>
@stop
