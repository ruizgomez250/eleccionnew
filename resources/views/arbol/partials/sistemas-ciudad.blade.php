{{-- resources/views/arbol/partials/sistemas-ciudad.blade.php --}}
<div class="table-responsive">
    <table id="sistemas-table" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Responsable</th>
                <th>Dirigentes</th>
                <th>Punteros</th>
                <th>Votantes</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sistemas as $sistema)
                <tr>
                    <td>{{ $sistema->id }}</td>
                    <td><strong>{{ $sistema->nombre }}</strong></td>
                    <td>
                        <span class="badge badge-primary">{{ $sistema->tipo }}</span>
                    </td>
                    <td>{{ $sistema->usuario->name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-warning">{{ $sistema->equipos->flatMap->dirigentes->count() }}</span>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $sistema->equipos->flatMap->dirigentes->sum(fn($d) => $d->punteros->count()) }}</span>
                    </td>
                    <td>
                        <span class="badge badge-success">{{ $sistema->equipos->flatMap->dirigentes->sum(fn($d) => $d->punteros->sum(fn($p) => $p->votantes->count())) }}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="abrirModalPunterosLista({{ $sistema->id }}, '{{ $sistema->nombre }}')">
                            <i class="fas fa-users"></i> Ver Punteros
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        <i class="fas fa-info-circle"></i> No hay sistemas registrados en este distrito
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>