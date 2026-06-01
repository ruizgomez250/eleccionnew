<h5>{{ $titulo }} - Puntero #{{ $punteroId }}</h5>
<hr>
@if($votantes->isEmpty())
    <p class="text-muted">No hay votantes en esta categoría.</p>
@else
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-striped">
            <thead>
                <tr>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Mesa</th>
                    <th>Escuela</th>
                    <th>Ciudad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($votantes as $v)
                <tr>
                    <td>{{ number_format($v->cedula, 0, ',', '.') }}</td>
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
