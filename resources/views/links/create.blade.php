@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Create Link</h2>

    <form action="{{ route('links.store') }}" method="POST">
        @csrf

        @include('links._form')

        <div>
            <a href="{{ route('links.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary">Create</button>
        </div>
    </form>
</div>
@endsection
