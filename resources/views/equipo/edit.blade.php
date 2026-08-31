@extends('adminlte::page')

@section('title', 'Editar colegio electoral')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Editar Colegio electoral</h1>
@stop

@section('content')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('equipo.update', $equipo->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">

                    {{-- Descripción --}}
                    <x-adminlte-input name="descripcion" label="Descripción del Colegio electoral" placeholder="Descripción"
                        value="{{ $equipo->descripcion }}" fgroup-class="col-md-3" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text">
                                <i class="fas fa-users"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    {{-- Sistema --}}
                    <x-adminlte-input name="sist" label="Sistema" placeholder="Sistema" value="{{ $equipo->sist }}"
                        fgroup-class="col-md-2">
                        <x-slot name="prependSlot">
                            <div class="input-group-text">
                                <i class="fas fa-cogs"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    {{-- Colegio --}}
                    <x-adminlte-input name="colegio" label="Colegio" placeholder="Colegio" value="{{ $equipo->colegio }}"
                        fgroup-class="col-md-3">
                        <x-slot name="prependSlot">
                            <div class="input-group-text">
                                <i class="fas fa-school"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    {{-- Ciudad --}}
                    <x-adminlte-input name="ciudad" label="Ciudad" placeholder="Ciudad" value="{{ $equipo->ciudad }}"
                        fgroup-class="col-md-2">
                        <x-slot name="prependSlot">
                            <div class="input-group-text">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    {{-- Botón --}}
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Actualizar
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>

@stop
