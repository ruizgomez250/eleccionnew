<div class="row mb-4">
    <div class="col-md-12">
        <strong> Escuela:</strong> {{ $escuela }}
    </div>
</div>

<div class="table-responsive">
    <table class="table table-sm table-bordered">
        <thead class="thead-light">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Cédula</th>
                <th>Mesa</th>
                <th>Orden</th>
                <th>Voto</th>
                <th>Puntero</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($votantes as $votante)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $votante->nombre }}</td>
                    <td>{{ number_format($votante->cedula, 0, ',', '.') }}</td>
                    <td>{{ $votante->mesa }}</td>
                    <td>{{ $votante->orden }}</td>
                    <td>
                        @if($votante->voto == 1)
                            <span class="badge badge-success"> Votó</span>
                        @else
                            <span class="badge badge-danger"> No votó</span>
                        @endif
                    </td>
                    <td>{{ $votante->puntero->nombre ?? 'Sin puntero' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No hay votantes registrados en esta escuela</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
