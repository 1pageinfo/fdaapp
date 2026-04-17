@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h2>Chats</h2>
        <a href="{{ route('chats.create') }}" class="btn btn-primary">New Chat</a>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <table class="table table-bordered">
        <thead><tr><th>Name</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach($chats as $item)
            <tr>
                <td>{{ $item->name ?? 'N/A' }}</td>
                <td>
                    <a href="{{ route('chats.edit',$item) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('chats.destroy',$item) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $chats->links() }}
</div>
@endsection