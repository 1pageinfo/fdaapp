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
    ];
@endphp

<div class="container-fluid mt-4 mb-5 sangh-show-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h2 class="details-title mb-1">Sangh Details</h2>
            <p class="text-muted mb-0 details-subtitle">Complete profile and renewal records</p>
        </div>
        <div class="d-flex flex-wrap gap-2 no-print">
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
            <a href="{{ route('sanghs.edit', $sangh) }}" class="btn btn-sm btn-primary">
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

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3">
                    <small class="text-muted d-block">Sang Number</small>
                    <strong>{{ $display($sangh->unique_ref_no) }}</strong>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body py-3">
                    <small class="text-muted d-block">District Ref</small>
                    <strong>{{ $display($sangh->district_ref_no) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-id-card text-primary"></i> Master Details</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 details-table">
                <tbody>
                    @foreach($masterRows as $row)
                        <tr>
                            <th>{{ $row[0] }}</th>
                            <td>{{ $display($row[1]) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-landmark text-primary"></i> प्रादेशिक विभागाने घेतलेल्या रकमेचे तपशील</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 details-table">
                <tbody>
                    <tr>
                        <th>प्रवेश शुल्क</th>
                        <td>{{ $sangh->pradeshik_admission_fee !== null ? '₹ ' . number_format($sangh->pradeshik_admission_fee, 2) : '-' }}</td>
                    </tr>
                    <tr>
                        <th>वार्षिक शुल्क</th>
                        <td>{{ $sangh->pradeshik_annual_fee !== null ? '₹ ' . number_format($sangh->pradeshik_annual_fee, 2) : '-' }}</td>
                    </tr>
                    <tr>
                        <th>विकास निधी शुल्क</th>
                        <td>{{ $sangh->pradeshik_development_fee !== null ? '₹ ' . number_format($sangh->pradeshik_development_fee, 2) : '-' }}</td>
                    </tr>
                    <tr class="table-warning">
                        <th>एकूण रक्कम</th>
                        <td><strong>₹ {{ number_format(($sangh->pradeshik_admission_fee ?? 0) + ($sangh->pradeshik_annual_fee ?? 0) + ($sangh->pradeshik_development_fee ?? 0), 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-file-text-o text-primary"></i> New register Sangh Receipt</h5>
        </div>
        @if($newRegisterReceipt)
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0 renewal-table">
                <thead class="table-light">
                    <tr>
                        <th>Year</th>
                        <th>Status</th>
                        <th>फेस्कॉम पावती क्र.</th>
                        <th>फेस्कॉम पावती दिनांक</th>
                        <th>पुरुष</th>
                        <th>महिला</th>
                        <th>एकूण</th>
                        <th>प्रवेश शुल्क</th>
                        <th>वार्षिक शुल्क</th>
                        <th>विकास निधी शुल्क</th>
                        <th>एकूण रक्कम</th>
                        <th>पावती रक्कम (भरलेली)</th>
                        <th>बाकी रक्कम</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <form id="newRegisterReceiptForm" action="{{ route('sanghs.registration_receipt.update', $sangh) }}" method="POST" class="d-none">
                        @csrf
                    </form>
                    @php
                        $newRegisterTotal = ($newRegisterReceipt->admission_fee ?? 0) + ($newRegisterReceipt->annual_fee ?? 0) + ($newRegisterReceipt->development_fee ?? 0);
                        $newRegisterBalance = $newRegisterTotal - ($newRegisterReceipt->paid_amount ?? 0);
                    @endphp
                    <tr>
                        <td><strong>@fy($newRegisterReceipt->receipt_year ?? $registrationYear)</strong></td>
                        <td>
                            <select name="status" class="form-select form-select-sm" form="newRegisterReceiptForm">
                                <option value="paid" @selected($newRegisterReceipt->is_paid)>Paid</option>
                                <option value="unpaid" @selected(!$newRegisterReceipt->is_paid)>Unpaid</option>
                            </select>
                        </td>
                        <td><input type="text" class="form-control form-control-sm bg-light" value="{{ $newRegisterReceipt->feskcom_receipt_no }}" readonly disabled></td>
                        <td><input type="date" name="feskcom_receipt_date" class="form-control form-control-sm" value="{{ optional($newRegisterReceipt->feskcom_receipt_date)->format('Y-m-d') }}" form="newRegisterReceiptForm"></td>
                        <td><input type="number" class="form-control form-control-sm" value="{{ $registeredMale }}" readonly></td>
                        <td><input type="number" class="form-control form-control-sm" value="{{ $registeredFemale }}" readonly></td>
                        <td><input type="number" class="form-control form-control-sm" value="{{ $registeredTotal }}" readonly></td>
                        <td><input type="number" class="form-control form-control-sm bg-light" value="{{ $newRegisterReceipt->admission_fee }}" disabled></td>
                        <td><input type="number" class="form-control form-control-sm bg-light" value="{{ $newRegisterReceipt->annual_fee }}" disabled></td>
                        <td><input type="number" class="form-control form-control-sm bg-light" value="{{ $newRegisterReceipt->development_fee }}" disabled></td>
                        <td><input type="number" class="form-control form-control-sm bg-light fw-bold" value="{{ number_format($newRegisterTotal, 2) }}" disabled></td>
                        <td><input type="number" name="paid_amount" min="0" step="0.01" class="form-control form-control-sm" value="{{ $newRegisterReceipt->paid_amount }}" form="newRegisterReceiptForm"></td>
                        <td><input type="number" class="form-control form-control-sm {{ $newRegisterBalance > 0 ? 'bg-danger-subtle text-danger fw-bold' : 'bg-success-subtle text-success fw-bold' }}" value="{{ number_format($newRegisterBalance, 2) }}" disabled></td>
                        <td class="text-nowrap">
                            <button type="submit" class="btn btn-sm btn-success mb-1" form="newRegisterReceiptForm">
                                <i class="fa fa-save"></i> Save
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info mb-1" data-bs-toggle="modal" data-bs-target="#receiptModalNewRegister">
                                <i class="fa fa-receipt"></i> Receipt
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @else
            <div class="card-body text-muted">Set registration year in Sangh form to use New register Sangh Receipt.</div>
        @endif
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0"><i class="fa fa-refresh text-primary"></i> Renewal Sangh Receipt</h5>
                <small class="text-muted">{{ $renewals->count() }} record(s)</small>
            </div>
            @if(count($availableYears))
            <form action="{{ route('sanghs.renewals.create', $sangh) }}" method="POST" class="d-flex align-items-center gap-2 no-print">
                @csrf
                <select name="renewal_year" class="form-select form-select-sm" style="width:120px;" required>
                    <option value="">Year</option>
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}">@fy($y)</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-primary text-nowrap">
                    <i class="fa fa-plus"></i> Create Record
                </button>
            </form>
            @endif
        </div>
        @if($renewals->isEmpty())
            <div class="card-body text-muted">No renewal records yet. Use "Create Record" above to add a year.</div>
        @else
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0 renewal-table">
                <thead class="table-light">
                    <tr>
                        <th>Year</th>
                        <th>Status</th>
                        <th>फेस्कॉम पावती क्र.</th>
                        <th>फेस्कॉम पावती दिनांक</th>
                        <th>पुरुष</th>
                        <th>महिला</th>
                        <th>एकूण</th>
                        <th>वार्षिक शुल्क</th>
                        <th>विकास निधी शुल्क</th>
                        <th>दंड शुल्क</th>
                        <th>एकूण रक्कम</th>
                        <th>पावती रक्कम (भरलेली)</th>
                        <th>बाकी रक्कम</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($renewals as $renewal)
                        <tr>
                            <form action="{{ route('sanghs.renewals.update', [$sangh, $renewal->renewal_year]) }}" method="POST">
                                @csrf
                                <td><strong>@fy($renewal->renewal_year)</strong></td>
                                <td>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="paid" @selected($renewal->is_paid)>Paid</option>
                                        <option value="unpaid" @selected(!$renewal->is_paid)>Unpaid</option>
                                    </select>
                                </td>
                                <td><input type="text" class="form-control form-control-sm bg-light" value="{{ $renewal->feskcom_receipt_no ?: 'FSREN/auto' }}" readonly disabled></td>
                                <td><input type="date" name="feskcom_receipt_date" class="form-control form-control-sm" value="{{ optional($renewal->feskcom_receipt_date)->format('Y-m-d') }}"></td>
                                <td><input type="number" name="male_members" min="0" class="form-control form-control-sm renewal-male" value="{{ $renewal->male_members }}"></td>
                                <td><input type="number" name="female_members" min="0" class="form-control form-control-sm renewal-female" value="{{ $renewal->female_members }}"></td>
                                <td><input type="number" name="total_members" min="0" class="form-control form-control-sm bg-light renewal-total" value="{{ $renewal->total_members }}" readonly></td>
                                <td><input type="number" class="form-control form-control-sm bg-light renewal-annual" value="{{ $renewal->annual_fee }}" disabled></td>
                                <td><input type="number" class="form-control form-control-sm bg-light renewal-development" value="{{ $renewal->development_fee }}" disabled></td>
                                <td><input type="number" name="penalty_fee" min="0" step="0.01" class="form-control form-control-sm" value="{{ $renewal->penalty_fee }}"></td>
                                <td><input type="number" class="form-control form-control-sm bg-light fw-bold" value="{{ number_format(($renewal->annual_fee ?? 0) + ($renewal->development_fee ?? 0) + ($renewal->penalty_fee ?? 0), 2) }}" disabled></td>
                                <td><input type="number" name="paid_amount" min="0" step="0.01" class="form-control form-control-sm" value="{{ $renewal->paid_amount }}"></td>
                                <td><input type="number" class="form-control form-control-sm {{ (($renewal->annual_fee ?? 0) + ($renewal->development_fee ?? 0) + ($renewal->penalty_fee ?? 0) - ($renewal->paid_amount ?? 0)) > 0 ? 'bg-danger-subtle text-danger fw-bold' : 'bg-success-subtle text-success fw-bold' }}" value="{{ number_format(($renewal->annual_fee ?? 0) + ($renewal->development_fee ?? 0) + ($renewal->penalty_fee ?? 0) - ($renewal->paid_amount ?? 0), 2) }}" disabled></td>
                                <td class="text-nowrap">
                                    <button type="submit" class="btn btn-sm btn-success mb-1">
                                        <i class="fa fa-save"></i> Save
                                    </button>
                                    @if($renewal->is_paid)
                                        <button type="button" class="btn btn-sm btn-outline-info mb-1" data-bs-toggle="modal" data-bs-target="#receiptModal{{ $renewal->renewal_year }}">
                                            <i class="fa fa-receipt"></i> Receipt
                                        </button>
                                    @endif
                            </form>
                            <form action="{{ route('sanghs.renewals.destroy', [$sangh, $renewal->renewal_year]) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete @fy($renewal->renewal_year) renewal record?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mb-1">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                                </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    @if(!empty($newRegisterReceipt))
        <div class="modal fade" id="receiptModalNewRegister" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-receipt"></i> Receipt - @fy($newRegisterReceipt->receipt_year ?? $registrationYear)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="receipt-print border p-4 bg-white">
                            <div class="text-center mb-4 pb-3 border-bottom">
                                <h3 class="mb-1" style="font-weight: 700;">RECEIPT</h3>
                                <p class="text-muted mb-0">New register Sangh Receipt</p>
                                <p class="small text-muted mb-0">Federation of Senior Citizens Organisation of Maharashtra</p>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p><strong>संघ नाव:</strong> {{ $display($sangh->name_of_sangh) }}</p>
                                    <p><strong>Sang Number:</strong> {{ $display($sangNumber) }}</p>
                                    <p><strong>जिल्हा:</strong> {{ $display($sangh->district) }}</p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p><strong>वर्ष:</strong> @fy($newRegisterReceipt->receipt_year ?? $registrationYear)</p>
                                    <p><strong>दिनांक:</strong> {{ now()->format('d-m-Y') }}</p>
                                    <p>
                                        <strong>पावती स्थिति:</strong>
                                        <span class="badge {{ $newRegisterReceipt->is_paid ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $newRegisterReceipt->is_paid ? 'PAID' : 'UNPAID' }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>विवरण</th>
                                            <th class="text-end">रक्कम</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>प्रवेश शुल्क</td>
                                            <td class="text-end">₹ {{ number_format($newRegisterReceipt->admission_fee ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>वार्षिक शुल्क</td>
                                            <td class="text-end">₹ {{ number_format($newRegisterReceipt->annual_fee ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>विकास निधी शुल्क</td>
                                            <td class="text-end">₹ {{ number_format($newRegisterReceipt->development_fee ?? 0, 2) }}</td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td><strong>एकूण रक्कम</strong></td>
                                            <td class="text-end"><strong>₹ {{ number_format(($newRegisterReceipt->annual_fee ?? 0) + ($newRegisterReceipt->admission_fee ?? 0) + ($newRegisterReceipt->development_fee ?? 0), 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong class="text-success">भरलेली रक्कम</strong></td>
                                            <td class="text-end"><strong class="text-success">₹ {{ number_format($newRegisterReceipt->paid_amount ?? 0, 2) }}</strong></td>
                                        </tr>
                                        @php
                                            $newRegisterModalBalance = (($newRegisterReceipt->annual_fee ?? 0) + ($newRegisterReceipt->admission_fee ?? 0) + ($newRegisterReceipt->development_fee ?? 0)) - ($newRegisterReceipt->paid_amount ?? 0);
                                        @endphp
                                        <tr>
                                            <td><strong class="{{ $newRegisterModalBalance > 0 ? 'text-danger' : 'text-success' }}">बाकी रक्कम</strong></td>
                                            <td class="text-end"><strong class="{{ $newRegisterModalBalance > 0 ? 'text-danger' : 'text-success' }}">₹ {{ number_format($newRegisterModalBalance, 2) }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>फेस्कॉम पावती क्र.:</strong> {{ $display($newRegisterReceipt->feskcom_receipt_no) }}</p>
                                    <p class="mb-1"><strong>Generated by:</strong> {{ $newRegisterReceipt->user?->name ?? 'System' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>पुरुष सदस्य:</strong> {{ $newRegisterReceipt->male_members ?? 0 }}</p>
                                    <p class="mb-1"><strong>महिला सदस्य:</strong> {{ $newRegisterReceipt->female_members ?? 0 }}</p>
                                    <p class="mb-1"><strong>एकूण सदस्य:</strong> {{ $newRegisterReceipt->total_members ?? 0 }}</p>
                                </div>
                            </div>

                            <div class="text-center border-top pt-3">
                                <p class="small text-muted mb-0">This is an automated receipt. Please retain for your records.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-info btn-sm" onclick="printReceipt('NewRegister')">
                            <i class="fa fa-print"></i> Print Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @foreach($renewals as $renewal)
        <div class="modal fade" id="receiptModal{{ $renewal->renewal_year }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-receipt"></i> Receipt - @fy($renewal->renewal_year)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="receipt-print border p-4 bg-white">
                            <div class="text-center mb-4 pb-3 border-bottom">
                                <h3 class="mb-1" style="font-weight: 700;">RECEIPT</h3>
                                <p class="text-muted mb-0">Sangh Renewal Receipt</p>
                                <p class="small text-muted mb-0">Federation of Senior Citizens Organisation of Maharashtra</p>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p><strong>संघ नाव:</strong> {{ $display($sangh->name_of_sangh) }}</p>
                                    <p><strong>Sang Number:</strong> {{ $display($sangNumber) }}</p>
                                    <p><strong>जिल्हा:</strong> {{ $display($sangh->district) }}</p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p><strong>वर्ष:</strong> @fy($renewal->renewal_year)</p>
                                    <p><strong>दिनांक:</strong> {{ now()->format('d-m-Y') }}</p>
                                    <p>
                                        <strong>पावती स्थिति:</strong>
                                        <span class="badge {{ $renewal->is_paid ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $renewal->is_paid ? 'PAID' : 'UNPAID' }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>विवरण</th>
                                            <th class="text-end">रक्कम</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>वार्षिक शुल्क</td>
                                            <td class="text-end">₹ {{ number_format($renewal->annual_fee ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>विकास निधी शुल्क</td>
                                            <td class="text-end">₹ {{ number_format($renewal->development_fee ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>दंड शुल्क</td>
                                            <td class="text-end">₹ {{ number_format($renewal->penalty_fee ?? 0, 2) }}</td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td><strong>एकूण रक्कम</strong></td>
                                            <td class="text-end"><strong>₹ {{ number_format(($renewal->annual_fee ?? 0) + ($renewal->development_fee ?? 0) + ($renewal->penalty_fee ?? 0), 2) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong class="text-success">भरलेली रक्कम</strong></td>
                                            <td class="text-end"><strong class="text-success">₹ {{ number_format($renewal->paid_amount ?? 0, 2) }}</strong></td>
                                        </tr>
                                        @php
                                            $renewalModalBalance = (($renewal->annual_fee ?? 0) + ($renewal->development_fee ?? 0) + ($renewal->penalty_fee ?? 0)) - ($renewal->paid_amount ?? 0);
                                        @endphp
                                        <tr>
                                            <td><strong class="{{ $renewalModalBalance > 0 ? 'text-danger' : 'text-success' }}">बाकी रक्कम</strong></td>
                                            <td class="text-end"><strong class="{{ $renewalModalBalance > 0 ? 'text-danger' : 'text-success' }}">₹ {{ number_format($renewalModalBalance, 2) }}</strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>फेस्कॉम पावती क्र.:</strong> {{ $display($renewal->feskcom_receipt_no) }}</p>
                                    <p class="mb-1"><strong>Generated by:</strong> {{ $renewal->user?->name ?? 'System' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>पुरुष सदस्य:</strong> {{ $renewal->male_members ?? 0 }}</p>
                                    <p class="mb-1"><strong>महिला सदस्य:</strong> {{ $renewal->female_members ?? 0 }}</p>
                                    <p class="mb-1"><strong>एकूण सदस्य:</strong> {{ $renewal->total_members ?? 0 }}</p>
                                </div>
                            </div>

                            <div class="text-center border-top pt-3">
                                <p class="small text-muted mb-0">This is an automated receipt. Please retain for your records.</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-info btn-sm" onclick="printReceipt({{ $renewal->renewal_year }})">
                            <i class="fa fa-print"></i> Print Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <style>
        .sangh-show-page .details-title {
            font-size: 2rem;
            line-height: 1.1;
            font-weight: 600;
        }

        .sangh-show-page .details-subtitle {
            font-size: 0.95rem;
        }

        .sangh-show-page .card {
            border-radius: 10px;
        }

        .sangh-show-page .table th,
        .sangh-show-page .table td {
            vertical-align: middle;
        }

        .sangh-show-page .details-table th {
            width: 32%;
            background-color: #f8f9fa;
            color: #495057;
        }

        .sangh-show-page .details-table td {
            color: #212529;
        }

        .sangh-show-page .renewal-table thead th {
            font-size: 12px;
            white-space: nowrap;
        }

        .sangh-show-page .renewal-table input,
        .sangh-show-page .renewal-table select {
            min-width: 90px;
        }

        .sangh-show-page .alert {
            border-left: 4px solid #198754;
            border-radius: 8px;
        }

        .receipt-print {
            border: 2px solid #333 !important;
            background-color: #fff;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }

            .no-print,
            .modal,
            .btn,
            .modal-backdrop {
                display: none !important;
            }

            .navbar,
            #sidebar {
                display: none !important;
            }

            .page-body-wrapper,
            .main-panel {
                margin-left: 0 !important;
                margin-top: 0 !important;
                padding-top: 0 !important;
                width: 100% !important;
                min-height: 0 !important;
            }

            .sangh-show-page {
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Let wide tables print in full instead of being clipped by the scroll container */
            .sangh-show-page .table-responsive {
                overflow: visible !important;
            }

            .sangh-show-page .card {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .sangh-show-page table {
                width: 100% !important;
                table-layout: auto !important;
                font-size: 9px !important;
            }

            .sangh-show-page .renewal-table th,
            .sangh-show-page .renewal-table td {
                padding: 2px 3px !important;
                white-space: normal !important;
            }

            /* Remove the fixed min-width that forces horizontal overflow on print */
            .sangh-show-page .renewal-table input,
            .sangh-show-page .renewal-table select {
                min-width: 0 !important;
                width: 100% !important;
                font-size: 9px !important;
                padding: 1px 2px !important;
                border: none !important;
                background: transparent !important;
                -webkit-appearance: none;
                appearance: none;
            }

            .sangh-show-page input[disabled],
            .sangh-show-page select[disabled],
            .sangh-show-page input[readonly] {
                color: #000 !important;
                -webkit-text-fill-color: #000 !important;
                opacity: 1 !important;
            }
        }

        @media (max-width: 992px) {
            .sangh-show-page .details-title {
                font-size: 1.6rem;
            }
        }
    </style>
</div>

@push('scripts')
    <script>
        function printSanghDetails() {
            window.print();
        }

        (function () {
            // Renewal Sangh Receipt: वार्षिक शुल्क / विकास निधी शुल्क always follow the
            // admin-configured sangh fee slabs — auto-calculated, never entered manually.
            const feeSlabs = @json($feeSlabsForRenewal);
            const developmentFeeRate = @json($developmentFeeRateForRenewal);

            function annualFeeForMembers(total) {
                if (total === null || total === '' || isNaN(total)) return 0;
                const n = parseInt(total, 10);
                const slab = feeSlabs.find(function (s) {
                    return n >= s.min_members && (s.max_members === null || n <= s.max_members);
                });
                return slab ? parseFloat(slab.annual_fee) : 0;
            }

            function syncRenewalRow(row) {
                const maleField = row.querySelector('.renewal-male');
                const femaleField = row.querySelector('.renewal-female');
                const totalField = row.querySelector('.renewal-total');
                const annualField = row.querySelector('.renewal-annual');
                const developmentField = row.querySelector('.renewal-development');
                if (!maleField || !femaleField || !totalField) return;

                const male = parseInt(maleField.value || '0', 10) || 0;
                const female = parseInt(femaleField.value || '0', 10) || 0;
                const total = (maleField.value === '' && femaleField.value === '') ? '' : (male + female);

                totalField.value = total;
                if (annualField) annualField.value = annualFeeForMembers(total);
                if (developmentField) developmentField.value = (total === '' ? 0 : developmentFeeRate * total);
            }

            document.querySelectorAll('.renewal-table tbody tr').forEach(function (row) {
                syncRenewalRow(row);
                ['.renewal-male', '.renewal-female'].forEach(function (sel) {
                    const field = row.querySelector(sel);
                    if (field) field.addEventListener('input', function () { syncRenewalRow(row); });
                });
            });
        })();

        function printReceipt(year) {
            try {
                const element = document.querySelector('#receiptModal' + year + ' .receipt-print');

                if (!element) {
                    alert('Receipt element not found!');
                    return;
                }

                const printWindow = window.open('', '', 'height=600,width=800');
                printWindow.document.write('<html><head><title>Receipt</title>');
                printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">');
                printWindow.document.write('<style>body { font-family: Arial, sans-serif; margin: 20px; } @media print { body { margin: 0; } }</style>');
                printWindow.document.write('</head><body>');
                printWindow.document.write(element.innerHTML);
                printWindow.document.write('</body></html>');
                printWindow.document.close();

                setTimeout(function () {
                    printWindow.print();
                }, 250);
            } catch (error) {
                console.error('Error in printReceipt:', error);
                alert('Error: ' + error.message);
            }
        }
    </script>
@endpush
@endsection
