@extends('layouts.app')
@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h2 class="d-flex justify-content-between align-items-center mb-3">Files</h2>
            <a href="{{ route('files.create') }}" class="btn btn-primary">New File</a>
        </div>
    </div>
    <hr>
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div> @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($files as $item)
                <tr>
                    <td>{{ $item->name ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('files.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('files.destroy', $item) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $files->links() }}
    </div>
@endsection