@extends('layouts.app')

@section('content')
    <div class="container mt-4">


        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3> Meeting Details
            </h3>
        
            <div>
                <a href="{{ route('meetings.edit', $meeting) }}" class="btn btn-warning">
                    ✏️ Edit
                </a>
                <form action="{{ route('meetings.destroy', $meeting) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Delete this meeting/event?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">🗑️ Delete</button>
                </form>
            </div>
        </div>
    <hr>
        <div class="card shadow-sm">
            <div class="card-body">
                <p><strong>Title:</strong> {{ $meeting->title }}</p>
                <p><strong>Date & Time:</strong> {{ $meeting->start_at->format('Y-m-d H:i') }}</p>
                <p><strong>Group:</strong> {{ $meeting->group?->name ?? 'N/A' }}</p>
            </div>
        </div>

        <a href="{{ route('meetings.index') }}" class="btn btn-link mt-3">⬅ Back to Calendar</a>
    </div>
@endsection