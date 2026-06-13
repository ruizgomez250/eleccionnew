@if($results && $results->count() > 0)
    <div class="results-header">
        <span class="results-count">{{ $results->count() }} resultado{{ $results->count() !== 1 ? 's' : '' }}</span>
    </div>
    <div class="table-container">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Socio N°</th>
                        <th>Cédula</th>
                        <th>Nombre y Apellido</th>
                        <th>Mesa</th>
                        <th>Orden</th>
                        <th>Situación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $r)
                        @php
                            $situClass = str_contains($r->SITUACION ?? '', 'ACTIVO') ? 'situacion-activo'
                                : (str_contains($r->SITUACION ?? '', 'SUSPENDIDO') ? 'situacion-suspendido'
                                : 'situacion-inactivo');
                        @endphp
                        <tr>
                            <td>{{ $r->NRO }}</td>
                            <td><strong>{{ $r->{'SOCIO NRO'} }}</strong></td>
                            <td>{{ $r->{'CI NRO'} }}</td>
                            <td>{{ $r->{'NOMBRE Y APELLIDO'} }}</td>
                            <td>{{ $r->Orden ?? '' }}</td>
                            <td>{{ $r->Mesa ?? '' }}</td>
                            <td><span class="situacion-badge {{ $situClass }}">{{ $r->SITUACION }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@elseif(isset($query) && strlen($query) > 0)
    <div class="table-container">
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
            </svg>
            <h3>Sin resultados</h3>
            <p>No se encontraron socios con <strong>"{{ $query }}"</strong></p>
        </div>
    </div>
@endif
