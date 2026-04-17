@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3 d-flex justify-content-between align-items-center">
        <span>Links</span>
        <a href="{{ route('links.create') }}" class="btn btn-primary">Add Link</a>
    </h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Social --}}
    <div class="card mb-3">
        <div class="card-header">Social Media</div>
        <div class="card-body">
            @if($links->get('social') && $links->get('social')->count())
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Platform</th>
                            <th>URL</th>
                            <th>Active</th>
                            <th>Order</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($links->get('social') as $link)
                            <tr>
                                <td>{{ $link->title }}</td>
                                <td>{{ $link->platform }}</td>
                                <td><a href="{{ $link->url }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit($link->url, 60) }}</a></td>
                                <td>{{ $link->is_active ? 'Yes' : 'No' }}</td>
                                <td>{{ $link->sort_order }}</td>
                                <td class="text-end">
                                    <a href="{{ route('links.edit', $link) }}" class="btn btn-sm btn-outline-secondary">Edit</a>

                                    <form action="{{ route('links.destroy', $link) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this link?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="mb-0">No social links yet.</p>
            @endif
        </div>
    </div>

    {{-- Other --}}
    <div class="card mb-3">
        <div class="card-header">Other Links</div>
        <div class="card-body">
            @if($links->get('other') && $links->get('other')->count())
                <table class="table table-sm">
                    <thead>
                        <tr><th>Title</th><th>Platform</th><th>URL</th><th>Active</th><th>Order</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($links->get('other') as $link)
                            <tr>
                                <td>{{ $link->title }}</td>
                                <td>{{ $link->platform }}</td>
                                <td><a href="{{ $link->url }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit($link->url, 60) }}</a></td>
                                <td>{{ $link->is_active ? 'Yes' : 'No' }}</td>
                                <td>{{ $link->sort_order }}</td>
                                <td class="text-end">
                                    <a href="{{ route('links.edit', $link) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('links.destroy', $link) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this link?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="mb-0">No other links yet.</p>
            @endif
        </div>
    </div>

    {{-- Custom --}}
    <div class="card mb-3">
        <div class="card-header">Custom / Added Links</div>
        <div class="card-body">
            @if($links->get('custom') && $links->get('custom')->count())
                <table class="table table-sm">
                    <thead>
                        <tr><th>Title</th><th>Platform</th><th>URL</th><th>Active</th><th>Order</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($links->get('custom') as $link)
                            <tr>
                                <td>{{ $link->title }}</td>
                                <td>{{ $link->platform }}</td>
                                <td><a href="{{ $link->url }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit($link->url, 60) }}</a></td>
                                <td>{{ $link->is_active ? 'Yes' : 'No' }}</td>
                                <td>{{ $link->sort_order }}</td>
                                <td class="text-end">
                                    <a href="{{ route('links.edit', $link) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('links.destroy', $link) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this link?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="mb-0">No custom links yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
