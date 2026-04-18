@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Create Sangh</h2>
    <hr>
    @if($errors->any())
      <div class="alert alert-danger">
        <ul>
           @foreach($errors->all() as $err)
             <li>{{ $err }}</li>
           @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('sanghs.store') }}" method="POST">
        @csrf
        @php($sangh = null)
        @include('sanghs._form', ['sangh' => $sangh])

        <button class="btn btn-primary">Save Sangh</button>
        <a href="{{ route('sanghs.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
