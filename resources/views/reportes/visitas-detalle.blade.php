<div class="table-responsive">
    <table class="table table-sm table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Fecha</th>
                <th>Votante</th>
                <th>Cédula</th>
                <th>Casa de</th>
                <th>Dirección</th>
                <th>Resultado</th>
                <th>Observación</th>
                <th>GPS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($visitas as $visita)
                <tr>
                    <td>{{ $visita->fecha_visita->format('d/m/Y H:i') }}</td>
                    <td>{{ $visita->nombre_votante }} {{ $visita->apellido_votante }}</td>
                    <td>{{ $visita->cedula }}</td>
                    <td>{{ $visita->casa_de ?? '-' }}</td>
                    <td>{{ $visita->direccion ?? '-' }}</td>
                    <td><span class="badge badge-secondary">{{ $visita->resultado }}</span></td>
                    <td>{{ $visita->observacion ?? '-' }}</td>
                    <td>
                        @if($visita->latitud && $visita->longitud)
                            <a href="https://maps.google.com/?q={{ $visita->latitud }},{{ $visita->longitud }}" target="_blank" class="btn btn-xs btn-info">
                                <i class="fas fa-map-marker-alt"></i> Ver
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">No se encontraron visitas</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($visitas->count() > 0)
    <div class="mt-2">
        <small class="text-muted">
            <strong>Total: {{ $visitas->count() }} visita(s)</strong>
            @if($tipo !== 'todas')
                | Filtrado: {{ ucfirst($tipo) }}
            @endif
        </small>
    </div>
@endif
