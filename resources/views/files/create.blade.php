@extends('layouts.app')
@section('content')
<div class="container mt-4">
    <h2 class="d-flex justify-content-between align-items-center mb-3">Create File</h2>
    <hr>
    <form method="POST" action="{{ route('files.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <button class="btn btn-success">Save</button>
        <a href="{{ route('files.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection