@extends('layouts.app')

@section('content')
<div class="container-fluid">
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

    @if(auth()->check() && auth()->user()->hasRole('superadmin'))
      <div class="mb-3">
        <label class="form-label">Assign To</label>
        <select name="assigned_to" class="form-control">
          <option value="">-- No specific user (Unassigned) --</option>
          @foreach($allUsers as $u)
            <option value="{{ $u->id }}" {{ old('assigned_to', $group->assigned_to) == $u->id ? 'selected' : '' }}>
              {{ $u->name }}
            </option>
          @endforeach
        </select>
      </div>
    @endif

    <button class="btn btn-primary">Save</button>
    <a href="{{ route('groups.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
