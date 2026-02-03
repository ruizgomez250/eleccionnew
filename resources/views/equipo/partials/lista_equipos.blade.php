<div class="row">
    @foreach ($equipos as $equipo)
        <div class="col-md-3 mb-4 equipo-card" data-search="{{ strtolower($equipo->descripcion . ' ' . $equipo->ciudad) }}">
            <div class="card shadow h-100">
                <div class="card-header text-center bg-light">
                    <i class="fas fa-users fa-3x text-primary"></i>
                </div>
                <div class="card-body text-center">
                    <h5 class="font-weight-bold">{{ $equipo->descripcion }}</h5>
                    <p class="mb-0">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $equipo->ciudad ?? '—' }}
                    </p>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('dirigente.createWithEquipo', $equipo->id) }}"
                        class="btn btn-sm btn-outline-primary mb-1 w-100">
                        <i class="fas fa-user-tie"></i> Agregar Dirigente
                    </a>
                    <a href="{{ route('puntero.createWithEquipo', $equipo->id) }}"
                        class="btn btn-sm btn-outline-success mb-1 w-100">
                        <i class="fas fa-user-plus"></i> Agregar Puntero
                    </a>
                    <div class="btn-group w-100 mt-2">
                        <a href="{{ route('equipo.edit', $equipo->id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('equipo.destroy', $equipo->id) }}" method="POST"
                            class="form-delete d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $equipos->links('pagination::bootstrap-4') }}
</div>
