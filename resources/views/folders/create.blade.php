@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <h2 class="mb-2 mb-sm-0 d-flex align-items-center">{{ isset($parent) ? 'Create Subfolder in '.$parent->name : 'Create Main Folder' }}</h2>

<hr>    
    <form action="{{ route('folders.store') }}" method="POST">
        @csrf
        <input type="hidden" name="parent_id" value="{{ $parent->id ?? '' }}">

        <div class="mb-3">
            <label>Folder Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        @if(!$parent)
        <div class="mb-3">
            <label>Year (for main folders)</label>
            <input type="number" name="year" class="form-control" value="{{ date('Y') }}">
        </div>
        @endif

        <div class="mb-3">
            <label>Owner Group</label>
            <select name="owner_group_id" class="form-control">
                <option value="">None</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </select>
        </div>

        @if(auth()->check() && auth()->user()->hasRole('superadmin'))
            <div class="mb-3">
                <label>Assign To</label>
                <select name="assigned_to" class="form-control">
                    <option value="">-- No specific user (Unassigned) --</option>
                    @foreach($allUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <button class="btn btn-success">Save</button>
    </form>
</div>
@endsection
