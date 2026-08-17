{{-- ============================================================
     TEMA OSCURO EXTENDIDO — Para páginas con tablas, modales,
     formularios, árboles y tarjetas (ej: /arbol, /ciudades,
     /vehiculo, /miembros-de-mesa, /certificados, /reportes)
     Incluye el tema base de useradmin + overrides adicionales.
     ============================================================ --}}
@include('useradmin._dark_theme')
<style>
    /* ---------- Tarjetas (generales) ---------- */
    .content-wrapper .card {
        background: linear-gradient(180deg, var(--ua-card2), var(--ua-card));
        border: 1px solid var(--ua-border);
        border-radius: var(--ua-radius);
        color: var(--ua-text);
        box-shadow: var(--ua-shadow);
    }
    .content-wrapper .card .card-header {
        color: var(--ua-text);
        border-bottom: 1px solid var(--ua-border);
    }
    .content-wrapper .card .card-title { color: var(--ua-text); }
    .content-wrapper .card .card-body { color: var(--ua-text); }
    .content-wrapper .card .card-footer {
        background: rgba(13, 20, 36, .45);
        border-top: 1px solid var(--ua-border);
        border-radius: 0 0 var(--ua-radius) var(--ua-radius);
        color: var(--ua-text);
    }
    .card-outline { border-top: 3px solid var(--ua-indigo) !important; }
    .card-outline.card-success { border-top-color: var(--ua-emerald) !important; }
    .card-outline.card-danger { border-top-color: var(--ua-rose) !important; }
    .card-outline.card-warning { border-top-color: var(--ua-amber) !important; }
    .card-outline.card-info { border-top-color: var(--ua-teal) !important; }
    .card-outline.card-secondary { border-top-color: var(--ua-muted) !important; }

    /* ---------- Pestañas (nav-tabs) ---------- */
    .content-wrapper .nav-tabs {
        border-bottom: 1px solid var(--ua-border);
        background: transparent;
    }
    .content-wrapper .nav-tabs .nav-link {
        color: var(--ua-muted);
        border: none;
        margin-bottom: -1px;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        transition: color .15s ease, border-color .15s ease, background .15s ease;
    }
    .content-wrapper .nav-tabs .nav-link:hover { color: var(--ua-text); border-bottom-color: var(--ua-border2); }
    .content-wrapper .nav-tabs .nav-link.active {
        color: var(--ua-text);
        background: transparent;
        border-bottom-color: var(--ua-violet);
    }

    /* ---------- Tablas (generales) ---------- */
    .content-wrapper .table { color: var(--ua-text); }
    .content-wrapper .table thead th {
        background: rgba(99, 102, 241, .13);
        color: var(--ua-text);
        border-color: var(--ua-border) !important;
        font-weight: 600;
        white-space: nowrap;
    }
    .content-wrapper .table thead.bg-primary { background: var(--ua-grad) !important; }
    .content-wrapper .table thead.bg-primary th { color: #fff; border-color: transparent; }
    .content-wrapper .table thead.thead-dark { background: #0d1424 !important; }
    .content-wrapper .table thead.thead-dark th { color: var(--ua-text); border-color: var(--ua-border); }
    .content-wrapper .table tbody td {
        border-color: var(--ua-border) !important;
        color: var(--ua-muted);
        vertical-align: middle;
    }
    .content-wrapper .table tbody tr { transition: background .15s ease; }
    .content-wrapper .table tbody tr:hover { background: rgba(139, 92, 246, .07); }
    .content-wrapper .table-striped tbody tr:nth-of-type(odd) { background: rgba(13, 20, 36, .4); }
    .content-wrapper .table-striped tbody tr:nth-of-type(odd):hover { background: rgba(139, 92, 246, .10); }
    .content-wrapper .table .table-success,
    .content-wrapper .table tr.table-success,
    .content-wrapper .table-striped tbody tr.table-success {
        background: rgba(5, 150, 105, .25) !important;
        color: #a7f3d0;
    }
    .content-wrapper .table .table-success td { color: #a7f3d0; }
    .table-responsive { overflow-x: auto; }

    /* ---------- Controles de formulario (página completa) ---------- */
    .form-control {
        background: var(--ua-input);
        border: 1px solid var(--ua-border2);
        border-radius: 10px;
        color: var(--ua-text);
        box-shadow: inset 0 2px 6px rgba(0, 0, 0, .35);
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .form-control:focus {
        background: var(--ua-input);
        border-color: var(--ua-violet);
        color: var(--ua-text);
        box-shadow: 0 0 0 3px rgba(139, 92, 246, .18);
    }
    .form-control[readonly] {
        background: var(--ua-input) !important;
        color: var(--ua-text) !important;
        opacity: 1;
    }
    .form-control:disabled {
        background: var(--ua-input) !important;
        color: var(--ua-muted) !important;
        opacity: 1;
    }
    .form-control::placeholder { color: rgba(142, 163, 191, .6); }
    .form-control option { background: var(--ua-card); color: var(--ua-text); }
    select.form-control { background: var(--ua-input); }
    label { color: var(--ua-muted); font-weight: 600; }
    .form-label { color: var(--ua-muted); font-weight: 600; }

    /* ---------- Botones Bootstrap ---------- */
    .btn {
        border-radius: 10px;
        border: none;
        font-weight: 600;
        transition: transform .18s ease, filter .18s ease, box-shadow .18s ease;
        box-shadow: 0 6px 16px -10px rgba(0, 0, 0, .7);
    }
    .btn:hover { transform: translateY(-1px); filter: brightness(1.07); }
    .btn-primary { background: var(--ua-grad); }
    .btn-primary:hover { background: var(--ua-grad); color: #fff; }
    .btn-info { background: linear-gradient(135deg, #0ea5e9, #22d3ee); color: #062a33; }
    .btn-success { background: linear-gradient(135deg, #059669, #34d399); }
    .btn-warning { background: linear-gradient(135deg, #d97706, #fbbf24); color: #1c1204 !important; }
    .btn-danger { background: linear-gradient(135deg, #e11d48, #fb7185); }
    .btn-secondary { background: rgba(142, 163, 191, .15); color: var(--ua-text); border: 1px solid var(--ua-border2); }
    .btn-secondary:hover { color: var(--ua-text); filter: brightness(1.2); }
    .btn-light { background: rgba(142, 163, 191, .15); color: var(--ua-text); }
    .btn-outline-danger { border: 1px solid var(--ua-rose); color: var(--ua-rose); }
    .btn-tool, .ua-tool { color: var(--ua-muted); }

    /* ---------- Insignias ---------- */
    .badge { border-radius: 999px; font-weight: 600; }
    .badge-primary { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
    .badge-info { background: linear-gradient(135deg, #0ea5e9, #22d3ee); color: #052e33; }
    .badge-success { background: linear-gradient(135deg, #059669, #34d399); color: #052e16; }
    .badge-danger { background: linear-gradient(135deg, #e11d48, #fb7185); }
    .badge-warning { background: linear-gradient(135deg, #d97706, #fbbf24); color: #1c1204; }
    .badge-secondary { background: rgba(142, 163, 191, .25); color: var(--ua-text); }
    .badge-light { background: rgba(142, 163, 191, .15); color: var(--ua-text); }
    .badge-dark { background: #0d1424; color: var(--ua-text); }

    /* ---------- Modales ---------- */
    .modal-content {
        background: linear-gradient(180deg, var(--ua-card2), var(--ua-card));
        border: 1px solid var(--ua-border2);
        border-radius: 20px;
        color: var(--ua-text);
        box-shadow: 0 25px 60px rgba(0, 0, 0, .65);
    }
    .modal-header {
        border-bottom: 1px solid var(--ua-border);
        border-radius: 20px 20px 0 0;
    }
    .modal-header.bg-primary,
    .modal-header.bg-info,
    .modal-header.bg-success,
    .modal-header.bg-warning,
    .modal-header.bg-secondary,
    .modal-header.bg-danger {
        background: linear-gradient(135deg, rgba(99, 102, 241, .22), rgba(139, 92, 246, .16)) !important;
        color: var(--ua-text) !important;
        border-bottom: 1px solid var(--ua-border);
    }
    .modal-header .close { color: var(--ua-text); text-shadow: none; opacity: .85; }
    .modal-header .close:hover { opacity: 1; }
    .modal-footer { border-top: 1px solid var(--ua-border); background: rgba(13, 20, 36, .45); }

    /* Cabeceras de tarjetas dentro de modales */
    .card-header.bg-primary,
    .card-header.bg-success,
    .card-header.bg-info,
    .card-header.bg-warning,
    .card-header.bg-secondary,
    .card-header.bg-danger {
        background: linear-gradient(135deg, rgba(99, 102, 241, .20), rgba(139, 92, 246, .14)) !important;
        color: var(--ua-text) !important;
        border-bottom: 1px solid var(--ua-border);
    }

    /* ---------- Tablas dentro de modales ---------- */
    .modal .table { background: transparent; color: var(--ua-text); margin-bottom: 0; }
    .modal .table thead th {
        background: rgba(99, 102, 241, .13);
        color: var(--ua-text);
        border-bottom: 1px solid var(--ua-border) !important;
        border-top: none !important;
        font-weight: 600;
        white-space: nowrap;
    }
    .modal .table tbody td { border-color: var(--ua-border) !important; color: var(--ua-muted); vertical-align: middle; }
    .modal .table tbody tr:hover { background: rgba(139, 92, 246, .07); }
    .modal .table-striped tbody tr:nth-of-type(odd) { background: rgba(13, 20, 36, .4); }
    .modal .table-striped tbody tr:nth-of-type(odd):hover { background: rgba(139, 92, 246, .10); }
    .modal .table thead.bg-primary { background: var(--ua-grad) !important; }
    .modal .table thead.bg-primary th { color: #fff; border-color: transparent; }

    /* ---------- Alertas ---------- */
    .alert-danger { background: rgba(225, 29, 72, .15); border: 1px solid rgba(251, 113, 133, .30); color: #fecdd3; border-radius: 12px; }
    .alert-info { background: rgba(14, 165, 233, .14); border: 1px solid rgba(34, 211, 238, .30); color: #a5f3fc; border-radius: 12px; }
    .alert-warning { background: rgba(251, 191, 36, .12); border: 1px solid rgba(251, 191, 36, .30); color: #fde68a; border-radius: 12px; }
    .alert-success { background: rgba(5, 150, 105, .15); border: 1px solid rgba(52, 211, 153, .30); color: #a7f3d0; border-radius: 12px; }

    /* ---------- Texto ---------- */
    .text-muted { color: rgba(142, 163, 191, .85) !important; }
    .text-white { color: var(--ua-text) !important; }

    /* ---------- Input group (buscadores) ---------- */
    .input-group-text {
        background: rgba(13, 20, 36, .8);
        border: 1px solid var(--ua-border2);
        color: var(--ua-muted);
        border-radius: 10px;
    }
    .ua-input-icon {
        background: rgba(99, 102, 241, .18);
        border: 1px solid var(--ua-border2);
        border-right: none;
        color: var(--ua-text);
        border-radius: 10px 0 0 10px;
    }
    #buscadorArbol, #buscadorDistrito { border-radius: 0 10px 10px 0; }

    /* ---------- Separadores ---------- */
    hr { border-color: var(--ua-border); }

    /* ---------- Tarjetas de distritos (/ciudades) ---------- */
    .distrito-card {
        background: linear-gradient(180deg, var(--ua-card2), var(--ua-card));
        border: 1px solid var(--ua-border) !important;
        border-radius: 16px;
        box-shadow: var(--ua-shadow);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .distrito-card:hover {
        transform: scale(1.03) !important;
        box-shadow: 0 14px 30px rgba(0, 0, 0, .55) !important;
        border-color: var(--ua-violet) !important;
    }
    .distrito-card .card-title { color: var(--ua-text); }
    .distrito-card p { color: var(--ua-muted); }
    .distrito-card strong { color: var(--ua-text); }

    /* ---------- Árbol jerárquico (/arbol) ---------- */
    .tree {
        background-color: transparent;
        border: 1px solid var(--ua-border);
        border-radius: var(--ua-radius);
    }
    .tree li:before { border-left-color: var(--ua-border2); }
    .tree li:after { border-top-color: var(--ua-border2); }
    .tree-node .card {
        background: var(--ua-card);
        border: 1px solid var(--ua-border);
        border-left: 4px solid var(--ua-indigo);
        border-radius: 12px;
        box-shadow: var(--ua-shadow);
        margin-bottom: 8px;
    }
    .tree-node .card-header {
        background: transparent;
        border-bottom: 1px solid var(--ua-border);
        border-radius: 12px 12px 0 0;
        color: var(--ua-text);
        padding: 12px 15px;
    }
    .tree-node .card-title { color: var(--ua-text); }
    .tree-node:hover .card { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0, 0, 0, .5); }
    .level-distrito .card { border-left-color: var(--ua-indigo); border-left-width: 5px; }
    .level-distrito .card-header { background: linear-gradient(135deg, rgba(99, 102, 241, .35), rgba(139, 92, 246, .28)); }
    .level-intendente .card { border-left-color: var(--ua-rose); }
    .level-intendente .card-header { background: linear-gradient(135deg, rgba(225, 29, 72, .18), rgba(251, 113, 133, .12)); }
    .level-concejal .card { border-left-color: var(--ua-emerald); }
    .level-concejal .card-header { background: linear-gradient(135deg, rgba(5, 150, 105, .20), rgba(52, 211, 153, .12)); }
    .toggle-icon { color: var(--ua-muted); }
    .toggle-icon.expanded { color: var(--ua-indigo); }
    .tree .border-top { border-top-color: var(--ua-border) !important; }
    .stats-distrito .badge,
    .stats-distrito .badge-light { background: rgba(142, 163, 191, .15); color: var(--ua-text); }
    .nodo-buscar .card {
        background-color: rgba(251, 191, 36, .16) !important;
        border-left-color: var(--ua-amber) !important;
        box-shadow: 0 0 0 3px rgba(251, 191, 36, .25) !important;
    }
</style>
