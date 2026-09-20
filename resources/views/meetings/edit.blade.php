@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
  <h3>Edit Meeting</h3>
<hr>
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('meetings.update', $meeting) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" value="{{ old('title', $meeting->title) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Start At</label>
      <input type="datetime-local" name="start_at" class="form-control"
             value="{{ old('start_at', $meeting->start_at ? $meeting->start_at->format('Y-m-d\TH:i') : '') }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Group (optional)</label>
      <select name="group_id" class="form-control">
        <option value="">— No group —</option>
        @foreach($groups as $g)
          <option value="{{ $g->id }}" {{ old('group_id', $meeting->group_id) == $g->id ? 'selected' : '' }}>
            {{ $g->name }}
          </option>
        @endforeach
      </select>
    </div>

    @if(auth()->check() && auth()->user()->hasRole('superadmin'))
      <div class="mb-3">
        <label class="form-label">Assign To</label>
        <select name="assigned_to" class="form-control">
          <option value="">-- No specific user (Unassigned) --</option>
          @foreach($allUsers as $u)
            <option value="{{ $u->id }}" {{ old('assigned_to', $meeting->assigned_to) == $u->id ? 'selected' : '' }}>
              {{ $u->name }}
            </option>
          @endforeach
        </select>
      </div>
    @endif

    <button class="btn btn-primary">Update</button>
    <a href="{{ route('meetings.show', $meeting) }}" class="btn btn-secondary">Back</a>
  </form>
</div>
@endsection
