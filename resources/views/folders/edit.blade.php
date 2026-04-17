@extends('layouts.app')

@section('content')
<div class="container mt-4">
  <h3>Edit Folder</h3>
<hr>
  <form action="{{ route('folders.update', $folder->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label>Folder Name</label>
      <input type="text" name="name" value="{{ old('name', $folder->name) }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label>Year</label>
      <input type="number" name="year" value="{{ old('year', $folder->year) }}" class="form-control">
    </div>

    <div class="mb-3">
      <label>Owner Group</label>
      <select name="owner_group_id" class="form-control">
        <option value="">-- None --</option>
        @foreach($groups as $g)
          <option value="{{ $g->id }}" {{ $folder->owner_group_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
        @endforeach
      </select>
    </div>

    <button class="btn btn-success" type="submit">Save</button>
  </form>
</div>
@endsection
