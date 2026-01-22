@extends('adminlte::page')

@section('title', 'Editar Equipo')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Editar Equipo</h1>
@stop

@section('content')

<div class="card">
    <div class="card-body">
        <form action="{{ route('equipo.update', $equipo->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="descripcion" class="form-control"
                        value="{{ $equipo->descripcion }}" required>
                </div>

                <div class="col-md-2">
                    <input type="text" name="sist" class="form-control"
                        value="{{ $equipo->sist }}">
                </div>

                <div class="col-md-3">
                    <input type="text" name="colegio" class="form-control"
                        value="{{ $equipo->colegio }}">
                </div>

                <div class="col-md-2">
                    <input type="text" name="ciudad" class="form-control"
                        value="{{ $equipo->ciudad }}">
                </div>

                <div class="col-md-2">
                    <button class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@stop
