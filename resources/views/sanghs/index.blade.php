@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 mb-5">
    
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center g-3">
            <div class="col-xl-4 col-lg-12">
                <h2 class="page-title mb-1">
                    <i class="fa fa-sitemap text-primary"></i> Sangh Management
                </h2>
                <p class="text-muted mb-0 page-subtitle">Manage and view all Sangh records</p>
            </div>
            <div class="col-xl-8 col-lg-12">
                {{-- Separate buttons (not a btn-group) so they wrap as whole buttons, never mid-label --}}
                <div class="header-actions d-flex flex-wrap align-items-center justify-content-xl-end">
                    <a href="{{ route('sanghs.create') }}" class="btn btn-primary" data-perm="sanghs.create">
                        <i class="fa fa-plus"></i> Create New
                    </a>
                    <a href="{{ route('sanghs.export', request()->query()) }}" class="btn btn-outline-secondary">
                        <i class="fa fa-download"></i> Export
                    </a>
                    <a href="{{ route('sanghs.template') }}" class="btn btn-outline-info" data-perm="sanghs.create">
                        <i class="fa fa-file-excel-o"></i> Template
                    </a>
                    <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#importModal" data-perm="sanghs.create">
                        <i class="fa fa-upload"></i> Import
                    </button>
                    @if($rangeImportInfo)
                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#rangeImportModal">
                            <i class="fa fa-sort-numeric-asc"></i> Import by Sangh No.
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('import_errors'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-triangle"></i> <strong>Import Warnings:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters Section -->
    <div class="card filter-card shadow-sm mb-4 border-0">
        <div class="card-header bg-light border-bottom">
            <h5 class="mb-0">
                <i class="fa fa-filter text-primary"></i> Advanced Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('sanghs.index') }}" id="filterForm">
                <div class="row g-3 mb-3">
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label fw-bold small text-muted mb-1">प्रादेशिक विभाग</label>
                        <select name="pradeshik_vibhag" class="form-control form-control-sm shadow-none">
                            <option value="">All Vibhag</option>
                            @foreach($vibhags as $vibhag)
                                <option value="{{ $vibhag }}" @selected(request('pradeshik_vibhag') == $vibhag)>{{ $vibhag }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label fw-bold small text-muted mb-1">जिल्हा</label>
                        <select name="district" class="form-control form-control-sm shadow-none">
                            <option value="">All Districts</option>
                            @foreach($districts as $district)
                                <option value="{{ $district }}" @selected(request('district') == $district)>{{ $district }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-4">
                        <label class="form-label fw-bold small text-muted mb-1">Year</label>
                        <select name="year" class="form-control form-control-sm shadow-none">
                            <option value="">All Years</option>
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" @selected(request('year') == $yr)>@fy($yr)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-2 col-sm-4">
                        <label class="form-label fw-bold small text-muted mb-1">नोंदणी दिनांक From</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm shadow-none">
                    </div>
                    <div class="col-lg-3 col-md-2 col-sm-4">
                        <label class="form-label fw-bold small text-muted mb-1">नोंदणी दिनांक To</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm shadow-none">
                    </div>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label fw-bold small text-muted mb-1">Register Receipts</label>
                        <select name="register_receipt_year" class="form-control form-control-sm shadow-none">
                            <option value="">All Years</option>
                            @foreach($registerReceiptYears as $yr)
                                <option value="{{ $yr }}" @selected(request('register_receipt_year') == $yr)>@fy($yr)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6">
                        <label class="form-label fw-bold small text-muted mb-1">Renewal Receipts</label>
                        <select name="renewal_receipt_year" class="form-control form-control-sm shadow-none">
                            <option value="">All Years</option>
                            @foreach($renewalReceiptYears as $yr)
                                <option value="{{ $yr }}" @selected(request('renewal_receipt_year') == $yr)>@fy($yr)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-4">
                        <label class="form-label fw-bold small text-muted mb-1">Status</label>
                        <select name="payment_status" class="form-control form-control-sm shadow-none">
                            <option value="">All Status</option>
                            <option value="unpaid" @selected(request('payment_status') == 'unpaid')>Unpaid</option>
                            <option value="information_approved" @selected(request('payment_status') == 'information_approved')>Information approved</option>
                            <option value="sangh_registered" @selected(request('payment_status') == 'sangh_registered')>Sangh Registered</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-4">
                        <label class="form-label fw-bold small text-muted mb-1">Ownership</label>
                        <select name="ownership" class="form-control form-control-sm shadow-none">
                            <option value="">All Forms</option>
                            <option value="created_by_me" @selected(request('ownership') == 'created_by_me')>Created by Me</option>
                            <option value="assigned_to_me" @selected(request('ownership') == 'assigned_to_me')>Assigned to Me</option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-12 d-flex gap-2 mt-3 mt-lg-0 justify-content-lg-end">
                        <button type="submit" class="btn btn-success btn-sm px-4 shadow-sm">
                            <i class="fa fa-search me-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('sanghs.index') }}" class="btn btn-light btn-sm px-3 border shadow-sm">
                            <i class="fa fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                {{-- One-time setup: hidden once the placeholder rows exist. --}}
                @if($showPlaceholderSeeder)
                <form action="{{ route('sanghs.seed_placeholders') }}" method="POST" onsubmit="return confirm('Add placeholder rows up to {{ \App\Http\Controllers\SanghController::PLACEHOLDER_TARGET }}?');" style="display: inline;" class="me-2">
                    @csrf
                    <button class="btn btn-warning btn-sm shadow-sm">
                        <i class="fa fa-plus-square me-1"></i> Pre-add Blank Sangh rows
                    </button>
                </form>
                @endif
                
                {{-- Counts for the current filters --}}
                <div class="filter-stats">
                    @foreach([
                        ['Total Sangh Found', $sanghs->total()],
                        ['प्रादेशिक विभाग', $stats['vibhags']],
                        ['जिल्हा', $stats['districts']],
                        ['Register Receipts', $stats['register_receipts']],
                        ['Renewal Receipts', $stats['renewal_receipts']],
                    ] as [$statLabel, $statValue])
                        <div class="filter-stat">
                            <span class="filter-stat-label">{{ $statLabel }}</span>
                            <span class="num-pill">{{ number_format($statValue) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa fa-table text-primary"></i> Sangh Records
            </h5>
            <small class="text-muted">Total: <span class="num-pill">{{ number_format($sanghs->total()) }}</span> records</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 12%;"><i class="fa fa-barcode"></i> Unique Ref</th>
                        <th style="width: 12%;"><i class="fa fa-cube"></i> Pradeshik Ref</th>
                        <th style="width: 12%;"><i class="fa fa-cube"></i> District Ref</th>
                        <th style="width: 20%;"><i class="fa fa-building"></i> Sangh Name</th>
                        <th style="width: 12%;"><i class="fa fa-map"></i> Vibhag</th>
                        <th style="width: 12%;"><i class="fa fa-map-marker"></i> District</th>
                        <th style="width: 10%;"><i class="fa fa-users"></i> Members</th>
                        <th style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sanghs as $s)
                        <tr>
                            <td>@if($s->unique_ref_no)<span class="num-pill">{{ $s->unique_ref_no }}</span>@endif</td>
                            <td>@if($s->pradeshik_ref_no)<span class="num-pill">{{ $s->pradeshik_ref_no }}</span>@endif</td>
                            <td>@if($s->district_ref_no)<span class="num-pill">{{ $s->district_ref_no }}</span>@endif</td>
                            <td>
                                <strong>{{ $s->name_of_sangh }}</strong>
                            </td>
                            <td>{{ $s->pradeshik_vibhag }}</td>
                            <td>{{ $s->district }}</td>
                            <td>
                                <span class="num-pill">{{ $s->total_members ?? 0 }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('sanghs.show', $s) }}" class="btn btn-outline-info" title="View">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('sanghs.edit', $s) }}" class="btn btn-outline-primary" title="Edit" data-perm="sanghs.edit">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('sanghs.destroy', $s) }}" method="POST" style="display: inline;" data-perm="sanghs.delete" onsubmit="return confirm('Delete this Sangh?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fa fa-inbox fa-2x mb-2" style="display: block;"></i>
                                No records found. Create one or adjust filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Section -->
    <div class="card p-4 shadow-sm">
        <div class="row align-items-center">
            <!-- Left: Pagination Info -->
            <div class="col-md-4">
                <div class="pagination-info">
                    <p class="mb-0">
                        <strong style="font-size: 16px; color: #0d6efd;">
                            Page {{ $sanghs->currentPage() }} of {{ $sanghs->lastPage() }}
                        </strong>
                    </p>
                    <p class="mb-0 text-muted" style="font-size: 13px;">
                        <i class="fa fa-list"></i> Total: <strong>{{ $sanghs->total() }} records</strong>
                    </p>
                    <p class="mb-0 text-muted" style="font-size: 13px;">
                        Showing <strong>{{ ($sanghs->currentPage() - 1) * $sanghs->perPage() + 1 }}</strong> to 
                        <strong>{{ min($sanghs->currentPage() * $sanghs->perPage(), $sanghs->total()) }}</strong>
                    </p>
                </div>
            </div>

            <!-- Center: Go to Page -->
            <div class="col-md-4 text-center">
                <form class="d-flex align-items-center justify-content-center gap-2" method="GET" action="{{ route('sanghs.index') }}">
                    <!-- Preserve every active filter when jumping to a page -->
                    @foreach(request()->except('page') as $filterKey => $filterValue)
                        @if(is_scalar($filterValue) && $filterValue !== '')
                            <input type="hidden" name="{{ $filterKey }}" value="{{ $filterValue }}">
                        @endif
                    @endforeach
                    
                    <label class="mb-0 fw-bold"><i class="fa fa-arrow-right"></i> Go to:</label>
                    <input type="number" name="page" min="1" max="{{ $sanghs->lastPage() }}" class="form-control" style="width: 70px;" placeholder="#" required>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-forward"></i> Go</button>
                </form>
            </div>

            <!-- Right: Page Size -->
            <div class="col-md-4 text-end">
                <div class="pagination-items-per-page">
                    <p class="mb-0 text-muted" style="font-size: 13px;">
                        <i class="fa fa-cog"></i> Items per page: <strong>{{ $sanghs->perPage() }}</strong>
                    </p>
                </div>
            </div>
        </div>

        <!-- Manual Pagination Navigation -->
        <div class="mt-3 d-flex justify-content-center gap-2 flex-wrap">
            {{-- Previous --}}
            @if($sanghs->onFirstPage())
                <span class="btn btn-sm btn-secondary disabled">← Previous</span>
            @else
                <a href="{{ $sanghs->previousPageUrl() }}" class="btn btn-sm btn-outline-primary">← Previous</a>
            @endif

            {{-- Page Numbers --}}
            @php
                $totalPages = $sanghs->lastPage();
                $currentPage = $sanghs->currentPage();
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);
            @endphp

            @if($start > 1)
                <a href="{{ $sanghs->url(1) }}" class="btn btn-sm btn-outline-secondary">1</a>
                @if($start > 2)
                    <span class="btn btn-sm btn-light disabled" style="border: none; cursor: default;">...</span>
                @endif
            @endif

            @for($page = $start; $page <= $end; $page++)
                @if($page == $currentPage)
                    <span class="btn btn-sm btn-primary" style="cursor: default;">{{ $page }}</span>
                @else
                    <a href="{{ $sanghs->url($page) }}" class="btn btn-sm btn-outline-primary">{{ $page }}</a>
                @endif
            @endfor

            @if($end < $totalPages)
                @if($end < $totalPages - 1)
                    <span class="btn btn-sm btn-light disabled" style="border: none; cursor: default;">...</span>
                @endif
                <a href="{{ $sanghs->url($totalPages) }}" class="btn btn-sm btn-outline-secondary">{{ $totalPages }}</a>
            @endif

            {{-- Next --}}
            @if($sanghs->hasMorePages())
                <a href="{{ $sanghs->nextPageUrl() }}" class="btn btn-sm btn-outline-primary">Next →</a>
            @else
                <span class="btn btn-sm btn-secondary disabled">Next →</span>
            @endif
        </div>
    </div>

    <!-- Pagination Styles -->
    <style>
        .page-title {
            font-size: 2.2rem;
            line-height: 1.1;
            font-weight: 600;
        }

        .page-subtitle {
            font-size: 0.95rem;
        }

        .header-actions {
            gap: 0.5rem;
        }

        /* Filter counts: chips that wrap on desktop, a 2-column grid on phones. */
        .filter-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            width: 100%;
        }

        .filter-stat {
            display: inline-flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.4rem 0.75rem;
            background: #fff;
            border: 1px solid #e7eaf0;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        }

        .filter-stat-label {
            color: #6c757d;
            font-weight: 600;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        @media (max-width: 576px) {
            .filter-stats {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .filter-stat:first-child {
                grid-column: 1 / -1;   /* Total spans the full row */
            }

            .filter-stat-label {
                white-space: normal;
                font-size: 0.8rem;
            }
        }

        .header-actions .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            line-height: 1.2;
            white-space: nowrap;      /* labels never break onto several lines */
            border-radius: 8px;
            margin: 0;
        }

        .header-actions .btn i {
            font-size: 0.85rem;
        }

        @media (max-width: 992px) {
            .page-title {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 576px) {
            /* Phones: two buttons per row, full width */
            .header-actions .btn {
                flex: 1 1 calc(50% - 0.5rem);
                justify-content: center;
            }
        }

        /* Hide default pagination from theme */
        nav[aria-label="Page navigation"] {
            display: none !important;
        }

        .pagination {
            display: none !important;
        }

        /* Pagination Info & Items Per Page */
        .pagination-info {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #0d6efd;
        }

        .pagination-items-per-page {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #6c757d;
        }

        /* Button styles */
        .btn-outline-primary {
            color: #0d6efd !important;
            border-color: #0d6efd !important;
            background-color: transparent !important;
            text-decoration: none;
        }

        .btn-outline-primary:hover {
            background-color: #0d6efd !important;
            color: white !important;
            text-decoration: none;
        }

        .btn-outline-secondary {
            color: #6c757d !important;
            border-color: #6c757d !important;
            background-color: transparent !important;
            text-decoration: none;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d !important;
            color: white !important;
            text-decoration: none;
        }
    </style>

    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('sanghs.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select file (.xlsx/.xls/.csv) · max {{ \App\Support\Uploads::mb(\App\Support\Uploads::MAX_EXCEL_KB) }} MB</label>
                            <input type="file" name="excel_file" class="form-control" required accept="{{ \App\Support\Uploads::accept(\App\Support\Uploads::EXCEL_TYPES) }}" data-max-mb="{{ \App\Support\Uploads::mb(\App\Support\Uploads::MAX_EXCEL_KB) }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success btn-sm">Import</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Import by Sangh No. (superadmin): Excel rows go into a chosen range of Sangh numbers --}}
    @if($rangeImportInfo)
    <div class="modal fade" id="rangeImportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-sort-numeric-asc"></i> Import by Sangh No.</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('sanghs.import_range') }}" method="POST" enctype="multipart/form-data" id="rangeImportForm">
                    @csrf
                    <div class="modal-body">
                        <p class="small text-muted mb-3">
                            Excel row 1 goes into <strong>From Sangh No.</strong>, row 2 into the next number, and so on.
                            Each Sangh keeps its number. Use the same file format as <a href="{{ route('sanghs.template') }}">Template</a>.
                        </p>
                        <div class="alert alert-light border small py-2 mb-3">
                            Sangh No. available: <strong>{{ $rangeImportInfo['min'] }} – {{ $rangeImportInfo['max'] }}</strong>
                            · Blank: <strong>{{ number_format($rangeImportInfo['blank']) }}</strong>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small mb-1">From Sangh No.</label>
                                <input type="number" name="from_no" id="rangeFrom" class="form-control" min="{{ $rangeImportInfo['min'] }}" max="{{ $rangeImportInfo['max'] }}" value="{{ old('from_no') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1">To Sangh No.</label>
                                <input type="number" name="to_no" id="rangeTo" class="form-control" min="{{ $rangeImportInfo['min'] }}" max="{{ $rangeImportInfo['max'] }}" value="{{ old('to_no') }}" required>
                            </div>
                        </div>
                        <p class="small mb-3" id="rangeHint">Choose the range to see how many Excel rows you need.</p>
                        <div class="form-group mb-3">
                            <label class="form-label small mb-1">Excel file (.xlsx/.xls/.csv) · max {{ \App\Support\Uploads::mb(\App\Support\Uploads::MAX_EXCEL_KB) }} MB</label>
                            <input type="file" name="excel_file" class="form-control" accept="{{ \App\Support\Uploads::accept(\App\Support\Uploads::EXCEL_TYPES) }}" data-max-mb="{{ \App\Support\Uploads::mb(\App\Support\Uploads::MAX_EXCEL_KB) }}" required>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="overwrite" value="1" id="rangeOverwrite" @checked(old('overwrite'))>
                            <label class="form-check-label small" for="rangeOverwrite">
                                Also update Sanghs that already have data
                                <span class="d-block text-muted">Without this, only blank Sanghs are filled and nothing is imported if the range contains a filled Sangh. Empty Excel cells always keep the current value.</span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-warning btn-sm">Import</button>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const from = document.getElementById('rangeFrom');
            const to = document.getElementById('rangeTo');
            const hint = document.getElementById('rangeHint');
            const update = () => {
                const f = parseInt(from.value, 10), t = parseInt(to.value, 10);
                if (!f || !t) { hint.className = 'small mb-3'; hint.textContent = 'Choose the range to see how many Excel rows you need.'; return; }
                if (t < f) { hint.className = 'small mb-3 text-danger'; hint.textContent = '"To" must be the same as or after "From".'; return; }
                const n = t - f + 1;
                hint.className = 'small mb-3 text-primary';
                hint.textContent = `Sangh No. ${f} to ${t} = ${n} Sangh${n === 1 ? '' : 's'} → your Excel file must have exactly ${n} data row${n === 1 ? '' : 's'} (below the header).`;
            };
            from.addEventListener('input', update);
            to.addEventListener('input', update);
            update();

            @if(session('open_range_import'))
                // Re-open after a failed import so the user can fix and retry.
                const modalEl = document.getElementById('rangeImportModal');
                if (window.bootstrap?.Modal) { new bootstrap.Modal(modalEl).show(); }
                else if (window.jQuery) { jQuery(modalEl).modal('show'); }
            @endif
        });
    </script>
    @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const vibhagDistrictMap = @json($vibhagDistricts);

        const vibhagSelect = document.querySelector('select[name="pradeshik_vibhag"]');
        const districtSelect = document.querySelector('select[name="district"]');
        
        if (!vibhagSelect || !districtSelect) return;

        const allDistrictOptions = Array.from(districtSelect.options).filter(o => o.value !== '');

        function updateDistricts() {
            const selectedVibhag = (vibhagSelect.value || '').trim();
            const allowedDistricts = vibhagDistrictMap[selectedVibhag] || null;

            // Clear current options except the placeholder
            districtSelect.innerHTML = '<option value="">All Districts</option>';

            const toShow = allowedDistricts 
                ? allDistrictOptions.filter(o => allowedDistricts.includes(o.value.trim())) 
                : allDistrictOptions;

            toShow.forEach(o => districtSelect.appendChild(o.cloneNode(true)));

            const currentValue = oldDistrict;
            if (currentValue && toShow.some(o => o.value.trim() === currentValue.trim())) {
                districtSelect.value = currentValue;
            } else {
                districtSelect.value = '';
            }
        }

        const oldDistrict = @json((string) request('district', ''));
        vibhagSelect.addEventListener('change', updateDistricts);
        updateDistricts();
        
    });
</script>
@endsection