{{-- resources/views/arbol/partials/punteros.blade.php --}}
<div class="table-responsive">
    <table id="punteros-lista-table" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Barrio</th>
                <th>Votantes</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($punteros as $index => $puntero)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $puntero->cedula }}</td>
                    <td>{{ $puntero->nombre }}</td>
                    <td>{{ $puntero->telefono ?? 'N/A' }}</td>
                    <td>{{ $puntero->barrio ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-success">{{ $puntero->votantes->count() }}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-success" onclick="cargarVotantes({{ $puntero->id }}, '{{ $puntero->nombre }}')">
                            <i class="fas fa-vote-yea"></i> Ver Votantes
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        <i class="fas fa-info-circle"></i> No hay punteros registrados
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>