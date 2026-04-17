@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Edit Link</h2>

    <form action="{{ route('links.update', $link) }}" method="POST">
        @csrf
        @method('PUT')

        @include('links._form')

        <div>
            <a href="{{ route('links.index') }}" class="btn btn-secondary">Cancel</a>
            <button class="btn btn-primary">Update</button>
        </div>
    </form>
</div>
@endsection
