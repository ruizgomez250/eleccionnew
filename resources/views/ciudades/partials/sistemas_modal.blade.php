@if ($sistemas->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover table-striped">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Nombre del Sistema</th>
                    <th>Tipo</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sistemas as $index => $sistema)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $sistema->nombre }}</td>
                        <td>{{ $sistema->tipo ?? 'No especificado' }}</td>
                        <td>

                            {{-- BOTON DIRIGENTES --}}
                            <button class="btn btn-warning btn-sm btn-dirigentes" data-sistema="{{ $sistema->id }}"
                                data-nombre="{{ $sistema->nombre }}" data-toggle="modal"
                                data-target="#modalDirigentes">

                                <i class="fas fa-users"></i> Dirigentes

                            </button>

                            {{-- BOTON PUNTEROS --}}
                            <button class="btn btn-sm btn-info btn-punteros" data-sistema-id="{{ $sistema->id }}"
                                onclick="abrirModalPunterosLista({{ $sistema->id }}, '{{ $sistema->nombre }}')">
                                <i class="fas fa-users"></i> Punteros
                            </button>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-info text-center mb-0">
        <i class="fas fa-info-circle"></i> No hay sistemas disponibles para este distrito.
    </div>
@endif
