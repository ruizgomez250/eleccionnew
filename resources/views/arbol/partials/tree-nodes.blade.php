{{-- resources/views/arbol/partials/tree-nodes.blade.php --}}
@foreach ($nodes as $node)
    <li>
        <div class="tree-node level-{{ $node['tipo'] }}" data-id="{{ $node['id'] ?? '' }}"
            data-tipo="{{ $node['tipo_nivel'] ?? '' }}">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            {{-- Icono de expandir/colapsar solo si tiene hijos --}}
                            @if (!empty($node['hijos']))
                                <span class="toggle-icon collapsed">▶</span>
                            @else
                                <span class="toggle-icon-placeholder" style="display: inline-block; width: 20px;"></span>
                            @endif
                            <strong class="card-title">{{ $node['nombre'] ?? 'Sin nombre' }}</strong>
                            <span
                                class="badge badge-nivel ml-2 
                                @if ($node['tipo'] == 'intendente') badge-danger
                                @elseif($node['tipo'] == 'concejal') badge-success
                                @elseif($node['tipo'] == 'convencional') badge-info
                                @elseif($node['tipo'] == 'convencional_juventud') badge-warning
                                @elseif($node['tipo'] == 'miembro_comite') badge-orange
                                @elseif($node['tipo'] == 'miembro_juventud') badge-purple
                                @else badge-primary @endif">
                                {{ $node['tipo_nivel'] ?? 'Nodo' }}
                            </span>

                            {{-- 🔹 MOSTRAR TOTALES DEL SISTEMA EN LA CABECERA (solo si no es distrito) --}}
                            @if (isset($node['totales']) && $node['tipo'] != 'distrito')
                                <span class="ml-2">
                                    <span class="badge badge-info" title="Dirigentes">
                                        <i class="fas fa-user-tie"></i> {{ $node['totales']['dirigentes'] ?? 0 }}
                                    </span>
                                    <span class="badge badge-primary" title="Punteros">
                                        <i class="fas fa-user-friends"></i> {{ $node['totales']['punteros'] ?? 0 }}
                                    </span>
                                    <span class="badge badge-success" title="Votantes">
                                        <i class="fas fa-users"></i> {{ $node['totales']['votantes'] ?? 0 }}
                                    </span>
                                </span>
                            @endif
                        </div>

                        @if (isset($node['totales']) && $node['tipo'] == 'distrito')
                            <div class="stats-distrito">
                                <span class="badge badge-light">
                                    <i class="fas fa-chalkboard-user"></i> {{ $node['totales']['total_candidaturas'] }}
                                    Candidaturas
                                </span>
                                <span class="badge badge-light">
                                    <i class="fas fa-user-tie"></i> {{ $node['totales']['total_dirigentes'] }}
                                    Dirigentes
                                </span>
                                <span class="badge badge-light">
                                    <i class="fas fa-user-friends"></i> {{ $node['totales']['total_punteros'] }}
                                    Punteros
                                </span>
                                <span class="badge badge-light">
                                    <i class="fas fa-users"></i> {{ $node['totales']['total_votantes'] }} Votantes
                                </span>
                            </div>
                        @endif
                    </div>

                    @if (isset($node['totales']) && $node['tipo'] == 'distrito')
                        <div class="row mt-2 pt-2 border-top">
                            <div class="col-md-7">
                                <small>
                                    <strong><i class="fas fa-chart-line"></i> Candidaturas:</strong>
                                    <span class="ml-2">
                                        <span class="badge badge-danger">Intendentes:
                                            {{ $node['totales']['intendentes'] }}</span>
                                        <span class="badge badge-success">Concejales:
                                            {{ $node['totales']['concejales'] }}</span>
                                        <span class="badge badge-info">Convencionales:
                                            {{ $node['totales']['convencionales'] }}</span>
                                        <span class="badge badge-warning">Conv. Juventud:
                                            {{ $node['totales']['convencionales_juventud'] }}</span>
                                        <span class="badge badge-orange">Miembros Comite:
                                            {{ $node['totales']['miembros_comite'] }}</span>
                                        <span class="badge badge-purple">Miembros Juventud:
                                            {{ $node['totales']['miembros_juventud'] }}</span>
                                    </span>
                                </small>
                            </div>
                            <div class="col-md-5 text-md-right">
                                <small>
                                    <strong><i class="fas fa-chart-simple"></i> Estructura:</strong>
                                    <span class="ml-2">
                                        <span class="badge badge-info"><i class="fas fa-user-tie"></i> Dir:
                                            {{ $node['totales']['total_dirigentes'] }}</span>
                                        <span class="badge badge-primary"><i class="fas fa-user-friends"></i> Punt:
                                            {{ $node['totales']['total_punteros'] }}</span>
                                        <span class="badge badge-success"><i class="fas fa-users"></i> Vot:
                                            {{ $node['totales']['total_votantes'] }}</span>
                                    </span>
                                </small>
                            </div>
                        </div>
                    @endif

                    {{-- BOTONES PARA SISTEMAS (no distritos) con contadores --}}
                    @if (isset($node['id']) && $node['tipo'] != 'distrito')
                        <div class="mt-2 pt-2 border-top">
                            <button
                                onclick="event.stopPropagation(); abrirModalDirigentes({{ $node['id'] }}, '{{ addslashes($node['nombre']) }}', '{{ addslashes($node['tipo_nivel']) }}')"
                                class="btn btn-sm btn-warning">
                                <i class="fas fa-user-tie"></i> Ver Dirigentes
                                @if (isset($node['totales']['dirigentes']) && $node['totales']['dirigentes'] > 0)
                                    <span class="badge badge-light ml-1">{{ $node['totales']['dirigentes'] }}</span>
                                @endif
                            </button>

                            <button
                                onclick="event.stopPropagation(); abrirModalPunterosLista({{ $node['id'] }}, '{{ addslashes($node['nombre']) }}')"
                                class="btn btn-sm btn-info">
                                <i class="fas fa-user-friends"></i> Ver Punteros
                                @if (isset($node['totales']['punteros']) && $node['totales']['punteros'] > 0)
                                    <span class="badge badge-light ml-1">{{ $node['totales']['punteros'] }}</span>
                                @endif
                            </button>

                            {{-- Botón adicional para ver todos los votantes del sistema --}}
                            <span class="ml-2">
                                <span class="badge badge-success" style="font-size: 0.8rem; padding: 6px 10px;">
                                    <i class="fas fa-users"></i> Votantes: {{ $node['totales']['votantes'] ?? 0 }}
                                </span>
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if (!empty($node['hijos']))
            <ul class="child-list">
                @include('arbol.partials.tree-nodes', ['nodes' => $node['hijos'], 'nivel' => $nivel + 1])
            </ul>
        @endif
    </li>
@endforeach
