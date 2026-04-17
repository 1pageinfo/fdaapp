@extends('layouts.app')

@section('content')
<div class="container  mt-4">
  <h3>Create Meeting / Event</h3>
<hr>
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('meetings.store') }}">
    @csrf

    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Start At</label>
      <!-- datetime-local expects value like 2025-11-14T15:30 -->
      <input type="datetime-local" name="start_at" class="form-control"
             value="{{ old('start_at') }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Group (optional)</label>
      <select name="group_id" class="form-control">
        <option value="">— No group —</option>
        @foreach(\App\Models\Group::all() as $g)
          <option value="{{ $g->id }}" {{ old('group_id') == $g->id ? 'selected' : '' }}>
            {{ $g->name }}
          </option>
        @endforeach
      </select>
    </div>

    <button class="btn btn-primary">Create</button>
    <a href="{{ route('meetings.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
