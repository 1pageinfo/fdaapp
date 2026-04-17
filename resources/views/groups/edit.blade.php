@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Edit Group</h3>

  <form action="{{ route('groups.update', $group) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">Name</label>
      <input name="name" class="form-control" value="{{ old('name', $group->name) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description" rows="4" class="form-control">{{ old('description', $group->description) }}</textarea>
    </div>

    <button class="btn btn-primary">Save</button>
    <a href="{{ route('groups.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
