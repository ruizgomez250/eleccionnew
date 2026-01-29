@extends('adminlte::page')

@section('title', 'Editar Vehículo')

@section('content_header')
    <h1>
        <i class="fas fa-car"></i> Editar Vehículo
    </h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('vehiculo.update', $vehiculo->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">

                {{-- Cédula Chofer --}}
                <div class="col-md-2">
                    <input type="text"
                        name="cedulachofer"
                        class="form-control"
                        value="{{ $vehiculo->cedulachofer }}"
                        placeholder="Cédula"
                        required>
                </div>

                {{-- Nombre Chofer --}}
                <div class="col-md-3">
                    <input type="text"
                        name="nombre"
                        class="form-control"
                        value="{{ $vehiculo->nombre }}"
                        placeholder="Nombre del Chofer"
                        required>
                </div>

                {{-- Chapa --}}
                <div class="col-md-2">
                    <input type="text"
                        name="chapa"
                        class="form-control"
                        value="{{ $vehiculo->chapa }}"
                        placeholder="Chapa"
                        required>
                </div>

                {{-- Tipo Vehículo --}}
                <div class="col-md-2">
                    <select name="tipovehiculo" class="form-control" required>
                        <option value="AUTOMOVIL"
                            {{ $vehiculo->tipovehiculo == 'AUTOMOVIL' ? 'selected' : '' }}>
                            AUTOMÓVIL
                        </option>
                        <option value="CAMIONETA"
                            {{ $vehiculo->tipovehiculo == 'CAMIONETA' ? 'selected' : '' }}>
                            CAMIONETA
                        </option>
                        <option value="FURGONETA"
                            {{ $vehiculo->tipovehiculo == 'FURGONETA' ? 'selected' : '' }}>
                            FURGONETA
                        </option>
                    </select>
                </div>

                {{-- Teléfono 1 --}}
                <div class="col-md-2">
                    <input type="text"
                        name="telefono1"
                        class="form-control"
                        value="{{ $vehiculo->telefono1 }}"
                        placeholder="Teléfono 1">
                </div>

                {{-- Botón --}}
                <div class="col-md-1">
                    <button class="btn btn-primary w-100">
                        <i class="fas fa-save"></i>
                    </button>
                </div>

            </div>

            <hr>

            <div class="row mt-2">

                {{-- Teléfono 2 --}}
                <div class="col-md-2">
                    <input type="text"
                        name="telefono2"
                        class="form-control"
                        value="{{ $vehiculo->telefono2 }}"
                        placeholder="Teléfono 2">
                </div>

                {{-- Teléfono 3 --}}
                <div class="col-md-2">
                    <input type="text"
                        name="telefono3"
                        class="form-control"
                        value="{{ $vehiculo->telefono3 }}"
                        placeholder="Teléfono 3">
                </div>

                {{-- Monto a pagar --}}
                <div class="col-md-2">
                    <input type="number"
                        name="montopagar"
                        class="form-control"
                        value="{{ $vehiculo->montopagar }}"
                        placeholder="Monto">
                </div>

                {{-- Cantidad de pagos --}}
                <div class="col-md-2">
                    <input type="number"
                        name="cantidadpagos"
                        class="form-control"
                        value="{{ $vehiculo->cantidadpagos }}"
                        placeholder="Pagos">
                </div>

            </div>

        </form>

    </div>
</div>

@stop
