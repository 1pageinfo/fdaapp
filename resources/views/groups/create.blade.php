@extends('layouts.app')
@section('content')
<div class="container mt-4">
  <h2 class="mb-2 mb-sm-0 d-flex align-items-center">Create Group</h2>
  <hr>
  <form method="POST" action="{{ route('groups.store') }}">
    @csrf
    <div class="mb-3">
      <label>Name</label>
      <input name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Description</label>
      <textarea name="description" class="form-control"></textarea>
    </div>
    <button class="btn btn-success">Save</button>
  </form>
</div>
@endsection
