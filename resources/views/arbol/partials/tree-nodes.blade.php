{{-- resources/views/arbol/partials/tree-nodes.blade.php --}}
@if(isset($nodes) && count($nodes) > 0)
    @foreach($nodes as $node)
        <li>
            <div class="tree-node level-{{ $node['tipo'] }}" 
                 data-id="{{ $node['id'] }}" 
                 data-tipo="{{ $node['tipo_nivel'] }}">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                @php
                                    $iconos = [
                                        'intendente' => 'fa-city',
                                        'intendente_virtual' => 'fa-question-circle',
                                        'concejal' => 'fa-user-tie',
                                        'convencional' => 'fa-users',
                                        'convencional_juventud' => 'fa-child',
                                        'miembro_comite' => 'fa-handshake',
                                        'miembro_juventud' => 'fa-users'
                                    ];
                                    $icono = $iconos[$node['tipo']] ?? 'fa-building';
                                @endphp
                                <i class="fas {{ $icono }} mr-2"></i>
                                <strong class="card-title">{{ $node['nombre'] }}</strong>
                            </div>
                            <div>
                                @php
                                    $colorNivel = [
                                        'intendente' => 'danger',
                                        'intendente_virtual' => 'secondary',
                                        'concejal' => 'success',
                                        'convencional' => 'info',
                                        'convencional_juventud' => 'warning',
                                        'miembro_comite' => 'orange',
                                        'miembro_juventud' => 'purple'
                                    ];
                                    $color = $colorNivel[$node['tipo']] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $color }} badge-nivel">
                                    {{ $node['tipo_nivel'] }}
                                </span>
                            </div>
                        </div>
                        
                        @if(isset($node['ciudad']))
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> {{ $node['ciudad'] }}
                                    @if(isset($node['departamento']) && $node['departamento'] != 'Sin departamento')
                                        | {{ $node['departamento'] }}
                                    @endif
                                </small>
                            </div>
                        @endif
                        
                        @php
                            $totalDirigentes = count($node['candidatos'] ?? []);
                            $totalPunteros = collect($node['candidatos'] ?? [])->sum(function($d) {
                                return count($d['punteros'] ?? []);
                            });
                            $totalVotantes = collect($node['candidatos'] ?? [])->sum(function($d) {
                                return collect($d['punteros'] ?? [])->sum(function($p) {
                                    return count($p['votantes'] ?? []);
                                });
                            });
                        @endphp
                        
                        <div class="mt-2">
                            <small>
                                @if($totalDirigentes > 0)
                                    <span class="text-warning mr-3">
                                        <i class="fas fa-user-tie"></i> 
                                        <strong>{{ number_format($totalDirigentes, 0, '', '.') }}</strong> dirigentes
                                    </span>
                                @endif
                                @if($totalPunteros > 0)
                                    <span class="text-success mr-3">
                                        <i class="fas fa-user-friends"></i> 
                                        <strong>{{ number_format($totalPunteros, 0, '', '.') }}</strong> punteros
                                    </span>
                                @endif
                                @if($totalVotantes > 0)
                                    <span class="text-primary">
                                        <i class="fas fa-vote-yea"></i> 
                                        <strong>{{ number_format($totalVotantes, 0, '', '.') }}</strong> votantes
                                    </span>
                                @endif
                                @if($totalDirigentes == 0 && $totalPunteros == 0 && $totalVotantes == 0)
                                    <span class="text-muted">
                                        <i class="fas fa-info-circle"></i> Sin candidatos registrados
                                    </span>
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            @if(isset($node['concejales']) && count($node['concejales']) > 0)
                <ul>
                    @include('arbol.partials.tree-nodes', ['nodes' => $node['concejales']])
                </ul>
            @endif
            
            @if(isset($node['convencionales']) && count($node['convencionales']) > 0)
                <ul>
                    @include('arbol.partials.tree-nodes', ['nodes' => $node['convencionales']])
                </ul>
            @endif
            
            @if(isset($node['convencionales_juventud']) && count($node['convencionales_juventud']) > 0)
                <ul>
                    @include('arbol.partials.tree-nodes', ['nodes' => $node['convencionales_juventud']])
                </ul>
            @endif
            
            @if(isset($node['miembros_comite']) && count($node['miembros_comite']) > 0)
                <ul>
                    @include('arbol.partials.tree-nodes', ['nodes' => $node['miembros_comite']])
                </ul>
            @endif
            
            @if(isset($node['miembros_juventud']) && count($node['miembros_juventud']) > 0)
                <ul>
                    @include('arbol.partials.tree-nodes', ['nodes' => $node['miembros_juventud']])
                </ul>
            @endif
        </li>
    @endforeach
@else
    <li>
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle"></i> No hay nodos para mostrar
        </div>
    </li>
@endif