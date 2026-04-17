@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Create Chat</h2>
    <form method="POST" action="{{ route('chats.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <button class="btn btn-success">Save</button>
        <a href="{{ route('chats.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection