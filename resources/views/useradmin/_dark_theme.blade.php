{{-- ============================================================
     TEMA OSCURO MODERNO — Página de Administración de Usuarios
     CSS con scope solo para esta página (cargado únicamente aquí)
     ============================================================ --}}
<style>
    :root {
        --ua-bg: #0b1220;
        --ua-card: #141d2f;
        --ua-card2: #1b2740;
        --ua-input: #0d1424;
        --ua-border: #26334d;
        --ua-border2: #32415f;
        --ua-text: #e6edf7;
        --ua-muted: #8ea3bf;
        --ua-indigo: #6366f1;
        --ua-violet: #8b5cf6;
        --ua-teal: #14b8a6;
        --ua-emerald: #34d399;
        --ua-amber: #fbbf24;
        --ua-rose: #fb7185;
        --ua-grad: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        --ua-radius: 16px;
        --ua-shadow: 0 12px 32px -14px rgba(0, 0, 0, .65);
    }

    /* ---------- Fondo del área de contenido ---------- */
    .content-wrapper {
        background:
            radial-gradient(1100px 520px at 8% -8%, rgba(99, 102, 241, .16), transparent 60%),
            radial-gradient(900px 480px at 108% 0%, rgba(139, 92, 246, .12), transparent 55%),
            var(--ua-bg);
    }

    .content-header {
        padding: 1.5rem 1.5rem 0;
    }

    /* ---------- Cabecera de la página ---------- */
    .ua-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        background: linear-gradient(135deg, rgba(99, 102, 241, .14), rgba(139, 92, 246, .10));
        border: 1px solid var(--ua-border);
        border-radius: var(--ua-radius);
        padding: 1.25rem 1.5rem;
        box-shadow: var(--ua-shadow);
        margin-bottom: 1.25rem;
    }

    .ua-header .ua-title {
        margin: 0;
        color: var(--ua-text);
        font-weight: 700;
        font-size: 1.35rem;
        letter-spacing: .2px;
    }

    .ua-header .ua-title i {
        background: var(--ua-grad);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-right: .35rem;
    }

    .ua-header .ua-subtitle {
        margin: .2rem 0 0;
        color: var(--ua-muted);
        font-size: .85rem;
    }

    /* ---------- Botones ---------- */
    .ua-btn {
        border: none;
        border-radius: 10px;
        font-weight: 600;
        padding: .5rem 1.1rem;
        color: #fff;
        box-shadow: 0 6px 16px -8px rgba(0, 0, 0, .6);
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        display: inline-flex;
        align-items: center;
        gap: .45rem;
    }

    .ua-btn:hover {
        transform: translateY(-2px);
        color: #fff;
        box-shadow: 0 10px 22px -10px rgba(99, 102, 241, .55);
        filter: brightness(1.08);
    }

    .ua-btn-grad { background: var(--ua-grad); }
    .ua-btn-teal { background: linear-gradient(135deg, #0d9488, #2dd4bf); }
    .ua-btn-emerald { background: linear-gradient(135deg, #059669, #34d399); color: #052e16; }
    .ua-btn-ghost {
        background: rgba(139, 92, 246, .10);
        border: 1px solid rgba(139, 92, 246, .35);
        color: var(--ua-text);
    }

    .ua-btn-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        box-shadow: 0 6px 14px -8px rgba(0, 0, 0, .6);
        transition: transform .18s ease, filter .18s ease;
    }

    .ua-btn-icon:hover { transform: translateY(-2px) scale(1.05); color: #fff; filter: brightness(1.1); }
    .ua-btn-edit { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
    .ua-btn-del { background: linear-gradient(135deg, #e11d48, #fb7185); }

    /* ---------- Tarjetas ---------- */
    .card.ua-card {
        border: 1px solid var(--ua-border);
        border-radius: var(--ua-radius);
        background: linear-gradient(180deg, var(--ua-card2), var(--ua-card));
        box-shadow: var(--ua-shadow);
        overflow: hidden;
        margin-bottom: 1.25rem;
    }

    .card.ua-card > .card-header {
        background: rgba(139, 92, 246, .09);
        border-bottom: 1px solid var(--ua-border);
        border-radius: var(--ua-radius) var(--ua-radius) 0 0;
        padding: 1rem 1.4rem;
    }

    .card.ua-card > .card-header .card-title {
        color: var(--ua-text);
        font-weight: 700;
        letter-spacing: .3px;
    }

    .card.ua-card > .card-header .card-title i {
        background: var(--ua-grad);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-right: .4rem;
    }

    .card.ua-card > .card-body {
        color: var(--ua-text);
        padding: 1.25rem 1.4rem 1.4rem;
    }

    /* ---------- Tablas ---------- */
    .ua-table { width: 100%; margin: 0; color: var(--ua-text); }
    .ua-table thead th {
        background: rgba(99, 102, 241, .13);
        color: var(--ua-text);
        border: none !important;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
        padding: .9rem 1rem;
        white-space: nowrap;
    }
    .ua-table thead th:first-child { border-radius: 10px 0 0 10px; }
    .ua-table thead th:last-child { border-radius: 0 10px 10px 0; }
    .ua-table tbody td {
        color: var(--ua-muted);
        border-color: var(--ua-border) !important;
        padding: .8rem 1rem;
        vertical-align: middle;
    }
    .ua-table tbody tr { transition: background .15s ease; }
    .ua-table tbody tr:hover { background: rgba(139, 92, 246, .07); }
    .ua-table tbody tr:hover td { color: var(--ua-text); }

    /* ---------- Insignias ---------- */
    .ua-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .32rem .75rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .ua-badge-teal { background: rgba(20, 184, 166, .14); color: #5eead4; border: 1px solid rgba(20, 184, 166, .35); }
    .ua-badge-violet { background: rgba(139, 92, 246, .14); color: #c4b5fd; border: 1px solid rgba(139, 92, 246, .35); }
    .ua-badge-amber { background: rgba(251, 191, 36, .12); color: #fcd34d; border: 1px solid rgba(251, 191, 36, .30); }
    .ua-badge-muted { background: rgba(142, 163, 191, .12); color: var(--ua-muted); border: 1px solid rgba(142, 163, 191, .25); }

    /* ---------- Formularios ---------- */
    .ua-form label {
        color: var(--ua-muted);
        font-weight: 600;
        font-size: .8rem;
        letter-spacing: .3px;
        margin-bottom: .35rem;
    }
    .ua-form .form-control,
    .ua-form input.form-control,
    .ua-form select.form-control {
        background: var(--ua-input);
        border: 1px solid var(--ua-border2);
        border-radius: 10px;
        color: var(--ua-text);
        box-shadow: inset 0 2px 6px rgba(0, 0, 0, .35);
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .ua-form .form-control:focus {
        border-color: var(--ua-violet);
        box-shadow: 0 0 0 3px rgba(139, 92, 246, .18);
        color: var(--ua-text);
        background: var(--ua-input);
    }
    .ua-form .form-control::placeholder { color: rgba(142, 163, 191, .6); }
    .ua-form .form-control option { background: var(--ua-card); color: var(--ua-text); }
    .ua-form .text-muted { color: rgba(142, 163, 191, .7) !important; }
    .ua-form .text-danger { color: var(--ua-rose) !important; }

    .ua-inner-card {
        background: rgba(13, 20, 36, .65);
        border: 1px solid var(--ua-border);
        border-radius: 14px;
        overflow: hidden;
        height: 100%;
    }
    .ua-inner-card > .card-header {
        background: linear-gradient(135deg, rgba(99, 102, 241, .16), rgba(139, 92, 246, .12));
        border-bottom: 1px solid var(--ua-border);
        color: var(--ua-text);
    }
    .ua-inner-card > .card-header h6 { margin: 0; font-weight: 700; }
    .ua-inner-card > .card-body { color: var(--ua-text); }

    /* ---------- Modales ---------- */
    .ua-modal .modal-content {
        background: linear-gradient(180deg, var(--ua-card2), var(--ua-card));
        border: 1px solid var(--ua-border2);
        border-radius: 20px;
        color: var(--ua-text);
        box-shadow: 0 25px 60px rgba(0, 0, 0, .65);
    }
    .ua-modal .modal-header {
        background: linear-gradient(135deg, rgba(99, 102, 241, .14), rgba(139, 92, 246, .10));
        border-bottom: 1px solid var(--ua-border);
        border-radius: 20px 20px 0 0;
        padding: 1.1rem 1.5rem;
    }
    .ua-modal .modal-header .modal-title { font-weight: 700; color: var(--ua-text); }
    .ua-modal .modal-header .close { color: var(--ua-text); text-shadow: none; opacity: .8; }
    .ua-modal .modal-header .close:hover { opacity: 1; }
    .ua-modal .modal-body { padding: 1.5rem; }
    .ua-modal .modal-footer {
        border-top: 1px solid var(--ua-border);
        border-radius: 0 0 20px 20px;
        background: rgba(13, 20, 36, .45);
        padding: 1rem 1.5rem;
    }
    .ua-modal .alert-info { background: rgba(99, 102, 241, .12); border: 1px solid rgba(99, 102, 241, .3); color: #c7d2fe; border-radius: 10px; }
    .ua-modal .select2-selection { background: var(--ua-input) !important; border-color: var(--ua-border2) !important; }
    .ua-modal .select2-selection__rendered { color: var(--ua-text) !important; }

    /* ---------- DataTables ---------- */
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
        background: var(--ua-input);
        border: 1px solid var(--ua-border2);
        border-radius: 8px;
        color: var(--ua-text);
        padding: .3rem .6rem;
    }
    .dataTables_wrapper .dataTables_filter input:focus,
    .dataTables_wrapper .dataTables_length select:focus {
        border-color: var(--ua-violet);
        box-shadow: 0 0 0 3px rgba(139, 92, 246, .18);
        color: var(--ua-text);
    }
    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_info {
        color: var(--ua-muted);
    }
    .dataTables_wrapper .dataTables_paginate .page-link {
        background: rgba(13, 20, 36, .8);
        border-color: var(--ua-border2);
        color: var(--ua-muted);
        border-radius: 8px !important;
        margin: 0 2px;
    }
    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background: var(--ua-grad);
        border-color: transparent;
        color: #fff;
    }
    .dataTables_wrapper .dataTables_paginate .page-item.disabled .page-link { color: rgba(142, 163, 191, .4); }
    table.dataTable { border-collapse: separate; border-spacing: 0 .25rem; }
    table.dataTable thead th, table.dataTable thead td { border-bottom: none !important; }
    table.dataTable tbody tr, table.dataTable tbody td { background: transparent; }

    /* ---------- Select2 ---------- */
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        background: var(--ua-input) !important;
        border: 1px solid var(--ua-border2) !important;
        border-radius: 10px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--ua-text) !important; line-height: 38px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow b { border-color: var(--ua-muted) transparent transparent transparent; }
    .select2-dropdown {
        background: var(--ua-card) !important;
        border: 1px solid var(--ua-border2) !important;
        border-radius: 10px !important;
        box-shadow: var(--ua-shadow);
    }
    .select2-container--default .select2-results__option { color: var(--ua-text); }
    .select2-container--default .select2-results__option--highlighted[aria-selected],
    .select2-container--default .select2-results__option[aria-selected="true"] { background: var(--ua-grad); color: #fff; }
    .select2-container--default .select2-search--dropdown .select2-search__field { background: var(--ua-input); border: 1px solid var(--ua-border2); color: var(--ua-text); border-radius: 8px; }
    .select2-container--default .select2-selection--multiple .select2-selection__choice { background: rgba(99, 102, 241, .2); border-color: rgba(99, 102, 241, .4); color: var(--ua-text); }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color: var(--ua-rose); }

    /* ---------- SweetAlert ---------- */
    .swal2-popup {
        background: var(--ua-card) !important;
        border: 1px solid var(--ua-border2);
        border-radius: 20px !important;
        box-shadow: 0 25px 60px rgba(0, 0, 0, .7);
    }
    .swal2-title, .swal2-html-container { color: var(--ua-text) !important; }
    .swal2-confirm, .swal2-cancel { border-radius: 10px !important; }
</style>
