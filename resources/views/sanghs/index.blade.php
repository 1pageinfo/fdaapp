@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 mb-5">
    
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="page-title mb-1">
                    <i class="fa fa-sitemap text-primary"></i> Sangh Management
                </h2>
                <p class="text-muted mb-0 page-subtitle">Manage and view all Sangh records</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group header-actions" role="group">
                    <a href="{{ route('sanghs.create') }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus"></i> Create New
                    </a>
                    <a href="{{ route('sanghs.export') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-download"></i> Export
                    </a>
                    <a href="{{ route('sanghs.template') }}" class="btn btn-sm btn-outline-info">
                        <i class="fa fa-file-excel"></i> Template
                    </a>
                    <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fa fa-upload"></i> Import
                    </button>
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
                <div class="row mb-3">
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">प्रादेशिक विभाग</label>
                        <select name="pradeshik_vibhag" class="form-select form-select-sm">
                            <option value="">All Vibhag</option>
                            @foreach($vibhags as $vibhag)
                                <option value="{{ $vibhag }}" @selected(request('pradeshik_vibhag') == $vibhag)>{{ $vibhag }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">जिल्हा</label>
                        <select name="district" class="form-select form-select-sm">
                            <option value="">All Districts</option>
                            @foreach($districts as $district)
                                <option value="{{ $district }}" @selected(request('district') == $district)>{{ $district }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Year</label>
                        <select name="year" class="form-select form-select-sm">
                            <option value="">All Years</option>
                            @foreach($years as $yr)
                                <option value="{{ $yr }}" @selected(request('year') == $yr)>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">From Date</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">To Date</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Register Receipts</label>
                        <select name="register_receipt_year" class="form-select form-select-sm">
                            <option value="">All Years</option>
                            @foreach($registerReceiptYears as $yr)
                                <option value="{{ $yr }}" @selected(request('register_receipt_year') == $yr)>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Renewal Receipts</label>
                        <select name="renewal_receipt_year" class="form-select form-select-sm">
                            <option value="">All Years</option>
                            @foreach($renewalReceiptYears as $yr)
                                <option value="{{ $yr }}" @selected(request('renewal_receipt_year') == $yr)>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Payment Status</label>
                        <select name="payment_status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="paid" @selected(request('payment_status') == 'paid')>Paid</option>
                            <option value="unpaid" @selected(request('payment_status') == 'unpaid')>Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-search"></i> Apply Filters
                        </button>
                        <a href="{{ route('sanghs.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Seed Placeholders Button -->
    <div class="mb-3">
        <form action="{{ route('sanghs.seed_placeholders') }}" method="POST" onsubmit="return confirm('Add placeholder rows up to 6676?');" style="display: inline;">
            @csrf
            <button class="btn btn-warning btn-sm">
                <i class="fa fa-plus-square"></i> Pre-add Blank Sangh rows till 6676
            </button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa fa-table text-primary"></i> Sangh Records
            </h5>
            <small class="text-muted">Total: {{ $sanghs->total() }} records</small>
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
                            <td><span class="badge bg-light text-dark">{{ $s->unique_ref_no }}</span></td>
                            <td>{{ $s->pradeshik_ref_no }}</td>
                            <td>{{ $s->district_ref_no }}</td>
                            <td>
                                <strong>{{ $s->name_of_sangh }}</strong>
                            </td>
                            <td>{{ $s->pradeshik_vibhag }}</td>
                            <td>{{ $s->district }}</td>
                            <td>
                                <span class="badge bg-info">{{ $s->total_members ?? 0 }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('sanghs.show', $s) }}" class="btn btn-outline-info" title="View">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('sanghs.edit', $s) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('sanghs.destroy', $s) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this Sangh?');">
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
                    <!-- Preserve all filter parameters -->
                    <input type="hidden" name="pradeshik_vibhag" value="{{ request('pradeshik_vibhag') }}">
                    <input type="hidden" name="district" value="{{ request('district') }}">
                    <input type="hidden" name="month" value="{{ request('month') }}">
                    <input type="hidden" name="year" value="{{ request('year') }}">
                    <input type="hidden" name="from_date" value="{{ request('from_date') }}">
                    <input type="hidden" name="to_date" value="{{ request('to_date') }}">
                    <input type="hidden" name="renewal_year" value="{{ request('renewal_year') }}">
                    <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
                    
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

        .header-actions .btn {
            padding: 0.45rem 0.85rem;
            font-size: 0.95rem;
        }

        .header-actions .btn i {
            font-size: 0.85rem;
        }

        @media (max-width: 992px) {
            .page-title {
                font-size: 1.8rem;
            }

            .header-actions {
                margin-top: 0.75rem;
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
                            <label>Select file (.xlsx/.xls/.csv)</label>
                            <input type="file" name="excel_file" class="form-control" required>
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
</div>
@endsection