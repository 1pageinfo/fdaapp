@extends('layouts.app')

@section('content')
    <div class="container mt-4">
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
            <div class="col-md-3">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-3">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-3">
                <label>Group</label>
                <select name="group_id" class="form-control">
                    <option value="">All Groups</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
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
                    <th>Group</th>
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
                        <td>{{ $receipt->group?->name ?? '-' }}</td>
                        <td>
                            @if($receipt->file_path)
                                <a href="{{ asset('storage/app/public/' . $receipt->file_path) }}" target="_blank">View</a>
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