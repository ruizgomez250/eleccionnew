<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-table"></i> Mesa: <strong>{{ $mesa->codigo_mesa }}</strong>
            @if($mesa->equipo)
                | Escuela: <strong>{{ $mesa->equipo->descripcion }}</strong>
            @endif
            | Cargo: <strong>{{ ucfirst($cargo) }}</strong>
        </h3>
    </div>
    <div class="card-body p-0">
        <form id="formCargaResultados">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="mesa_id" value="{{ $mesa->id }}">
            <input type="hidden" name="cargo" value="{{ $cargo }}">

            <table class="table table-bordered table-hover mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th style="width:50px;">Lista</th>
                        <th>Partido</th>
                        <th>Candidato</th>
                        <th style="width:140px;">Votos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $row)
                        @php
                            $partido = $row['partido'];
                            $candidatos = $row['candidatos'];
                            $totalCandidatos = $candidatos->count();
                            $preferencias = $row['preferencias'];
                        @endphp
                        @forelse($candidatos as $j => $cand)
                            @php
                                $prefVoto = $preferencias->get($cand->id);
                            @endphp
                            <tr>
                                <td class="text-center align-middle">{{ $j === 0 ? $partido->numero_lista : '' }}</td>
                                <td class="align-middle">{{ $j === 0 ? ($partido->sigla ?: $partido->nombre) : '' }}</td>
                                <td>
                                    <span class="badge badge-secondary mr-2">{{ $cand->numero_orden }}</span>
                                    {{ $cand->nombre_completo }}
                                </td>
                                <td>
                                    <input type="number"
                                        name="preferencias[{{ $partido->id }}][{{ $cand->id }}]"
                                        class="form-control"
                                        min="0"
                                        value="{{ $prefVoto ? $prefVoto->cantidad_votos : '' }}"
                                        placeholder="0">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-2">
                                    <small>No hay candidatos registrados para este partido y cargo.</small>
                                </td>
                            </tr>
                        @endforelse
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-info-circle"></i>
                                No hay partidos activos para este cargo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>
    @if(count($rows) > 0)
        <div class="card-footer text-right">
            <button type="button" class="btn btn-success btn-lg" id="btnGuardarResultados">
                <i class="fas fa-save"></i> Guardar Resultados
            </button>
        </div>
    @endif
</div>

<script>
    $(document).ready(function() {
        $('#btnGuardarResultados').off('click').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

            var token = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: '{{ route("certificados.guardar") }}',
                type: 'POST',
                data: $('#formCargaResultados').serialize(),
                headers: {
                    'X-CSRF-TOKEN': token
                },
                success: function(resp) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: resp.message,
                        timer: 1000,
                        showConfirmButton: false
                    }).then(function() {
                        $('#btnCargar').trigger('click');
                    });
                },
                error: function(xhr) {
                    var msg = 'Error al guardar';
                    if (xhr.responseJSON) {
                        msg = xhr.responseJSON.message || (xhr.responseJSON.errors ? Object.values(xhr.responseJSON.errors).flat().join(', ') : msg);
                    } else if (xhr.status === 419) {
                        msg = 'La sesión ha expirado. Recargue la página.';
                    } else if (xhr.status === 0) {
                        msg = 'Error de conexión. Verifique su red.';
                    } else {
                        msg = 'Error ' + xhr.status + ': ' + (xhr.statusText || 'Error desconocido');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error (' + xhr.status + ')',
                        text: msg
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Resultados');
                }
            });
        });
    });
</script>
