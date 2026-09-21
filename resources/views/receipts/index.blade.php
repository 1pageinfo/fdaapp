@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <h2 class="d-flex justify-content-between align-items-center mb-3">
            <span>Receipts</span>
            <!-- Actions -->
            <div class="mb-3">
                <a href="{{ route('receipts.create') }}" class="btn btn-sm btn-success">➕ Add Receipt</a>
                <a href="{{ route('receipts.export', request()->all()) }}" class="btn btn-sm btn-warning">⬇️ Export CSV</a>
            </div>
        </h2>
        <hr>



        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Filter Form -->
        <form method="GET" action="{{ route('receipts.index') }}" class="row g-2 mb-3">
            <div class="col-md-4">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-4">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-primary w-100">Apply Filter</button>
            </div>
        </form>



        <!-- Receipts Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Amount</th>
                    <th>User</th>
                    <th>File</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipts as $receipt)
                    <tr>
                        <td>{{ $receipt->id }}</td>
                        <td>{{ $receipt->subject }}</td>
                        <td>{{ $receipt->amount }}</td>
                        <td>{{ $receipt->user?->name }}</td>
                        <td>
                            @if($receipt->file_path)
                                <a href="{{ asset('storage/' . $receipt->file_path) }}" target="_blank">View</a>
                            @endif
                        </td>
                        <td>{{ $receipt->created_at->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $receipts->links() }}
    </div>
@endsection