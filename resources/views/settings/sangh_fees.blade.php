@extends('layouts.app')

@section('content')

<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Sangh Fee Slabs</h2>
        <a href="{{ route('settings.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-arrow-left"></i> Back to Settings
        </a>
    </div>
    <p class="text-muted">These amounts are used to auto-calculate the New register Sangh Receipt fees. Admin can update them anytime as per requirement.</p>
    <hr>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Standard flat fees --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light border-bottom">
            <h5 class="mb-0">Standard Fees</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.sangh_fees.update') }}" class="row g-3 align-items-end">
                @csrf
                @method('PUT')
                <div class="col-md-4">
                    <label class="form-label">प्रवेश शुल्क (Registration Fee)</label>
                    <input type="number" name="admission_fee" min="0" step="0.01" class="form-control"
                           value="{{ old('admission_fee', $admissionFee) }}" required>
                    <small class="text-muted">Flat amount, same for every sangh.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">विकास निधी शुल्क दर (Development Fee Rate)</label>
                    <input type="number" name="development_fee_rate" min="0" step="0.01" class="form-control"
                           value="{{ old('development_fee_rate', $developmentFeeRate) }}" required>
                    <small class="text-muted">₹ per member, per year (e.g. ₹1/- × total members).</small>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-success">
                        <i class="fa fa-save"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Annual fee slabs, based on member count --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-light border-bottom">
            <h5 class="mb-0">वार्षिक शुल्क (Annual Fee) Slabs — by Member Count</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Min Members</th>
                        <th>Max Members</th>
                        <th>वार्षिक शुल्क</th>
                        <th style="width:100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slabs as $slab)
                        <tr>
                            <td>{{ $slab->min_members }}</td>
                            <td>{{ $slab->max_members ?? 'No limit' }}</td>
                            <td>₹ {{ number_format($slab->annual_fee, 2) }}</td>
                            <td>
                                <form method="POST" action="{{ route('settings.sangh_fees.slabs.destroy', $slab) }}"
                                      onsubmit="return confirm('Remove this fee slab?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-muted text-center py-3">No fee slabs added yet.</td>
                        </tr>
                    @endforelse
                    <tr>
                        <form method="POST" action="{{ route('settings.sangh_fees.slabs.store') }}" class="d-none" id="addSlabForm">
                            @csrf
                        </form>
                        <td><input type="number" name="min_members" min="0" class="form-control form-control-sm" form="addSlabForm" required></td>
                        <td><input type="number" name="max_members" min="0" class="form-control form-control-sm" form="addSlabForm" placeholder="blank = no limit"></td>
                        <td><input type="number" name="annual_fee" min="0" step="0.01" class="form-control form-control-sm" form="addSlabForm" required></td>
                        <td>
                            <button type="submit" class="btn btn-sm btn-primary" form="addSlabForm">
                                <i class="fa fa-plus"></i> Add
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
