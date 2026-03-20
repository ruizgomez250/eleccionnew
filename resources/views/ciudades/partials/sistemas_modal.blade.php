<h4 class="mb-3">
    Totales Generales:
    <span class="badge badge-warning">
        Dirigentes: {{ number_format($totalesSistemas ? collect($totalesSistemas)->sum('dirigentes') : 0) }}
    </span>
    <span class="badge badge-info">
        Punteros: {{ number_format($totalesSistemas ? collect($totalesSistemas)->sum('punteros') : 0) }}
    </span>
    <span class="badge badge-success">
        Votantes: {{ number_format($totalesSistemas ? collect($totalesSistemas)->sum('votantes') : 0) }}
    </span>
</h4>

<div class="card">
    <div class="card-body">

        <table id="sistemas-table" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sistema</th>
                    <th>Tipo</th>
                    <th class="text-center">Dirigentes</th>
                    <th class="text-center">Punteros</th>
                    <th class="text-center">Votantes</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($sistemas as $s)
                    @php
                        $dir = $totalesSistemas[$s->id]['dirigentes'] ?? 0;
                        $pun = $totalesSistemas[$s->id]['punteros'] ?? 0;
                        $vot = $totalesSistemas[$s->id]['votantes'] ?? 0;
                    @endphp

                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $s->nombre }}</td>
                        <td>{{ $s->tipo ?? 'No especificado' }}</td>

                        <td class="text-center">
                            <span class="badge badge-warning">{{ $dir }}</span>
                        </td>

                        <td class="text-center">
                            <span class="badge badge-info">{{ $pun }}</span>
                        </td>

                        <td class="text-center">
                            <span class="badge badge-success">{{ $vot }}</span>
                        </td>

                        <td>

                            {{-- DIRIGENTES --}}
                            <button class="btn btn-warning btn-sm btn-dirigentes" data-sistema="{{ $s->id }}"
                                data-nombre="{{ $s->nombre }}" data-toggle="modal" data-target="#modalDirigentes">
                                <i class="fas fa-user-tie"></i>
                            </button>
                           

                            {{-- PUNTEROS --}}
                            <button class="btn btn-info btn-sm"
                                onclick="abrirModalPunterosLista({{ $s->id }}, '{{ $s->nombre }}')">
                                <i class="fas fa-users"></i>
                            </button>

                        </td>
                    </tr>
                @endforeach
            </tbody>

            {{-- 🔥 FOOTER dinámico --}}
            <tfoot>
                <tr style="background:#f4f6f9; font-weight:bold;">
                    <td colspan="3" class="text-right">TOTALES:</td>
                    <td class="text-center text-warning"></td>
                    <td class="text-center text-info"></td>
                    <td class="text-center text-success"></td>
                    <td></td>
                </tr>
            </tfoot>

        </table>

    </div>
</div>
<script>
$(document).ready(function() {

    let tabla = $('#sistemas-table').DataTable({
        responsive: true,
        destroy: true,

        dom: "<'row'<'col-md-4'l><'col-md-4'f><'col-md-4 text-right'B>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",

        buttons: [
            {
                extend: 'excelHtml5',
                className: 'btn btn-success btn-sm',
                text: '<i class="fas fa-file-excel"></i> Excel'
            },
            {
                extend: 'pdfHtml5',
                className: 'btn btn-danger btn-sm',
                text: '<i class="fas fa-file-pdf"></i> PDF'
            },
            {
                extend: 'print',
                className: 'btn btn-secondary btn-sm',
                text: '<i class="fas fa-print"></i> Imprimir'
            }
        ],

        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },

        pageLength: 10
    });

    // 🔥 Totales dinámicos al filtrar
    function actualizarTotales() {

        let totalDir = 0;
        let totalPun = 0;
        let totalVot = 0;

        tabla.rows({ search: 'applied' }).every(function() {
            let data = this.data();

            totalDir += parseInt($(data[3]).text()) || 0;
            totalPun += parseInt($(data[4]).text()) || 0;
            totalVot += parseInt($(data[5]).text()) || 0;
        });

        $('#sistemas-table tfoot td').eq(1).html(totalDir);
        $('#sistemas-table tfoot td').eq(2).html(totalPun);
        $('#sistemas-table tfoot td').eq(3).html(totalVot);
    }

    tabla.on('draw', actualizarTotales);

    actualizarTotales();
});
</script>