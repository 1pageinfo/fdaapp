@extends('layouts.app')

@section('content')
@php
    $sangh = $sangh ?? new \App\Models\Sangh();
    $display = fn ($v) => ($v === null || $v === '') ? '-' : $v;
    $date = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d-m-Y') : '-';
    $inr = fn ($v) => '₹ ' . number_format((float) $v, 0);

    // Everything date-based on this page uses संघ नोंदणी दिनांक (the day the Sangh became
    // "Sangh Registered"). The created date is shown only as activity info at the bottom.
    $registeredAt = $sangh->registered_at;
    $quarterStr = '-';
    if ($registeredAt) {
        $month = (int) $registeredAt->format('m');
        $quarterStr = match (true) {
            $month >= 4 && $month <= 6 => 'Q1 (Apr - Jun)',
            $month >= 7 && $month <= 9 => 'Q2 (Jul - Sep)',
            $month >= 10 => 'Q3 (Oct - Dec)',
            default => 'Q4 (Jan - Mar)',
        };
    }

    // [label, value, wide?] — short fields sit two per row; long ones take the full row.
    $detailRows = [
        ['वर्ष', \App\Providers\AppServiceProvider::financialYear($sangh->registration_year), false],
        ['संघ नोंदणी दिनांक', $registeredAt ? $registeredAt->format('d-m-Y') : null, false],
        ['संघाचे नाव', $sangh->name_of_sangh, true],
        ['श्रेणी', \App\Support\SanghCodes::categoryLabel($sangh->category_code), false],
        ['संघ प्रकार', \App\Support\SanghCodes::typeLabel($sangh->sangh_type_code), false],
        ['प्रादेशिक विभाग', $sangh->pradeshik_vibhag, false],
        ['जिल्हा', $sangh->district, false],
        ['तालुका', $sangh->taluka, false],
        ['गाव', $sangh->village, false],
        ['शहर', $sangh->city, false],
        ['मुक्काम पोस्ट', $sangh->mukkam_post, false],
        ['पत्ता', $sangh->address, true],
        ['रस्ता / पथ', $sangh->road_path, false],
        ['विभाग/प्रभाग', $sangh->ward_section, false],
        ['पिनकोड', $sangh->pincode, false],
        ['नोंदणी त्रैमासिक (Quarter)', $registeredAt ? $quarterStr : null, false],
        ['पुरुष सभासद संख्या', $sangh->male, false],
        ['महिला सभासद संख्या', $sangh->female, false],
        ['एकूण सभासद संख्या', $sangh->total_members, false],
        ['', null, false, 'spacer'],
        ['अध्यक्ष', $sangh->president, false],
        ['अध्यक्ष मोबाईल', $sangh->president_phone, false],
        ['अध्यक्ष व्हॉट्सअप', $sangh->president_whatsapp, false],
        ['अध्यक्ष इमेल', $sangh->president_email, false],
        ['सचिव', $sangh->secretary, false],
        ['सचिव मोबाईल', $sangh->secretary_phone, false],
        ['सचिव व्हॉट्सअप', $sangh->secretary_whatsapp, false],
        ['सचिव इमेल', $sangh->secretary_email, false],
    ];

    // प्रादेशिक विभागाची फी: always calculated from Settings → Sangh Fees.
    $pradeshikFees = \App\Support\PradeshikFees::calculate($sangh->male, $sangh->female);
    $fee = fn ($key) => $pradeshikFees ? '₹ ' . number_format($pradeshikFees[$key], 0) : '-';

    $statusGroups = [
        'sangh_registered' => ['Sangh Registered', 'bg-primary text-white'],
        'information_approved' => ['Information approved', 'bg-success text-white'],
        'unpaid' => ['Unpaid', 'bg-warning text-dark'],
    ];
    $kindLabel = ['registration' => 'नोंदणी', 'renewal' => 'नूतनीकरण', 'backdated' => 'मागील वर्ष'];
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
                <i class="fa fa-file-text-o"></i> Receipts <span class="num-pill ms-1">{{ $receiptCount }}</span>
            </a>
            <a href="{{ route('sanghs.edit', $sangh) }}" class="btn btn-sm btn-primary" data-perm="sanghs.edit">
                <i class="fa fa-edit"></i> Edit
            </a>
            <a href="{{ route('sanghs.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
            <button type="button" class="btn btn-sm btn-success" onclick="window.print()">
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

    {{-- Reference numbers --}}
    <div class="row g-3 mb-3">
        @foreach([
            ['fa-hashtag', 'Sangh Number', $sangh->unique_ref_no],
            ['fa-university', 'Pradeshik Ref', $sangh->pradeshik_ref_no],
            ['fa-map-marker', 'District Ref', $sangh->district_ref_no],
        ] as [$icon, $label, $value])
            <div class="col-md-4">
                <div class="card ref-card h-100">
                    <div class="card-body">
                        <div class="ref-icon"><i class="fa {{ $icon }}"></i></div>
                        <div>
                            <small class="d-block">{{ $label }}</small>
                            @if($value)
                                <span class="num-pill num-pill-lg">{{ $value }}</span>
                            @else
                                <strong class="text-muted">-</strong>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Master details: two columns --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-id-card"></i> Master Details</h5>
        </div>
        <div class="kv-grid">
            @foreach($detailRows as $row)
                @if(($row[3] ?? null) === 'spacer')
                    <div class="kv-item kv-spacer"></div>
                @else
                    <div class="kv-item {{ $row[2] ? 'kv-wide' : '' }}">
                        <div class="kv-label">{{ $row[0] }}</div>
                        <div class="kv-value {{ ($row[1] === null || $row[1] === '') ? 'is-empty' : '' }}">{{ $display($row[1]) }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- प्रादेशिक fees: one compact row --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-university"></i> प्रादेशिक विभागाने घेतलेल्या रकमेचे तपशील</h5>
            <small class="text-muted no-print">Auto-calculated from Settings → Sangh Fees</small>
        </div>
        <div class="kv-grid kv-grid-4">
            <div class="kv-item">
                <div class="kv-label">प्रवेश शुल्क <small>(× {{ \App\Support\PradeshikFees::ADMISSION_MULTIPLIER }})</small></div>
                <div class="kv-value">{{ $fee('admission') }}</div>
            </div>
            <div class="kv-item">
                <div class="kv-label">वार्षिक शुल्क <small>(× {{ \App\Support\PradeshikFees::ANNUAL_MULTIPLIER }})</small></div>
                <div class="kv-value">{{ $fee('annual') }}</div>
            </div>
            <div class="kv-item">
                <div class="kv-label">विकास निधी शुल्क <small>(× {{ \App\Support\PradeshikFees::DEVELOPMENT_MULTIPLIER }})</small></div>
                <div class="kv-value">{{ $fee('development') }}</div>
            </div>
            <div class="kv-item kv-total">
                <div class="kv-label">एकूण घेतलेली रक्कम (Total)</div>
                <div class="kv-value">{{ $fee('total') }}</div>
            </div>
        </div>
    </div>

    {{-- All receipts, grouped by status (every status — printed too) --}}
    <div class="card mb-3" id="receipts">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0"><i class="fa fa-file-text-o"></i> Receipts</h5>
            <div class="d-flex flex-wrap gap-2 small">
                @foreach($statusGroups as $code => [$label, $class])
                    <span class="badge {{ $class }}">{{ $label }}: {{ $allReceipts->where('status', $code)->count() }}</span>
                @endforeach
            </div>
        </div>
        @if($allReceipts->isEmpty())
            <div class="card-body text-muted">No receipts yet.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 receipts-table">
                    <thead>
                        <tr>
                            <th>वर्ष</th>
                            <th>पावती क्र.</th>
                            <th>पावती दिनांक</th>
                            <th>नोंदणी दिनांक</th>
                            <th class="text-end">सभासद</th>
                            <th class="text-end">एकूण</th>
                            <th class="text-end">भरलेली</th>
                            <th class="text-end">बाकी</th>
                            <th>बँक / चेक-Transaction क्र.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statusGroups as $code => [$label, $class])
                            <tr class="status-group-row">
                                <td colspan="9"><span class="badge {{ $class }}">{{ $label }}</span></td>
                            </tr>
                            @forelse($allReceipts->where('status', $code) as $rc)
                                <tr>
                                    <td class="text-nowrap">@fy($rc['year']) <small class="text-muted">{{ $kindLabel[$rc['kind']] }}</small></td>
                                    <td>{{ $rc['no'] ?: '-' }}</td>
                                    <td>{{ $date($rc['date']) }}</td>
                                    <td>{{ $date($rc['registered_at']) }}</td>
                                    <td class="text-end">{{ $rc['members'] ?? '-' }}</td>
                                    <td class="text-end">{{ $inr($rc['total']) }}</td>
                                    <td class="text-end text-success">{{ $inr($rc['paid']) }}</td>
                                    <td class="text-end {{ $rc['balance'] > 0 ? 'text-danger fw-bold' : 'text-success' }}">{{ $inr($rc['balance']) }}</td>
                                    <td class="small">
                                        {{ $rc['bank'] ?: '' }}
                                        @if($rc['cheque_no']) <span class="text-muted">· {{ $rc['cheque_no'] }}</span> @endif
                                        @if($rc['cheque_date']) <span class="text-muted">· {{ $date($rc['cheque_date']) }}</span> @endif
                                        @if(! $rc['bank'] && ! $rc['cheque_no']) - @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-muted small ps-4">No {{ strtolower($label) }} receipts.</td></tr>
                            @endforelse
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Activity info only (created date is not used for anything else) --}}
    <p class="small text-muted mb-0 activity-note">
        <i class="fa fa-clock-o"></i>
        Form created {{ $date($sangh->created_date ?? $sangh->created_at) }}@if($sangh->creator) by {{ $sangh->creator->name }}@endif
        · last updated {{ $date($sangh->updated_at) }}
    </p>

    @include('sanghs._detail_styles')
</div>
@endsection
