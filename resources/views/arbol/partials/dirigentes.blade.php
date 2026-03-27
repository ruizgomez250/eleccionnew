{{-- resources/views/arbol/partials/dirigentes.blade.php --}}
<div class="table-responsive">
    <table id="dirigentes-table" class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Punteros</th>
                <th>Votantes</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dirigentes as $index => $dirigente)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dirigente->cedula }}</td>
                    <td>{{ $dirigente->nombre }}</td>
                    <td>{{ $dirigente->telefono ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-info">{{ $dirigente->punteros->count() }}</span>
                    </td>
                    <td>
                        <span class="badge badge-success">{{ $dirigente->punteros->sum(fn($p) => $p->votantes->count()) }}</span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="abrirModalPunterosListapordir({{ $dirigente->id }}, '{{ $dirigente->nombre }}')">
                            <i class="fas fa-users"></i> Ver Punteros
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        <i class="fas fa-info-circle"></i> No hay dirigentes registrados
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>