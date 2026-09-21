@extends('layouts.app')

@section('content')
@php($sangh = $sangh ?? new \App\Models\Sangh())
<div class="container-fluid mt-4">
    <h2>Edit Sangh{{ $sangh->sangh_sr_no ? ' — #' . $sangh->sangh_sr_no : ' (not yet registered)' }}</h2>
<hr>
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger">
        <ul>
           @foreach($errors->all() as $err)
             <li>{{ $err }}</li>
           @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('sanghs.update', $sangh) }}" method="POST">
        @csrf
        @method('PUT')
        @include('sanghs._form', ['sangh' => $sangh])

        <div class="mt-3">
            <button class="btn btn-primary">Update Sangh</button>
            <a href="{{ route('sanghs.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
