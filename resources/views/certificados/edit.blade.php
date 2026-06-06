@extends('adminlte::page')

@section('title', 'Editar Certificado')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Editar Certificado #{{ $voto->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('certificados.update', $voto->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <x-adminlte-select name="mesa_id" label="Mesa" fgroup-class="col-md-6" required>
                        <option value="">Seleccione una mesa</option>
                        @foreach ($mesas as $mesa)
                            <option value="{{ $mesa->id }}" {{ $voto->mesa_id == $mesa->id ? 'selected' : '' }}>
                                {{ $mesa->codigo_mesa }} - {{ $mesa->ubicacion }}
                            </option>
                        @endforeach
                    </x-adminlte-select>

                    <x-adminlte-select name="partido_id" label="Partido" fgroup-class="col-md-6" required id="partido_id_edit">
                        <option value="">Seleccione un partido</option>
                        @foreach ($partidos as $partido)
                            <option value="{{ $partido->id }}" {{ $voto->partido_id == $partido->id ? 'selected' : '' }}>
                                {{ $partido->nombre_completo }}
                            </option>
                        @endforeach
                    </x-adminlte-select>
                </div>
                <div class="row">
                    <x-adminlte-select name="cargo" label="Cargo" fgroup-class="col-md-4" required id="cargo_edit">
                        <option value="">Seleccione un cargo</option>
                        @foreach ($cargos as $cargo)
                            <option value="{{ $cargo }}" {{ $voto->cargo == $cargo ? 'selected' : '' }}>{{ ucfirst($cargo) }}</option>
                        @endforeach
                    </x-adminlte-select>

                    <x-adminlte-select name="tipo_voto" label="Tipo de Voto" fgroup-class="col-md-4" required id="tipo_voto_edit">
                        <option value="lista" {{ $voto->tipo_voto == 'lista' ? 'selected' : '' }}>Lista</option>
                        <option value="preferencia" {{ $voto->tipo_voto == 'preferencia' ? 'selected' : '' }}>Preferencia</option>
                    </x-adminlte-select>

                    <x-adminlte-input name="cantidad_votos" label="Cantidad de Votos" type="number" min="0"
                        value="{{ $voto->cantidad_votos }}" fgroup-class="col-md-4" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-vote-yea"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
                <div class="row">
                    <x-adminlte-select name="candidato_id" label="Candidato (solo para preferencia)" fgroup-class="col-md-6" id="candidato_edit">
                        <option value="">Seleccione partido y cargo primero</option>
                        @foreach ($candidatos as $candidato)
                            <option value="{{ $candidato->id }}" {{ $voto->candidato_id == $candidato->id ? 'selected' : '' }}>
                                {{ $candidato->numero_orden }}. {{ $candidato->nombre_completo }}
                            </option>
                        @endforeach
                    </x-adminlte-select>

                    <x-adminlte-select name="veedor_id" label="Veedor (opcional)" fgroup-class="col-md-6">
                        <option value="">Sin veedor</option>
                        @foreach ($veedores as $veedor)
                            <option value="{{ $veedor->id }}" {{ $voto->veedor_id == $veedor->id ? 'selected' : '' }}>
                                {{ $veedor->nombre_completo }}
                            </option>
                        @endforeach
                    </x-adminlte-select>
                </div>
                <div class="row mt-4">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('certificados.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Actualizar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        toggleCandidato();

        $('#tipo_voto_edit').on('change', function() {
            toggleCandidato();
        });

        $('#partido_id_edit, #cargo_edit').on('change', function() {
            cargarCandidatos();
        });

        function toggleCandidato() {
            if ($('#tipo_voto_edit').val() === 'preferencia') {
                $('#candidato_edit').closest('.form-group').show();
            } else {
                $('#candidato_edit').closest('.form-group').hide();
                $('#candidato_edit').val('');
            }
        }

        function cargarCandidatos() {
            var partidoId = $('#partido_id_edit').val();
            var cargo = $('#cargo_edit').val();
            var $select = $('#candidato_edit');
            var selectedId = {{ $voto->candidato_id ?? 'null' }};

            if (partidoId && cargo) {
                $.get('{{ route('certificados.candidatos') }}', { partido_id: partidoId, cargo: cargo }, function(data) {
                    $select.empty().append('<option value="">Seleccione un candidato</option>');
                    $.each(data, function(i, c) {
                        var selected = c.id == selectedId ? 'selected' : '';
                        $select.append('<option value="' + c.id + '" ' + selected + '>' + c.numero_orden + '. ' + c.nombre_completo + '</option>');
                    });
                });
            } else {
                $select.empty().append('<option value="">Seleccione partido y cargo primero</option>');
            }
        }
    });
</script>
@stop
