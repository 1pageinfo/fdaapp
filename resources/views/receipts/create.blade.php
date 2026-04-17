@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-2 mb-sm-0 d-flex align-items-center">Add New Receipt</h2>
    <hr>

    <form action="{{ route('receipts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label>Receipt Subject</label>
            <input type="text" name="subject" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Total Amount</label>
            <input type="number" name="amount" class="form-control" step="0.01" required>
        </div>

        <div class="mb-3">
            <label>Photo/Attachment</label>
            <input type="file" name="file" class="form-control" accept="image/*,.pdf">
        </div>

        <button class="btn btn-success">Save Receipt</button>
    </form>
</div>
@endsection
