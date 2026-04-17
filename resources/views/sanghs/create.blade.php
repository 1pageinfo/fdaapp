@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Create Sangh</h2>
<hr>
    @if($errors->any())
      <div class="alert alert-danger">
        <ul>
           @foreach($errors->all() as $err)
             <li>{{ $err }}</li>
           @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('sanghs.store') }}" method="POST">
        @csrf

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Name of Sangh</label>
                <input type="text" name="name_of_sangh" class="form-control" value="{{ old('name_of_sangh') }}" required>
            </div>

            <div class="form-group col-md-3">
                <label>District</label>
                <input type="text" name="district" class="form-control" value="{{ old('district') }}">
            </div>

            <div class="form-group col-md-3">
                <label>District No.</label>
                <input type="text" name="district_no" class="form-control" value="{{ old('district_no') }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-3">
                <label>Taluka</label>
                <input type="text" name="taluka" class="form-control" value="{{ old('taluka') }}">
            </div>
            <div class="form-group col-md-3">
                <label>City</label>
                <input type="text" name="city" class="form-control" value="{{ old('city') }}">
            </div>
            <div class="form-group col-md-3">
                <label>Division</label>
                <input type="text" name="division" class="form-control" value="{{ old('division') }}">
            </div>
            <div class="form-group col-md-3">
                <label>Division No.</label>
                <input type="text" name="division_no" class="form-control" value="{{ old('division_no') }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-3">
                <label>Total M/F</label>
                <input type="text" name="total_m_f" class="form-control" value="{{ old('total_m_f') }}">
            </div>

            <div class="form-group col-md-3">
                <label>Date (Regulatory Board / Annual / Special Meeting)</label>
                <input type="date" name="date_meeting" class="form-control" value="{{ old('date_meeting') }}">
            </div>

            <div class="form-group col-md-3">
                <label>Receipt No.</label>
                <input type="text" name="receipt_no" class="form-control" value="{{ old('receipt_no') }}">
            </div>

            <div class="form-group col-md-3">
                <label>Receipt Date</label>
                <input type="date" name="receipt_date" class="form-control" value="{{ old('receipt_date') }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-3">
                <label>Receipt Amount</label>
                <input type="number" step="0.01" name="receipt_amount" class="form-control" value="{{ old('receipt_amount') }}">
            </div>

            <div class="form-group col-md-3">
                <label>Division Membership No.</label>
                <input type="text" name="division_membership_no" class="form-control" value="{{ old('division_membership_no') }}">
            </div>

            <div class="form-group col-md-2">
                <label>Male</label>
                <input type="number" name="male" class="form-control" value="{{ old('male') }}">
            </div>

            <div class="form-group col-md-2">
                <label>Female</label>
                <input type="number" name="female" class="form-control" value="{{ old('female') }}">
            </div>

            <div class="form-group col-md-2">
                <label>Total Members</label>
                <input type="number" name="total_members" class="form-control" value="{{ old('total_members') }}">
            </div>
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="address" class="form-control">{{ old('address') }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label>President</label>
                <input type="text" name="president" class="form-control" value="{{ old('president') }}">
            </div>

            <div class="form-group col-md-4">
                <label>Tel. No.</label>
                <input type="text" name="tel_no" class="form-control" value="{{ old('tel_no') }}">
            </div>

            <div class="form-group col-md-4">
                <label>Alt Tel. No.</label>
                <input type="text" name="alt_tel_no" class="form-control" value="{{ old('alt_tel_no') }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Email ID</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
            </div>

            <div class="form-group col-md-6">
                <label>Secretary</label>
                <input type="text" name="secretary" class="form-control" value="{{ old('secretary') }}">
            </div>
        </div>

        <button class="btn btn-primary">Save Sangh</button>
        <a href="{{ route('sanghs.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
