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
    // live-calculate वार्षिक शुल्क / विकास निधी शुल्क in the Edit Receipt pop-ups.
    $feeSlabsForRenewal = \App\Models\SanghFeeSlab::orderBy('min_members')->get(['min_members', 'max_members', 'annual_fee']);
    $developmentFeeRateForRenewal = (float) \App\Models\Setting::getValue('sangh_development_fee_rate', 0);
@endphp

<div class="container-fluid mt-4 mb-5 sangh-show-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h2 class="details-title mb-1">Receipts</h2>
            <p class="text-muted mb-0 details-subtitle">{{ $display($sangh->name_of_sangh) }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 no-print page-actions">
            <a href="{{ route('sanghs.show', $sangh) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa fa-arrow-left"></i> Sangh Details
            </a>
            <a href="{{ route('sanghs.edit', $sangh) }}" class="btn btn-sm btn-primary" data-perm="sanghs.edit">
                <i class="fa fa-edit"></i> Edit Sangh
            </a>
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
                        <span class="num-pill num-pill-lg">{{ $display($sangh->unique_ref_no) }}</span>
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
                        <span class="num-pill num-pill-lg">{{ $display($sangh->district_ref_no) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== RECEIPTS (all three kinds in one place) ===================== --}}
    @php
        $statusLabel = fn ($s) => match ($s) { 'sangh_registered' => 'Sangh Registered', 'information_approved' => 'Information approved', default => 'Unpaid' };
        $statusClass = fn ($s) => match ($s) { 'sangh_registered' => 'bg-primary text-white', 'information_approved' => 'bg-success text-white', default => 'bg-warning text-dark' };
        $regIsRegistered = $newRegisterReceipt && $newRegisterReceipt->status === 'sangh_registered';
        // $allReceipts (registration + renewal + backdated, newest first) comes from the controller.
        $statusTabs = [
            'all' => ['All', null],
            'unpaid' => ['Unpaid', 'fa-clock-o'],
            'information_approved' => ['Information approved', 'fa-check'],
            'sangh_registered' => ['Sangh Registered', 'fa-certificate'],
        ];
    @endphp
    <div class="card mb-4" id="receipts">
        <div class="card-header bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0"><i class="fa fa-file-text-o text-primary"></i> Receipts</h5>
            <small class="text-muted"><span class="num-pill">{{ $allReceipts->count() }}</span> receipt(s)</small>
        </div>
        <div class="card-body pb-2 no-print">
            <ul class="nav nav-tabs receipt-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#rcpt-registered" type="button" role="tab">
                        <i class="fa fa-certificate"></i> Registered Receipt
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rcpt-renewal" type="button" role="tab">
                        <i class="fa fa-refresh"></i> Renewal Receipt
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rcpt-backdated" type="button" role="tab">
                        <i class="fa fa-history"></i> Backdated Receipt
                    </button>
                </li>
            </ul>
            <div class="tab-content border border-top-0 rounded-bottom p-3">
                {{-- 1. Registered: generated automatically once status is Sangh Registered --}}
                <div class="tab-pane fade show active" id="rcpt-registered" role="tabpanel">
                    @if(! $newRegisterReceipt)
                        <p class="text-muted mb-0">Set the वर्ष (registration year) in the Sangh form to start the registration receipt.</p>
                    @elseif($regIsRegistered)
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <span><i class="fa fa-lock text-success"></i> Sangh registered on
                                <strong>{{ optional($newRegisterReceipt->registered_at)->format('d-m-Y') ?? '-' }}</strong>.
                                Registration is done once — use <strong>Renewal Receipt</strong> from now on.</span>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#receiptModalNewRegister">
                                <i class="fa fa-eye"></i> View / Print
                            </button>
                        </div>
                    @else
                        <p class="mb-1">
                            Current status: <span class="badge {{ $statusClass($newRegisterReceipt->status) }}">{{ $statusLabel($newRegisterReceipt->status) }}</span>
                        </p>
                        <p class="text-muted small mb-0">
                            The registration receipt is generated automatically when the status is set to <strong>Sangh Registered</strong>.
                        </p>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#editReceiptNewRegister" data-perm="sanghs.edit">
                            <i class="fa fa-pencil"></i> Update status / payment
                        </button>
                    @endif
                </div>

                {{-- 2. Renewal: only the next year --}}
                <div class="tab-pane fade" id="rcpt-renewal" role="tabpanel">
                    @if(! $isRegistered)
                        <p class="text-muted mb-0"><i class="fa fa-lock"></i> Renewal is available after the Sangh is registered — set the <strong>Registered Receipt</strong> status to <strong>Sangh Registered</strong> first.</p>
                    @elseif($nextRenewalYear)
                        <form action="{{ route('sanghs.renewals.create', $sangh) }}" method="POST" class="d-flex flex-wrap align-items-center gap-2" data-perm="sanghs.edit">
                            @csrf
                            <input type="hidden" name="receipt_type" value="renewal">
                            <input type="hidden" name="renewal_year" value="{{ $nextRenewalYear }}">
                            <span>Next renewal year: <strong>@fy($nextRenewalYear)</strong></span>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Create Renewal Receipt</button>
                        </form>
                        <small class="text-muted d-block mt-2">Renewal is created one year at a time — only the year after the latest receipt.</small>
                    @else
                        <p class="text-muted mb-0">No renewal is due yet — the next year's receipt already exists. Use <strong>Backdated Receipt</strong> for older years.</p>
                    @endif
                </div>

                {{-- 3. Backdated: any past year 1979-80 → last year without a receipt (not the current year) --}}
                <div class="tab-pane fade" id="rcpt-backdated" role="tabpanel">
                    @if(! $isRegistered)
                        <p class="text-muted mb-0"><i class="fa fa-lock"></i> Backdated receipts are available after the Sangh is registered — set the <strong>Registered Receipt</strong> status to <strong>Sangh Registered</strong> first.</p>
                    @elseif(count($backdatedYears))
                        <form action="{{ route('sanghs.renewals.create', $sangh) }}" method="POST" class="d-flex flex-wrap align-items-center gap-2" data-perm="sanghs.edit">
                            @csrf
                            <input type="hidden" name="receipt_type" value="backdated">
                            <label class="mb-0">Year</label>
                            <select name="renewal_year" class="form-select form-select-sm" style="width:140px;" required>
                                @foreach($backdatedYears as $y)
                                    <option value="{{ $y }}">@fy($y)</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-secondary"><i class="fa fa-plus"></i> Create Receipt</button>
                        </form>
                        <small class="text-muted d-block mt-2">Any past year from {{ \App\Providers\AppServiceProvider::financialYear(\App\Support\SanghYears::FIRST) }} to {{ \App\Providers\AppServiceProvider::financialYear(date('Y') - 1) }} that has no receipt yet. The current year is not available here.</small>
                    @else
                        <p class="text-muted mb-0">Every year from {{ \App\Providers\AppServiceProvider::financialYear(\App\Support\SanghYears::FIRST) }} to {{ \App\Providers\AppServiceProvider::financialYear(date('Y') - 1) }} already has a receipt.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Receipts by status: All / Unpaid / Information approved / Sangh Registered --}}
        @if($allReceipts->isEmpty())
            <div class="card-body pt-2 text-muted">No receipts yet.</div>
        @else
        <div class="px-3 pt-2 no-print">
            <ul class="nav nav-pills status-tabs gap-1" role="tablist">
                @foreach($statusTabs as $key => [$label, $icon])
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $key === 'all' ? 'active' : '' }}" data-status-tab="{{ $key }}">
                            @if($icon)<i class="fa {{ $icon }}"></i>@endif {{ $label }}
                            <span class="num-pill ms-1">{{ $key === 'all' ? $allReceipts->count() : $allReceipts->where('status', $key)->count() }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0" id="receiptsTable">
                <thead class="table-light">
                    <tr>
                        <th>वर्ष</th>
                        <th>पावती क्र.</th>
                        <th>पावती दिनांक</th>
                        <th>Status</th>
                        <th class="text-end">एकूण</th>
                        <th class="text-end">भरलेली</th>
                        <th class="text-end">बाकी</th>
                        <th class="no-print"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allReceipts as $rc)
                        <tr data-status="{{ $rc['status'] }}">
                            <td>@fy($rc['year'])</td>
                            <td>{{ $rc['no'] ?: '-' }}</td>
                            <td>{{ optional($rc['date'])->format('d-m-Y') ?? '-' }}</td>
                            <td><span class="badge {{ $statusClass($rc['status']) }}">{{ $statusLabel($rc['status']) }}</span></td>
                            <td class="text-end">₹ {{ number_format($rc['total'], 0) }}</td>
                            <td class="text-end text-success">₹ {{ number_format($rc['paid'], 0) }}</td>
                            <td class="text-end {{ ($rc['total'] - $rc['paid']) > 0 ? 'text-danger fw-bold' : 'text-success' }}">₹ {{ number_format($rc['total'] - $rc['paid'], 0) }}</td>
                            <td class="text-end text-nowrap no-print">
                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="{{ $rc['edit'] }}" data-perm="sanghs.edit">
                                    <i class="fa fa-pencil"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="{{ $rc['modal'] }}">
                                    <i class="fa fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="d-none" data-empty-row>
                        <td colspan="8" class="text-center text-muted py-3">No receipts with this status.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ===================== EDIT RECEIPT POP-UPS (opened from the Receipts table) ===================== --}}
    @if(!empty($newRegisterReceipt))
        <div class="modal fade" id="editReceiptNewRegister" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('sanghs.registration_receipt.update', $sangh) }}" method="POST" class="receipt-edit-form">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fa fa-pencil"></i> Registered Receipt — @fy($newRegisterReceipt->receipt_year ?? $registrationYear)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Status</label>
                                    @if($regIsRegistered)
                                        {{-- Registered once — status locked; only payment details can be corrected. --}}
                                        <input type="hidden" name="status" value="sangh_registered">
                                        <input type="text" class="form-control bg-light" value="Sangh Registered" disabled>
                                        <small class="text-muted"><i class="fa fa-lock"></i> Locked — registered on {{ optional($newRegisterReceipt->registered_at)->format('d-m-Y') }}. Registration happens only once.</small>
                                    @else
                                        <select name="status" class="form-select">
                                            <option value="unpaid" @selected($newRegisterReceipt->status === 'unpaid' || ! $newRegisterReceipt->status)>Unpaid</option>
                                            <option value="information_approved" @selected($newRegisterReceipt->status === 'information_approved')>Information approved</option>
                                            <option value="sangh_registered">Sangh Registered</option>
                                        </select>
                                        <small class="text-muted">Sangh Registered is done once: it records today as संघ नोंदणी दिनांक, gives the Sangh its number and then locks. After that only renewals are created.</small>
                                    @endif
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">फेस्कॉम पावती क्र.</label>
                                    <input type="text" class="form-control bg-light" value="{{ $newRegisterReceipt->feskcom_receipt_no ?: 'Auto' }}" disabled>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">पावती दिनांक</label>
                                    <input type="date" name="feskcom_receipt_date" class="form-control" value="{{ optional($newRegisterReceipt->feskcom_receipt_date)->format('Y-m-d') }}">
                                </div>

                                <div class="col-4"><label class="form-label small mb-1">पुरुष</label><input type="number" class="form-control bg-light" value="{{ $registeredMale }}" disabled></div>
                                <div class="col-4"><label class="form-label small mb-1">महिला</label><input type="number" class="form-control bg-light" value="{{ $registeredFemale }}" disabled></div>
                                <div class="col-4"><label class="form-label small mb-1">एकूण</label><input type="number" class="form-control bg-light" value="{{ $registeredTotal }}" disabled></div>

                                <div class="col-md-3 col-6"><label class="form-label small mb-1">प्रवेश शुल्क</label><input type="number" class="form-control bg-light" data-fee value="{{ (int) $newRegisterReceipt->admission_fee }}" disabled></div>
                                <div class="col-md-3 col-6"><label class="form-label small mb-1">वार्षिक शुल्क</label><input type="number" class="form-control bg-light" data-fee value="{{ (int) $newRegisterReceipt->annual_fee }}" disabled></div>
                                <div class="col-md-3 col-6"><label class="form-label small mb-1">विकास निधी शुल्क</label><input type="number" class="form-control bg-light" data-fee value="{{ (int) $newRegisterReceipt->development_fee }}" disabled></div>
                                <div class="col-md-3 col-6"><label class="form-label small mb-1">एकूण रक्कम</label><input type="number" class="form-control bg-light fw-bold" data-total disabled></div>

                                <div class="col-md-4"><label class="form-label small mb-1">पावती रक्कम (भरलेली)</label><input type="text" inputmode="numeric" data-int name="paid_amount" class="form-control" data-paid value="{{ $newRegisterReceipt->paid_amount }}"></div>
                                <div class="col-md-4"><label class="form-label small mb-1">बाकी रक्कम</label><input type="number" class="form-control fw-bold" data-balance disabled></div>
                                <div class="col-md-4"></div>

                                <div class="col-md-4"><label class="form-label small mb-1">बँकेचे नाव</label><input type="text" name="bank_name" class="form-control" value="{{ $newRegisterReceipt->bank_name }}"></div>
                                <div class="col-md-4"><label class="form-label small mb-1">चेक क्रमांक / Transaction क्रमांक</label><input type="text" name="cheque_no" class="form-control" value="{{ $newRegisterReceipt->cheque_no }}"></div>
                                <div class="col-md-4"><label class="form-label small mb-1">चेक दिनांक</label><input type="date" name="cheque_date" class="form-control" value="{{ optional($newRegisterReceipt->cheque_date)->format('Y-m-d') }}"></div>
                            </div>
                            <small class="text-muted d-block mt-3">Fees follow Settings → Sangh Fees and the Sangh's member count; they are not entered by hand.</small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @foreach($renewals as $renewal)
        <div class="modal fade" id="editRenewal{{ $renewal->renewal_year }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('sanghs.renewals.update', [$sangh, $renewal->renewal_year]) }}" method="POST" class="receipt-edit-form" data-renewal>
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fa fa-pencil"></i> {{ $renewal->is_backdated ? 'Backdated' : 'Renewal' }} Receipt — @fy($renewal->renewal_year)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="unpaid" @selected($renewal->status !== 'information_approved')>Unpaid</option>
                                        <option value="information_approved" @selected($renewal->status === 'information_approved')>Information approved</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">फेस्कॉम पावती क्र.</label>
                                    <input type="text" class="form-control bg-light" value="{{ $renewal->feskcom_receipt_no ?: 'Auto' }}" disabled>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">पावती दिनांक</label>
                                    <input type="date" name="feskcom_receipt_date" class="form-control" value="{{ optional($renewal->feskcom_receipt_date)->format('Y-m-d') }}">
                                </div>

                                <div class="col-4"><label class="form-label small mb-1">पुरुष</label><input type="text" inputmode="numeric" data-int name="male_members" class="form-control" data-male value="{{ $renewal->male_members }}"></div>
                                <div class="col-4"><label class="form-label small mb-1">महिला</label><input type="text" inputmode="numeric" data-int name="female_members" class="form-control" data-female value="{{ $renewal->female_members }}"></div>
                                <div class="col-4"><label class="form-label small mb-1">एकूण</label><input type="number" class="form-control bg-light" data-members value="{{ $renewal->total_members }}" disabled></div>

                                <div class="col-md-3 col-6"><label class="form-label small mb-1">वार्षिक शुल्क</label><input type="number" class="form-control bg-light" data-annual value="{{ (int) $renewal->annual_fee }}" disabled></div>
                                <div class="col-md-3 col-6"><label class="form-label small mb-1">विकास निधी शुल्क</label><input type="number" class="form-control bg-light" data-development value="{{ (int) $renewal->development_fee }}" disabled></div>
                                <div class="col-md-3 col-6"><label class="form-label small mb-1">दंड शुल्क</label><input type="text" inputmode="numeric" data-int name="penalty_fee" class="form-control" data-penalty value="{{ $renewal->penalty_fee }}"></div>
                                <div class="col-md-3 col-6"><label class="form-label small mb-1">एकूण रक्कम</label><input type="number" class="form-control bg-light fw-bold" data-total disabled></div>

                                <div class="col-md-4"><label class="form-label small mb-1">पावती रक्कम (भरलेली)</label><input type="text" inputmode="numeric" data-int name="paid_amount" class="form-control" data-paid value="{{ $renewal->paid_amount }}"></div>
                                <div class="col-md-4"><label class="form-label small mb-1">बाकी रक्कम</label><input type="number" class="form-control fw-bold" data-balance disabled></div>
                                <div class="col-md-4"></div>

                                <div class="col-md-4"><label class="form-label small mb-1">बँकेचे नाव</label><input type="text" name="bank_name" class="form-control" value="{{ $renewal->bank_name }}"></div>
                                <div class="col-md-4"><label class="form-label small mb-1">चेक क्रमांक / Transaction क्रमांक</label><input type="text" name="cheque_no" class="form-control" value="{{ $renewal->cheque_no }}"></div>
                                <div class="col-md-4"><label class="form-label small mb-1">चेक दिनांक</label><input type="date" name="cheque_date" class="form-control" value="{{ optional($renewal->cheque_date)->format('Y-m-d') }}"></div>
                            </div>
                            <small class="text-muted d-block mt-3">वार्षिक / विकास निधी शुल्क follow Settings → Sangh Fees for the member count; they are not entered by hand.</small>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="submit" class="btn btn-outline-danger btn-sm" form="deleteRenewal{{ $renewal->renewal_year }}" data-perm="sanghs.delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                            <div>
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success btn-sm"><i class="fa fa-save"></i> Save</button>
                            </div>
                        </div>
                    </form>
                    <form id="deleteRenewal{{ $renewal->renewal_year }}" action="{{ route('sanghs.renewals.destroy', [$sangh, $renewal->renewal_year]) }}" method="POST" class="d-none"
                          onsubmit="return confirm('Delete the @fy($renewal->renewal_year) receipt?')">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    @endforeach

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
                                    <p><strong>Sangh Number:</strong> {{ $display($sangNumber) }}</p>
                                    <p><strong>जिल्हा:</strong> {{ $display($sangh->district) }}</p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p><strong>वर्ष:</strong> @fy($newRegisterReceipt->receipt_year ?? $registrationYear)</p>
                                    <p><strong>पावती दिनांक:</strong> {{ optional($newRegisterReceipt->feskcom_receipt_date)->format('d-m-Y') ?? '-' }}</p>
                                    <p><strong>संघ नोंदणी दिनांक:</strong> {{ optional($newRegisterReceipt->registered_at)->format('d-m-Y') ?? '-' }}</p>
                                    <p>
                                        <strong>पावती स्थिति:</strong>
                                        <span class="badge {{ match($newRegisterReceipt->status) { 'sangh_registered' => 'bg-primary text-white', 'information_approved' => 'bg-success text-white', default => 'bg-warning text-dark' } }}">
                                            {{ match($newRegisterReceipt->status) { 'sangh_registered' => 'Sangh Registered', 'information_approved' => 'Information approved', default => 'Unpaid' } }}
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
                                            <td class="text-end">₹ {{ number_format($newRegisterReceipt->admission_fee ?? 0, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td>वार्षिक शुल्क</td>
                                            <td class="text-end">₹ {{ number_format($newRegisterReceipt->annual_fee ?? 0, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td>विकास निधी शुल्क</td>
                                            <td class="text-end">₹ {{ number_format($newRegisterReceipt->development_fee ?? 0, 0) }}</td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td><strong>एकूण रक्कम</strong></td>
                                            <td class="text-end"><strong>₹ {{ number_format(($newRegisterReceipt->annual_fee ?? 0) + ($newRegisterReceipt->admission_fee ?? 0) + ($newRegisterReceipt->development_fee ?? 0), 0) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong class="text-success">भरलेली रक्कम</strong></td>
                                            <td class="text-end"><strong class="text-success">₹ {{ number_format($newRegisterReceipt->paid_amount ?? 0, 0) }}</strong></td>
                                        </tr>
                                        @php
                                            $newRegisterModalBalance = (($newRegisterReceipt->annual_fee ?? 0) + ($newRegisterReceipt->admission_fee ?? 0) + ($newRegisterReceipt->development_fee ?? 0)) - ($newRegisterReceipt->paid_amount ?? 0);
                                        @endphp
                                        <tr>
                                            <td><strong class="{{ $newRegisterModalBalance > 0 ? 'text-danger' : 'text-success' }}">बाकी रक्कम</strong></td>
                                            <td class="text-end"><strong class="{{ $newRegisterModalBalance > 0 ? 'text-danger' : 'text-success' }}">₹ {{ number_format($newRegisterModalBalance, 0) }}</strong></td>
                                        </tr>
                                        @if($newRegisterReceipt->bank_name || $newRegisterReceipt->cheque_no)
                                        <tr>
                                            <td>बँक तपशील</td>
                                            <td class="text-end">
                                                {{ $newRegisterReceipt->bank_name }} 
                                                {{ $newRegisterReceipt->cheque_no ? '(चेक / Transaction क्र.: ' . $newRegisterReceipt->cheque_no . ')' : '' }}
                                                {{ $newRegisterReceipt->cheque_date ? ' - ' . \Carbon\Carbon::parse($newRegisterReceipt->cheque_date)->format('d/m/Y') : '' }}
                                            </td>
                                        </tr>
                                        @endif
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
                                    <p><strong>Sangh Number:</strong> {{ $display($sangNumber) }}</p>
                                    <p><strong>जिल्हा:</strong> {{ $display($sangh->district) }}</p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p><strong>वर्ष:</strong> @fy($renewal->renewal_year)</p>
                                    <p><strong>पावती दिनांक:</strong> {{ optional($renewal->feskcom_receipt_date)->format('d-m-Y') ?? '-' }}</p>
                                    <p><strong>संघ नोंदणी दिनांक:</strong> {{ optional($renewal->registered_at)->format('d-m-Y') ?? '-' }}</p>
                                    <p>
                                        <strong>पावती स्थिति:</strong>
                                        <span class="badge {{ match($renewal->status) { 'sangh_registered' => 'bg-primary text-white', 'information_approved' => 'bg-success text-white', default => 'bg-warning text-dark' } }}">
                                            {{ match($renewal->status) { 'sangh_registered' => 'Sangh Registered', 'information_approved' => 'Information approved', default => 'Unpaid' } }}
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
                                            <td class="text-end">₹ {{ number_format($renewal->annual_fee ?? 0, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td>विकास निधी शुल्क</td>
                                            <td class="text-end">₹ {{ number_format($renewal->development_fee ?? 0, 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td>दंड शुल्क</td>
                                            <td class="text-end">₹ {{ number_format($renewal->penalty_fee ?? 0, 0) }}</td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td><strong>एकूण रक्कम</strong></td>
                                            <td class="text-end"><strong>₹ {{ number_format(($renewal->annual_fee ?? 0) + ($renewal->development_fee ?? 0) + ($renewal->penalty_fee ?? 0), 0) }}</strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong class="text-success">भरलेली रक्कम</strong></td>
                                            <td class="text-end"><strong class="text-success">₹ {{ number_format($renewal->paid_amount ?? 0, 0) }}</strong></td>
                                        </tr>
                                        @php
                                            $renewalModalBalance = (($renewal->annual_fee ?? 0) + ($renewal->development_fee ?? 0) + ($renewal->penalty_fee ?? 0)) - ($renewal->paid_amount ?? 0);
                                        @endphp
                                        <tr>
                                            <td><strong class="{{ $renewalModalBalance > 0 ? 'text-danger' : 'text-success' }}">बाकी रक्कम</strong></td>
                                            <td class="text-end"><strong class="{{ $renewalModalBalance > 0 ? 'text-danger' : 'text-success' }}">₹ {{ number_format($renewalModalBalance, 0) }}</strong></td>
                                        </tr>
                                        @if($renewal->bank_name || $renewal->cheque_no)
                                        <tr>
                                            <td>बँक तपशील</td>
                                            <td class="text-end">
                                                {{ $renewal->bank_name }} 
                                                {{ $renewal->cheque_no ? '(चेक / Transaction क्र.: ' . $renewal->cheque_no . ')' : '' }}
                                                {{ $renewal->cheque_date ? ' - ' . \Carbon\Carbon::parse($renewal->cheque_date)->format('d/m/Y') : '' }}
                                            </td>
                                        </tr>
                                        @endif
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

    @include('sanghs._detail_styles')
</div>

@push('scripts')
    <script>
        (function () {
            // Edit Receipt pop-ups: live totals. वार्षिक / विकास निधी शुल्क follow the
            // admin-configured sangh fee slabs — auto-calculated, never entered by hand
            // (the server recalculates them on save as well).
            const feeSlabs = @json($feeSlabsForRenewal);
            const developmentFeeRate = @json($developmentFeeRateForRenewal);
            const num = (el) => (el ? parseInt(el.value || '0', 10) || 0 : 0);

            function annualFeeForMembers(n) {
                const slab = feeSlabs.find((s) => n >= s.min_members && (s.max_members === null || n <= s.max_members));
                return slab ? parseFloat(slab.annual_fee) : 0;
            }

            function sync(form) {
                let total;
                if (form.hasAttribute('data-renewal')) {
                    const male = form.querySelector('[data-male]');
                    const female = form.querySelector('[data-female]');
                    const members = num(male) + num(female);
                    form.querySelector('[data-members]').value = (male.value === '' && female.value === '') ? '' : members;
                    const annual = annualFeeForMembers(members);
                    const development = Math.round(members * developmentFeeRate);
                    form.querySelector('[data-annual]').value = annual;
                    form.querySelector('[data-development]').value = development;
                    total = annual + development + num(form.querySelector('[data-penalty]'));
                } else {
                    total = [...form.querySelectorAll('[data-fee]')].reduce((sum, el) => sum + num(el), 0);
                }
                form.querySelector('[data-total]').value = total;
                const balance = total - num(form.querySelector('[data-paid]'));
                const balanceEl = form.querySelector('[data-balance]');
                balanceEl.value = balance;
                balanceEl.classList.toggle('text-danger', balance > 0);
                balanceEl.classList.toggle('text-success', balance <= 0);
            }

            document.querySelectorAll('form.receipt-edit-form').forEach(function (form) {
                sync(form);
                form.addEventListener('input', () => sync(form));
            });
        })();

        // Status tabs: All / Unpaid / Information approved / Sangh Registered.
        (function () {
            const tabs = document.querySelectorAll('[data-status-tab]');
            const rows = document.querySelectorAll('#receiptsTable tbody tr[data-status]');
            const empty = document.querySelector('#receiptsTable [data-empty-row]');
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    const status = tab.getAttribute('data-status-tab');
                    tabs.forEach((t) => t.classList.toggle('active', t === tab));
                    let shown = 0;
                    rows.forEach(function (row) {
                        const show = status === 'all' || row.getAttribute('data-status') === status;
                        row.classList.toggle('d-none', !show);
                        if (show) shown++;
                    });
                    if (empty) empty.classList.toggle('d-none', shown > 0);
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
