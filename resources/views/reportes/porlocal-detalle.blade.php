<div class="row mb-4">
    <div class="col-md-4">
        <strong>🏫 Colegio:</strong> {{ $equipo->colegio ?? 'No registrado' }}
    </div>
    <div class="col-md-4">
        <strong>🏙️ Ciudad:</strong> {{ $equipo->ciudad ?? 'No registrada' }}
    </div>
    <div class="col-md-4">
        <strong>📊 Sistema ID:</strong> {{ $equipo->sist ?? 'N/A' }}
    </div>
</div>

<ul class="nav nav-tabs" id="detalleTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="dirigentes-tab" data-toggle="tab" href="#dirigentes" role="tab">
            <i class="fas fa-user-tie"></i> Dirigentes ({{ $equipo->dirigentes->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="punteros-tab" data-toggle="tab" href="#punteros" role="tab">
            <i class="fas fa-users"></i> Punteros ({{ $equipo->punteros->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="votantes-tab" data-toggle="tab" href="#votantes" role="tab">
            <i class="fas fa-check-circle"></i> Votantes ({{ $equipo->votantes->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="vehiculos-tab" data-toggle="tab" href="#vehiculos" role="tab">
            <i class="fas fa-truck"></i> Vehículos ({{ $equipo->vehiculos->count() }})
        </a>
    </li>
</ul>

<div class="tab-content mt-3" id="detalleTabContent">
    {{-- Dirigentes Tab --}}
    <div class="tab-pane fade show active" id="dirigentes" role="tabpanel">
        @if ($equipo->dirigentes->isEmpty())
            <p class="text-muted">No hay dirigentes registrados</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Teléfono</th>
                            <th>Teléfono 2</th>
                            <th>Barrio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($equipo->dirigentes as $dirigente)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $dirigente->nombre }}</td>
                                <td>{{ number_format($dirigente->cedula, 0, ',', '.') }}</td>
                                <td>{{ $dirigente->telefono }}</td>
                                <td>{{ $dirigente->telefono2 ?? '-' }}</td>
                                <td>{{ $dirigente->barrio ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Punteros Tab --}}
    <div class="tab-pane fade" id="punteros" role="tabpanel">
        @if ($equipo->punteros->isEmpty())
            <p class="text-muted">No hay punteros registrados</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Teléfono</th>
                            <th>Barrio</th>
                            <th>Dirigente</th>
                            <th>Votantes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($equipo->punteros as $puntero)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $puntero->nombre }}</td>
                                <td>{{ number_format($puntero->cedula, 0, ',', '.') }}</td>
                                <td>{{ $puntero->telefono }}</td>
                                <td>{{ $puntero->barrio ?? '-' }}</td>
                                <td>{{ $puntero->dirigente->nombre ?? 'Sin asignar' }}</td>
                                <td><span class="badge badge-primary">{{ $puntero->votantes->count() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Votantes Tab --}}
    <div class="tab-pane fade" id="votantes" role="tabpanel">
        @if ($equipo->votantes->isEmpty())
            <p class="text-muted">No hay votantes registrados</p>
        @else
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
                        @foreach ($equipo->votantes as $votante)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $votante->nombre }}</td>
                                <td>{{ number_format($votante->cedula, 0, ',', '.') }}</td>
                                <td>{{ $votante->mesa }}</td>
                                <td>{{ $votante->orden }}</td>
                                <td>
                                    @if($votante->voto == 1)
                                        <span class="badge badge-success">✔️ Votó</span>
                                    @else
                                        <span class="badge badge-danger">❌ No votó</span>
                                    @endif
                                </td>
                                <td>{{ $votante->puntero->nombre ?? 'Sin puntero' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Vehículos Tab --}}
    <div class="tab-pane fade" id="vehiculos" role="tabpanel">
        @if ($equipo->vehiculos->isEmpty())
            <p class="text-muted">No hay vehículos registrados</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Chapa</th>
                            <th>Chofer</th>
                            <th>Tipo</th>
                            <th>Capacidad</th>
                            <th>Teléfonos</th>
                            <th>Punteros</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($equipo->vehiculos as $vehiculo)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $vehiculo->chapa }}</td>
                                <td>{{ $vehiculo->nombre }}</td>
                                <td>{{ $vehiculo->tipovehiculo }}</td>
                                <td>{{ $vehiculo->capacidad }}</td>
                                <td>
                                    {{ $vehiculo->telefono1 }}
                                    @if($vehiculo->telefono2) - {{ $vehiculo->telefono2 }} @endif
                                    @if($vehiculo->telefono3) - {{ $vehiculo->telefono3 }} @endif
                                </td>
                                <td>
                                    @if($vehiculo->punteros->isEmpty())
                                        <span class="text-muted">Sin punteros</span>
                                    @else
                                        @foreach($vehiculo->punteros as $p)
                                            • {{ $p->nombre }}<br>
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script>
    // Reinicializar los tabs después de cargar el contenido
    $(document).ready(function() {
        $('#detalleTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
    });
</script>