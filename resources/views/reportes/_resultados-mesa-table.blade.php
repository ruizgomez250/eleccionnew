<div class="card card-success card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-school"></i> {{ $equipo->colegio }}
            <small class="text-muted ml-2">{{ $equipo->ciudad }}</small>
        </h3>
        <div class="card-tools">
            <span class="badge badge-info">{{ $mesas->count() }} mesas</span>
            <span class="badge badge-primary ml-1">{{ ucfirst($cargo) }}</span>
        </div>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-bordered table-striped table-sm table-resultados mb-0">
            <thead class="bg-primary text-white">
                <tr>
                    <th>Lista</th>
                    <th>Partido</th>
                    <th>Candidato</th>
                    <th class="text-center">Total</th>
                    @foreach ($mesas as $mesa)
                        <th class="text-center">Mesa {{ $mesa->numero_mesa }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotal = 0;
                    $mesaTotals = $mesas->mapWithKeys(fn($m) => [$m->id => 0]);
                @endphp
                @forelse ($partidos as $partido)
                    @php
                        $candidatos = $candidatosPorPartido->get($partido->id, collect());
                        $partidoTotal = 0;
                        $partidoMesaTotals = $mesas->mapWithKeys(fn($m) => [$m->id => 0]);
                    @endphp
                    @if ($candidatos->isNotEmpty())
                        <tr class="partido-row">
                            <td colspan="3">
                                <span class="badge" style="background: {{ $partido->color_hex ?? '#6c757d' }}; width: 12px; height: 12px; display: inline-block; border-radius: 50%;"></span>
                                Lista {{ $partido->numero_lista }} - {{ $partido->sigla ?? $partido->nombre }}
                            </td>
                            <td class="text-center">{{ number_format($candidatos->sum(fn($c) => $votosTotales->get($c->id, 0)), 0, ',', '.') }}</td>
                            @foreach ($mesas as $mesa)
                                <td class="text-center">{{ number_format($candidatos->sum(fn($c) => ($votosPorMesa[$mesa->id][$c->id] ?? 0)), 0, ',', '.') }}</td>
                            @endforeach
                        </tr>
                        @foreach ($candidatos as $candidato)
                            @php
                                $total = $votosTotales->get($candidato->id, 0);
                                $partidoTotal += $total;
                                $grandTotal += $total;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $partido->numero_lista }}</td>
                                <td>{{ $partido->sigla ?? $partido->nombre }}</td>
                                <td class="candidato-nombre">
                                    <span class="badge badge-secondary mr-1">{{ $candidato->numero_orden }}</span>
                                    {{ $candidato->nombre_completo }}
                                </td>
                                <td class="text-center font-weight-bold">{{ number_format($total, 0, ',', '.') }}</td>
                                @foreach ($mesas as $mesa)
                                    @php
                                        $votoMesa = $votosPorMesa[$mesa->id][$candidato->id] ?? 0;
                                        $partidoMesaTotals[$mesa->id] += $votoMesa;
                                        $mesaTotals[$mesa->id] += $votoMesa;
                                    @endphp
                                    <td class="text-center voto-cell">{{ $votoMesa ? number_format($votoMesa, 0, ',', '.') : '-' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ 4 + $mesas->count() }}" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle"></i> No hay candidatos registrados para esta candidatura.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right">TOTAL GENERAL</td>
                    <td class="text-center">{{ number_format($grandTotal, 0, ',', '.') }}</td>
                    @foreach ($mesas as $mesa)
                        <td class="text-center">{{ number_format($mesaTotals[$mesa->id], 0, ',', '.') }}</td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>
