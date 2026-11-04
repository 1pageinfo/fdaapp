@extends('layouts.app')

@section('content')
@php
    $sangh = $sangh ?? new \App\Models\Sangh();
    $display = fn ($v) => ($v === null || $v === '') ? '-' : $v;
    $sangNumberParts = array_values(array_filter([
        $sangh->unique_ref_no,
        $sangh->pradeshik_ref_no,
        $sangh->district_ref_no,
    ], fn ($v) => $v !== null && $v !== ''));
    $sangNumber = count($sangNumberParts) ? implode('/', $sangNumberParts) : null;
    $registeredMale = is_numeric($sangh->male) ? (int) $sangh->male : null;
    $registeredFemale = is_numeric($sangh->female) ? (int) $sangh->female : null;
    $registeredTotal = ($registeredMale === null && $registeredFemale === null)
        ? null
        : (($registeredMale ?? 0) + ($registeredFemale ?? 0));

    // Sangh fee slabs (admin-configured in Settings → Manage Fee Slabs) — used to
    // live-calculate वार्षिक शुल्क / विकास निधी शुल्क for renewal rows below.
    $feeSlabsForRenewal = \App\Models\SanghFeeSlab::orderBy('min_members')->get(['min_members', 'max_members', 'annual_fee']);
    $developmentFeeRateForRenewal = (float) \App\Models\Setting::getValue('sangh_development_fee_rate', 0);

    $createdAt = $sangh->created_date ?? $sangh->created_at;
    $createdAtStr = $createdAt ? \Carbon\Carbon::parse($createdAt)->format('d-m-Y') : '-';
    $quarterStr = '-';

    if ($createdAt) {
        $month = (int) \Carbon\Carbon::parse($createdAt)->format('m');
        if ($month >= 4 && $month <= 6) {
            $quarterStr = 'Q1 (Apr - Jun)';
        } elseif ($month >= 7 && $month <= 9) {
            $quarterStr = 'Q2 (Jul - Sep)';
        } elseif ($month >= 10 && $month <= 12) {
            $quarterStr = 'Q3 (Oct - Dec)';
        } else {
            $quarterStr = 'Q4 (Jan - Mar)';
        }
    }

    $masterRows = [
        ['वर्ष', \App\Providers\AppServiceProvider::financialYear($sangh->registration_year)],
        ['संघाचे नाव', $sangh->name_of_sangh],
        ['श्रेणी', $sangh->category_code],
        ['संघ प्रकार', $sangh->sangh_type_code],
        ['प्रादेशिक विभाग', $sangh->pradeshik_vibhag],
        ['जिल्हा', $sangh->district],
        ['तालुका', $sangh->taluka],
        ['गाव', $sangh->village],
        ['शहर', $sangh->city],
        ['मुक्काम पोस्ट', $sangh->mukkam_post],
        ['पिनकोड', $sangh->pincode],
        ['पत्ता', $sangh->address],
        ['रस्ता / पथ', $sangh->road_path],
        ['विभाग/प्रभाग', $sangh->ward_section],
        ['पुरुष सभासद संख्या', $sangh->male],
        ['महिला सभासद संख्या', $sangh->female],
        ['एकूण सभासद संख्या', $sangh->total_members],
        ['अध्यक्ष', $sangh->president],
        ['अध्यक्ष मोबाईल', $sangh->president_phone],
        ['अध्यक्ष व्हॉट्सअप', $sangh->president_whatsapp],
        ['अध्यक्ष इमेल', $sangh->president_email],
        ['सचिव', $sangh->secretary],
        ['सचिव मोबाईल', $sangh->secretary_phone],
        ['सचिव व्हॉट्सअप', $sangh->secretary_whatsapp],
        ['सचिव इमेल', $sangh->secretary_email],
        ['फॉर्म तयार केल्याची तारीख (Created Date)', $createdAtStr],
        ['फॉर्म तयार केल्याचे त्रैमासिक (Quarter)', $quarterStr],
    ];

    // प्रादेशिक विभागाची फी: always calculated from Settings → Sangh Fees (× 2 / × 2 / × 3).
    $pradeshikFees = \App\Support\PradeshikFees::calculate($sangh->male, $sangh->female);
    $fee = fn ($key) => $pradeshikFees ? '₹ ' . number_format($pradeshikFees[$key], 0) : '-';
@endphp

<div class="container-fluid mt-4 mb-5 sangh-show-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h2 class="details-title mb-1">Sangh Details</h2>
            <p class="text-muted mb-0 details-subtitle">{{ $display($sangh->name_of_sangh) }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 no-print page-actions">
            @if($prevSangh)
                <a href="{{ route('sanghs.show', $prevSangh->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-chevron-left"></i> Previous
                </a>
            @else
                <button type="button" class="btn btn-sm btn-outline-primary" disabled>
                    <i class="fa fa-chevron-left"></i> Previous
                </button>
            @endif
            @if($nextSangh)
                <a href="{{ route('sanghs.show', $nextSangh->id) }}" class="btn btn-sm btn-outline-primary">
                    Next <i class="fa fa-chevron-right"></i>
                </a>
            @else
                <button type="button" class="btn btn-sm btn-outline-primary" disabled>
                    Next <i class="fa fa-chevron-right"></i>
                </button>
            @endif
            <a href="{{ route('sanghs.receipts', $sangh) }}" class="btn btn-sm btn-warning">
                <i class="fa fa-file-text-o"></i> Receipts <span class="badge bg-white text-dark ms-1">{{ $receiptCount }}</span>
            </a>
            <a href="{{ route('sanghs.edit', $sangh) }}" class="btn btn-sm btn-primary" data-perm="sanghs.edit">
                <i class="fa fa-edit"></i> Edit
            </a>
            <a href="{{ route('sanghs.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button type="button" class="btn btn-sm btn-success" onclick="printSanghDetails()">
                <i class="fa fa-print"></i> Print
            </button>
        </div>
    </div>

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
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle"></i> {{ implode(' ', $errors->all()) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif


    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card ref-card h-100">
                <div class="card-body">
                    <div class="ref-icon"><i class="fa fa-hashtag"></i></div>
                    <div>
                        <small class="d-block">Sangh Number</small>
                        <strong>{{ $display($sangh->unique_ref_no) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card ref-card h-100">
                <div class="card-body">
                    <div class="ref-icon"><i class="fa fa-map-marker"></i></div>
                    <div>
                        <small class="d-block">District Ref</small>
                        <strong>{{ $display($sangh->district_ref_no) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-id-card"></i> Master Details</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 details-table">
                <tbody>
                    @foreach($masterRows as $row)
                        <tr>
                            <th>{{ $row[0] }}</th>
                            <td class="{{ ($row[1] === null || $row[1] === '') ? 'is-empty' : '' }}">{{ $display($row[1]) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-university"></i> प्रादेशिक विभागाने घेतलेल्या रकमेचे तपशील</h5>
            <small class="text-muted">Auto-calculated from Settings → Sangh Fees</small>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 details-table">
                <tbody>
                    <tr>
                        <th>प्रवेश शुल्क <small class="text-muted">(× {{ \App\Support\PradeshikFees::ADMISSION_MULTIPLIER }})</small></th>
                        <td class="{{ $pradeshikFees ? '' : 'is-empty' }}">{{ $fee('admission') }}</td>
                    </tr>
                    <tr>
                        <th>वार्षिक शुल्क <small class="text-muted">(× {{ \App\Support\PradeshikFees::ANNUAL_MULTIPLIER }})</small></th>
                        <td class="{{ $pradeshikFees ? '' : 'is-empty' }}">{{ $fee('annual') }}</td>
                    </tr>
                    <tr>
                        <th>विकास निधी शुल्क <small class="text-muted">(× {{ \App\Support\PradeshikFees::DEVELOPMENT_MULTIPLIER }})</small></th>
                        <td class="{{ $pradeshikFees ? '' : 'is-empty' }}">{{ $fee('development') }}</td>
                    </tr>
                    <tr class="table-warning">
                        <th>एकूण घेतलेली रक्कम (Total)</th>
                        <td>{{ $fee('total') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @include('sanghs._detail_styles')
</div>

@push('scripts')
    <script>
        function printSanghDetails() {
            window.print();
        }

    </script>
@endpush
@endsection
