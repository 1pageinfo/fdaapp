{{-- Shared look for the Sangh Details and Sangh Receipts pages. --}}
<style>
    .sangh-show-page {
        --sd-ink: #1f2937;
        --sd-muted: #6b7280;
        --sd-line: #e7eaf0;
        --sd-soft: #f7f9fc;
        --sd-brand: #2e7d32;
        color: var(--sd-ink);
    }

    /* Page title + actions */
    .sangh-show-page .details-title { font-size: 1.9rem; line-height: 1.15; font-weight: 700; letter-spacing: -.01em; }
    .sangh-show-page .details-subtitle { font-size: .95rem; color: var(--sd-muted) !important; }
    .sangh-show-page .page-actions .btn {
        display: inline-flex; align-items: center; gap: .35rem;
        border-radius: 8px; padding: .42rem .9rem; font-weight: 500; font-size: .875rem; white-space: nowrap;
    }
    .sangh-show-page .page-actions .btn i { font-size: .8rem; }

    /* Cards */
    .sangh-show-page .card {
        border: 1px solid var(--sd-line) !important;
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 2px 10px rgba(16, 24, 40, .05) !important;
        overflow: hidden;
    }
    .sangh-show-page .card > .card-header {
        background: #fff !important;
        border-bottom: 1px solid var(--sd-line) !important;
        padding: .85rem 1.25rem;
    }
    .sangh-show-page .card > .card-header h5 { font-size: 1.02rem; font-weight: 600; margin: 0; }
    .sangh-show-page .card > .card-header h5 i { color: var(--sd-brand) !important; margin-right: .3rem; }

    /* Reference number cards */
    .sangh-show-page .ref-card .card-body { padding: 1rem 1.25rem !important; display: flex; align-items: center; gap: .9rem; }
    .sangh-show-page .ref-card .ref-icon {
        flex: 0 0 40px; width: 40px; height: 40px; border-radius: 10px;
        background: #e8f5e9; color: var(--sd-brand);
        display: flex; align-items: center; justify-content: center;
    }
    .sangh-show-page .ref-card small { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: var(--sd-muted) !important; }
    .sangh-show-page .ref-card strong { font-size: 1.12rem; font-weight: 700; letter-spacing: .01em; }

    /* Label / value tables (Master Details, fees) */
    .sangh-show-page .details-table { margin: 0; }
    .sangh-show-page .details-table th,
    .sangh-show-page .details-table td {
        padding: .62rem 1.25rem;
        border-color: var(--sd-line);
        vertical-align: middle;
        font-size: .92rem;
    }
    .sangh-show-page .details-table th {
        width: 34%;
        background: var(--sd-soft);
        color: var(--sd-muted);
        font-weight: 500;
    }
    .sangh-show-page .details-table td { color: var(--sd-ink); font-weight: 500; }
    .sangh-show-page .details-table td.is-empty { color: #b6bdc8; font-weight: 400; }
    .sangh-show-page .details-table tr:hover th,
    .sangh-show-page .details-table tr:hover td { background: #f1f6f1; }
    .sangh-show-page .details-table tr.table-warning th,
    .sangh-show-page .details-table tr.table-warning td { background: #fff8e1; color: var(--sd-ink); font-weight: 700; }

    /* Receipts: tabs + table */
    .sangh-show-page .receipt-tabs .nav-link { color: var(--sd-muted); font-weight: 500; border-radius: 8px 8px 0 0; }
    .sangh-show-page .receipt-tabs .nav-link.active { color: var(--sd-brand); font-weight: 600; }
    .sangh-show-page #receipts .table thead th {
        font-size: .74rem; text-transform: uppercase; letter-spacing: .03em; color: var(--sd-muted);
        background: var(--sd-soft); border-bottom: 1px solid var(--sd-line); white-space: nowrap; font-weight: 600;
    }
    .sangh-show-page #receipts .table td { padding-top: .7rem; padding-bottom: .7rem; vertical-align: middle; }
    .sangh-show-page #receipts .table th:first-child,
    .sangh-show-page #receipts .table td:first-child { padding-left: 1.25rem; }
    .sangh-show-page #receipts .table th:last-child,
    .sangh-show-page #receipts .table td:last-child { padding-right: 1.25rem; }
    .sangh-show-page #receipts .table td .btn { border-radius: 8px; }
    .sangh-show-page .badge { font-weight: 500; padding: .38em .7em; border-radius: 6px; }

    .sangh-show-page .alert { border: 0; border-left: 4px solid currentColor; border-radius: 10px; }

    .receipt-print { border: 2px solid #333 !important; background-color: #fff; }

    /* Receipts page: status tabs */
    .sangh-show-page .status-tabs .nav-link {
        color: var(--sd-muted); font-weight: 500; border: 1px solid var(--sd-line); border-radius: 999px;
        padding: .3rem .8rem; background: #fff; display: inline-flex; align-items: center; gap: .3rem;
    }
    .sangh-show-page .status-tabs .nav-link.active { background: var(--sd-brand); border-color: var(--sd-brand); color: #fff; }
    /* Two-column label/value grid (Sangh Details) */
    .sangh-show-page .kv-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .sangh-show-page .kv-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .sangh-show-page .kv-item {
        display: grid; grid-template-columns: minmax(130px, 40%) 1fr; align-items: center;
        border-bottom: 1px solid var(--sd-line); min-width: 0;
    }
    .sangh-show-page .kv-grid:not(.kv-grid-4) .kv-item:nth-child(odd):not(.kv-wide) { border-right: 1px solid var(--sd-line); }
    .sangh-show-page .kv-wide { grid-column: 1 / -1; grid-template-columns: minmax(130px, 20%) 1fr; }
    .sangh-show-page .kv-spacer { border-bottom: 1px solid var(--sd-line); }
    .sangh-show-page .kv-label {
        padding: .55rem 1rem; background: var(--sd-soft); color: var(--sd-muted);
        font-size: .86rem; font-weight: 500; height: 100%; display: flex; align-items: center; gap: .25rem;
    }
    .sangh-show-page .kv-value { padding: .55rem 1rem; font-weight: 500; font-size: .92rem; overflow-wrap: anywhere; }
    .sangh-show-page .kv-value.is-empty { color: #b6bdc8; font-weight: 400; }
    .sangh-show-page .kv-grid-4 .kv-item { grid-template-columns: 1fr; border-right: 1px solid var(--sd-line); }
    .sangh-show-page .kv-grid-4 .kv-item:last-child { border-right: 0; }
    .sangh-show-page .kv-grid-4 .kv-label { padding: .45rem 1rem; }
    .sangh-show-page .kv-total .kv-label, .sangh-show-page .kv-total .kv-value { background: #fff8e1; color: var(--sd-ink); font-weight: 700; }

    /* Receipts grouped by status (Sangh Details) */
    .sangh-show-page .receipts-table thead th {
        font-size: .74rem; text-transform: uppercase; letter-spacing: .03em; color: var(--sd-muted);
        background: var(--sd-soft); white-space: nowrap; font-weight: 600;
    }
    .sangh-show-page .receipts-table .status-group-row td { background: #fbfcfe; padding-top: .5rem; padding-bottom: .4rem; }
    .sangh-show-page .receipts-table th:first-child, .sangh-show-page .receipts-table td:first-child { padding-left: 1.25rem; }

    @media (max-width: 768px) {
        .sangh-show-page .kv-grid, .sangh-show-page .kv-grid-4 { grid-template-columns: 1fr; }
        .sangh-show-page .kv-grid .kv-item { border-right: 0 !important; }
        .sangh-show-page .kv-wide { grid-template-columns: minmax(130px, 40%) 1fr; }
        .sangh-show-page .kv-spacer { display: none; }
    }

    @media (max-width: 992px) {
        .sangh-show-page .details-title { font-size: 1.55rem; }
        .sangh-show-page .details-table th { width: 42%; }
    }

    /* Print */
    @media print {
        @page { size: A4 portrait; margin: 0.4cm; }
        .no-print, .modal, .btn, .modal-backdrop, .navbar, #sidebar { display: none !important; }
        body { font-size: 10px !important; zoom: 0.8; background: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .container-fluid { padding: 0 !important; width: 100% !important; margin: 0 !important; }
        .page-body-wrapper, .main-panel { margin-left: 0 !important; margin-top: 0 !important; padding-top: 0 !important; width: 100% !important; min-height: 0 !important; }
        .sangh-show-page { margin: 0 !important; padding: 0 !important; }
        .sangh-show-page .table-responsive { overflow: visible !important; }
        .sangh-show-page .card { box-shadow: none !important; border: 1px solid #ccc !important; margin-bottom: 6px !important; break-inside: avoid; }
        .sangh-show-page .card > .card-header { padding: 4px 8px !important; }
        .sangh-show-page h5 { font-size: 11px !important; }
        .sangh-show-page table { width: 100% !important; font-size: 9px !important; }
        .sangh-show-page .details-table th, .sangh-show-page .details-table td { padding: 3px 8px !important; }
        /* One page: keep the two-column grid and tighten everything. */
        .sangh-show-page .kv-grid { grid-template-columns: repeat(2, 1fr) !important; }
        .sangh-show-page .kv-grid-4 { grid-template-columns: repeat(4, 1fr) !important; }
        .sangh-show-page .kv-label, .sangh-show-page .kv-value { padding: 2px 6px !important; font-size: 9px !important; }
        .sangh-show-page .receipts-table td, .sangh-show-page .receipts-table th { padding: 2px 4px !important; font-size: 8.5px !important; }
        .sangh-show-page .ref-card .card-body { padding: 4px 8px !important; }
        .sangh-show-page .ref-card .ref-icon { display: none; }
        .sangh-show-page .row.g-3 { --bs-gutter-y: 4px; }
        .sangh-show-page .row.g-3 > .col-md-4 { flex: 0 0 33.333%; max-width: 33.333%; }
        .sangh-show-page .details-title { font-size: 14px !important; }
        .sangh-show-page .badge { padding: 1px 5px !important; font-size: 8px !important; }
        .sangh-show-page .card#receipts { break-inside: auto; }
    }
</style>
