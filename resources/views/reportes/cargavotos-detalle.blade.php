<h5>{{ $titulo }} — Puntero #{{ $punteroId }}</h5>
<hr>
@if($votantes->isEmpty())
    <p class="text-muted">No hay votantes en esta categoría.</p>
@else
    <p class="text-muted">{{ $votantes->count() }} persona(s) encontrada(s).</p>
    <div class="table-responsive">
        <table id="modalDetalleTable" class="table table-sm table-bordered table-striped table-hover w-100">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Mesa</th>
                    <th>Escuela</th>
                    <th>Ciudad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($votantes as $i => $v)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $v->cedula }}</td>
                    <td>{{ $v->nombre }}</td>
                    <td>{{ $v->mesa }}</td>
                    <td>{{ $v->escuela }}</td>
                    <td>{{ $v->ciudad }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
