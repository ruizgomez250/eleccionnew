<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrón Cooperativa Luque</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @livewireStyles
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            color: #1e293b;
        }
        .header {
            background: linear-gradient(135deg, #0a4da4 0%, #1a6bdd 50%, #0a4da4 100%);
            padding: 20px 16px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
            animation: shimmer 8s ease-in-out infinite;
        }
        @keyframes shimmer {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(10%, 10%); }
        }
        .header-content { position: relative; z-index: 1; }
        .header h1 {
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }
        .header p {
            color: rgba(255,255,255,0.8);
            font-size: 13px;
            font-weight: 500;
        }
        .header .badge {
            display: inline-block;
            background: #facc15;
            color: #0a4da4;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 12px;
            border-radius: 20px;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
            padding: 20px 16px;
        }
        .search-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(10, 77, 164, 0.08);
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(10, 77, 164, 0.06);
        }
        .search-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #0a4da4;
            margin-bottom: 8px;
        }
        .search-wrapper {
            display: flex;
            gap: 8px;
        }
        .search-wrapper input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.2s;
            outline: none;
            background: #f8fafc;
        }
        .search-wrapper input:focus {
            border-color: #0a4da4;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(10, 77, 164, 0.1);
        }
        .search-wrapper input::placeholder {
            color: #94a3b8;
        }
        .search-wrapper button {
            padding: 12px 20px;
            background: linear-gradient(135deg, #0a4da4, #1a6bdd);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .search-wrapper button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(10, 77, 164, 0.3);
        }
        .search-wrapper button:active {
            transform: translateY(0);
        }
        .search-hint {
            font-size: 12px;
            color: #64748b;
            margin-top: 8px;
        }
        .search-hint strong {
            color: #0a4da4;
        }
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        .results-count {
            font-size: 13px;
            font-weight: 600;
            color: #0a4da4;
            background: rgba(10, 77, 164, 0.08);
            padding: 4px 12px;
            border-radius: 20px;
        }
        .table-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(10, 77, 164, 0.08);
            border: 1px solid rgba(10, 77, 164, 0.06);
            overflow: hidden;
        }
        .table-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        thead {
            background: linear-gradient(135deg, #0a4da4, #1a6bdd);
        }
        th {
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            text-align: left;
            white-space: nowrap;
        }
        td {
            padding: 11px 14px;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        tr:last-child td { border-bottom: none; }
        tbody tr:hover {
            background: rgba(10, 77, 164, 0.03);
        }
        tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        tbody tr:nth-child(even):hover {
            background: rgba(10, 77, 164, 0.05);
        }
        .situacion-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .situacion-activo { background: #dcfce7; color: #166534; }
        .situacion-inactivo { background: #fef2f2; color: #991b1b; }
        .situacion-suspendido { background: #fef9c3; color: #854d0e; }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }
        .empty-state svg {
            width: 48px;
            height: 48px;
            margin-bottom: 12px;
            opacity: 0.4;
        }
        .empty-state h3 {
            font-size: 16px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 4px;
        }
        .empty-state p {
            font-size: 13px;
        }
        .loading {
            text-align: center;
            padding: 30px;
        }
        .spinner {
            width: 32px;
            height: 32px;
            border: 3px solid #e2e8f0;
            border-top-color: #0a4da4;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #94a3b8;
        }

        @media (max-width: 600px) {
            .header h1 { font-size: 18px; }
            .search-card { padding: 16px; }
            .search-wrapper { flex-direction: column; }
            .search-wrapper button {
                justify-content: center;
                padding: 12px;
            }
            th, td { padding: 10px 12px; font-size: 13px; }
            table { min-width: 500px; }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="header-content">
        <h1>Padrón Cooperativa Luque</h1>
        <p>Consulta en línea de socios</p>
        <span class="badge">Consulta gratuita</span>
    </div>
</div>

<div class="container">
    <div class="search-card">
        <label class="search-label" for="search-input">Buscar socio</label>
        <div class="search-wrapper">
            <input
                id="search-input"
                type="text"
                placeholder="Cédula, N° de socio o Nombre y Apellido..."
                value="{{ $query ?? '' }}"
                autocomplete="off"
                autofocus
            >
            <button id="search-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                Buscar
            </button>
        </div>
        <p class="search-hint">
            Buscá por <strong>Cédula</strong>, <strong>N° de Socio</strong> o <strong>Nombre y Apellido</strong>
        </p>
    </div>

    <div id="results-container">
        @if(isset($results))
            @include('padron-coop-results', ['results' => $results, 'query' => $query ?? ''])
        @endif
    </div>
</div>

<div class="footer">
    Sistema de Consulta de Padrón &mdash; Cooperativa Luque
</div>

<script>
    const input = document.getElementById('search-input');
    const btn = document.getElementById('search-btn');
    const container = document.getElementById('results-container');

    function doSearch() {
        const q = input.value.trim();
        if (!q) return;

        container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

        fetch('{{ route("padron-coop.search") }}?q=' + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.results && data.results.length > 0) {
                let html = '<div class="results-header"><span class="results-count">' + data.count + ' resultado' + (data.count !== 1 ? 's' : '') + '</span></div>';
                html += '<div class="table-container"><div class="table-scroll"><table><thead><tr>';
                html += '<th>N°</th><th>Socio N°</th><th>Cédula</th><th>Nombre y Apellido</th><th>Mesa</th><th>Orden</th><th>Situación</th>';
                html += '</tr></thead><tbody>';

                data.results.forEach(r => {
                    const situClass = (r.SITUACION || '').toLowerCase().includes('activo') ? 'situacion-activo'
                        : (r.SITUACION || '').toLowerCase().includes('suspendido') ? 'situacion-suspendido'
                        : 'situacion-inactivo';
                    html += '<tr>';
                    html += '<td>' + (r.NRO || '') + '</td>';
                    html += '<td><strong>' + (r['SOCIO NRO'] || '') + '</strong></td>';
                    html += '<td>' + (r['CI NRO'] || '') + '</td>';
                    html += '<td>' + (r['NOMBRE Y APELLIDO'] || '') + '</td>';
                    html += '<td>' + (r.MESA || '') + '</td>';
                    html += '<td>' + (r.ORDEN || '') + '</td>';
                    html += '<td><span class="situacion-badge ' + situClass + '">' + (r.SITUACION || '') + '</span></td>';
                    html += '</tr>';
                });

                html += '</tbody></table></div></div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="table-container"><div class="empty-state">'
                    + '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>'
                    + '<h3>Sin resultados</h3><p>No se encontraron socios con <strong>"' + q + '"</strong></p>'
                    + '</div></div>';
            }
        })
        .catch(() => {
            container.innerHTML = '<div class="table-container"><div class="empty-state"><h3>Error</h3><p>Ocurrió un error al realizar la búsqueda</p></div></div>';
        });
    }

    btn.addEventListener('click', doSearch);
    input.addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });

    if (input.value.trim()) doSearch();
</script>

@livewireScripts
</body>
</html>
