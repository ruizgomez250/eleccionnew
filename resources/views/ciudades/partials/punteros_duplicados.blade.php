{{-- resources/views/ciudades/partials/punteros_duplicados.blade.php --}}
<div class="d-flex justify-content-between align-items-start flex-wrap mb-3">
    <div>
        <h4 class="mb-1">
            <i class="fas fa-copy text-danger"></i> Punteros Duplicados
        </h4>
        <p class="mb-0 text-muted">
            Distrito: <strong>{{ $distritoNombre }}</strong>
            <span class="mx-2">|</span>
            Cédulas duplicadas:
            <span class="badge badge-danger">{{ $grupos->count() }}</span>
        </p>
    </div>

    <div class="text-right">
        <button type="button" class="btn btn-secondary btn-sm" onclick="volverASistemasDelDistrito()">
            <i class="fas fa-arrow-left"></i> Volver a Sistemas
        </button>
        <button type="button" class="btn btn-danger btn-sm ml-1" id="btnBorrarPunterosDuplicados"
            onclick="borrarPunterosDuplicadosSeleccionados()">
            <i class="fas fa-trash"></i> Borrar Seleccionados
            <span class="badge badge-light" id="contadorSeleccionDup">0</span>
        </button>
    </div>
</div>

@if ($grupos->isEmpty())
    <div class="alert alert-success text-center py-5">
        <i class="fas fa-check-circle fa-3x mb-3"></i>
        <p class="mb-0 h5">No hay punteros duplicados en este distrito</p>
        <small class="text-muted">
            Se verifica cédula por cédula en todo el distrito. Si la misma cédula
            aparece en dos sistemas distintos también se marca como duplicado.
        </small>
    </div>
@else
    <div class="alert alert-warning py-2">
        <i class="fas fa-exclamation-triangle"></i>
        Cada grupo es <strong>una cédula cargada más de una vez</strong> (puede cruzarse entre
        sistemas: mismo colegio cargado dos veces). Marcá <strong>una sola carga</strong>:
        la vieja o la nueva, nunca las dos.
        Al borrar el puntero también se borran sus votantes asociados.
    </div>

    @foreach ($grupos as $indice => $grupo)
        <div class="card mb-2 shadow-sm grupo-puntero-duplicado">
            <div class="card-header py-2" style="background:#f4f6f9;">
                <span class="badge badge-info mr-2" title="Sistemas donde aparece esta cédula">
                    <i class="fas fa-flag"></i> {{ implode(' / ', $grupo['sistemas']) }}
                </span>
                <strong>Cédula: {{ $grupo['cedula'] }}</strong>
                <span class="ml-2">{{ $grupo['nombre'] }}</span>
                <span class="badge badge-secondary float-right">
                    {{ count($grupo['punteros']) }} cargas
                </span>
            </div>

            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach ($grupo['punteros'] as $item)
                            <tr>
                                <td class="text-center align-middle" style="width:52px;">
                                    <input type="checkbox" class="chk-puntero-duplicado" value="{{ $item['id'] }}"
                                        data-grupo="{{ $indice }}" style="width:1.3rem; height:1.3rem; cursor:pointer;"
                                        title="Marcar esta carga para borrar">
                                </td>

                                <td class="align-middle" style="width:170px;">
                                    @if ($item['es_nueva'])
                                        <span class="badge badge-success">
                                            <i class="fas fa-clock"></i> Carga más nueva
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-history"></i> Carga más vieja
                                        </span>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    <span class="text-muted">ID {{ $item['id'] }}</span>
                                    <strong>{{ $item['nombre'] }}</strong>
                                    <small class="text-muted d-block">
                                        <i class="fas fa-phone"></i> {{ $item['telefono'] ?: 'sin teléfono' }}
                                        @if ($item['barrio'])
                                            <span class="mx-1">|</span>
                                            <i class="fas fa-map-marker-alt"></i> {{ $item['barrio'] }}
                                        @endif
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="fas fa-user-tie"></i> {{ $item['dirigente'] ?? 'N/A' }}
                                        <span class="mx-1">|</span>
                                        <i class="fas fa-school"></i> {{ $item['equipo'] ?? 'N/A' }}
                                        <span class="mx-1">|</span>
                                        <i class="fas fa-flag"></i> {{ $item['sistema'] }}
                                    </small>
                                </td>

                                <td class="text-center align-middle" style="width:150px;">
                                    <span class="badge badge-primary">
                                        <i class="fas fa-users"></i>
                                        {{ number_format($item['votantes'], 0, '', '.') }}
                                    </span>
                                    <small class="d-block text-muted">votantes</small>
                                </td>

                                <td class="text-right align-middle" style="width:150px;">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> {{ $item['fecha_carga'] }}
                                    </small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif
