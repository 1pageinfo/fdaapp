@extends('layouts.app')

@section('content')
<div class="container-fluid mt-3">
    <h4>Edit File</h4>

    <form action="{{ route('files.update', $file->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="{{ old('name', $file->name) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Move to Folder</label>
            <select name="folder_id" class="form-control">
                <option value="">-- none --</option>
                @foreach($folders as $f)
                    <option value="{{ $f->id }}" {{ $file->folder_id == $f->id ? 'selected' : '' }}>
                        {{ $f->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Replace file (optional)</label>
            <input type="file" name="file" class="form-control" accept="{{ \App\Support\Uploads::accept(\App\Support\Uploads::FILE_TYPES) }}" data-max-mb="{{ \App\Support\Uploads::mb(\App\Support\Uploads::MAX_FILE_KB) }}">
            <small class="text-muted d-block">Max {{ \App\Support\Uploads::mb(\App\Support\Uploads::MAX_FILE_KB) }} MB · PDF, Word, Excel, PowerPoint, CSV, TXT, JPG, PNG, GIF, MP4, ZIP</small>
            <small class="text-muted">Current: <a href="{{ $file->disk_path ? \App\Support\Media::url($file->disk_path) : '#' }}" target="_blank">{{ $file->name }}</a></small>
        </div>

        <button class="btn btn-primary">Save</button>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
