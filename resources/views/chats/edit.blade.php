@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2>Edit Chat</h2>
    <form method="POST" action="{{ route('chats.update',$item) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" value="{{ $item->name ?? '' }}" class="form-control" required>
        </div>
        <button class="btn btn-success">Update</button>
        <a href="{{ route('chats.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection