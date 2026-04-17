@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Sangh List</h2>

            <div class="btn-group">
                <a href="{{ route('sanghs.create') }}" class="btn btn-sm btn-primary">Create</a>
                <a href="{{ route('sanghs.export') }}" class="btn btn-sm btn-secondary">Export</a>
                <a href="{{ route('sanghs.template') }}" class="btn btn-sm btn-info"><i class="ti-download"></i> Sample</a>

                <!-- Import CSV (Modal Trigger) -->
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                    Import
                </button>
            </div>
        </div>
        <hr>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('import_errors'))
            <div class="alert alert-warning">
                <strong>Import warnings:</strong>
                <ul>
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

       


        <!-- Import CSV Modal -->
        <div class="modal fade" id="importCsvModal" tabindex="-1">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Import CSV</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <form action="{{ route('sanghs.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="modal-body">
                            <div class="form-group">
                                <label>Select CSV File</label>
                                <input type="file" name="csv_file" class="form-control" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-success btn-sm">Import</button>
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                                Cancel
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Sr No</th>
                    <th>Name of Sangh</th>
                    <th>District</th>
                    <th>City</th>
                    <th>Male</th>
                    <th>Female</th>
                    <th>Total Members</th>
                    <th>Created Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sanghs as $s)
                    <tr>
                        <td>{{ $s->sangh_sr_no }}</td>
                        <td>{{ $s->name_of_sangh }}</td>
                        <td>{{ $s->district }}</td>
                        <td>{{ $s->city }}</td>
                        <td>{{ $s->male }}</td>
                        <td>{{ $s->female }}</td>
                        <td>{{ $s->total_members }}</td>
                        <td>{{ $s->created_date ? $s->created_date->format('Y-m-d') : $s->created_at->format('Y-m-d') }}</td>
                        <td>
                            <a href="{{ route('sanghs.show', $s) }}" class="btn btn-sm btn-info">View</a>

                            <a href="{{ route('sanghs.edit', $s) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('sanghs.destroy', $s) }}" method="POST" style="display:inline-block"
                                onsubmit="return confirm('Delete?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $sanghs->links() }}
    </div>
@endsection