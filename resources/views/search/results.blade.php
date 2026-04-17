@extends('layouts.app')

@section('content')
  <div class="container mt-4">
    <h2 class="mb-2 mb-sm-0 d-flex align-items-center">🔎 Search</h2>
    <hr>

    <form class="row g-2 mb-3" method="GET" action="{{ route('search.index') }}">
      <div class="col-md-10">
        <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="Type to search…">
      </div>
      <div class="col-md-2 d-grid">
        <button class="btn btn-primary">Search</button>
      </div>
    </form>

    @if($q === '')
      <div class="alert alert-info">Type something above to search receipts, sanghs, files, meetings, groups.</div>
    @else
      <p class="text-muted">Results for: <strong>{{ $q }}</strong></p>

      {{-- Show only non-empty sections with minimal UI headers like: — Sanghs --}}
      @if($sanghs->count() > 0)
        <h6 class="mt-3 mb-1">— Sanghs</h6>
        <ul class="list-unstyled mb-3">
          @foreach($sanghs as $s)
            @php
              $sanghName = $s->name ?? $s->sangh_name ?? $s->title ?? ($s->id ? 'Sangh #' . $s->id : '—');
              $district = $s->district ?? $s->area ?? $s->location ?? null;
              $pradesh = $s->pradeshik_vibhag_name ?? $s->division ?? $s->state ?? null;
              $meta = collect([$district, $pradesh])->filter()->implode(', ');
              $created = isset($s->created_at) ? \Carbon\Carbon::parse($s->created_at)->format('Y-m-d') : null;
            @endphp

            <li class="py-2 border-bottom d-flex justify-content-between align-items-center">
              <div>
                <a href="{{ route('sanghs.show', $s->id) }}" class="fw-semibold text-decoration-none">
                  {{ $sanghName }}
                </a>
                @if($meta)
                  <div class="small text-muted">{{ $meta }}</div>
                @endif
              </div>

              @if($created)
                <small class="text-muted">{{ $created }}</small>
              @endif
            </li>
          @endforeach
        </ul>
      @endif

      @if($files->count() > 0)
        <h6 class="mt-3 mb-1">— Files</h6>
        <ul class="list-unstyled mb-3">
          @foreach($files as $f)
            @php
              $fileName = $f->name ?? $f->title ?? ('File #' . $f->id);
              // If path looks like a full URL, use it; otherwise, try Storage::url on disk_path or path
              $fileUrl = $f->path && (Str::startsWith($f->path, ['http://','https://'])) ? $f->path
                        : ($f->path ? \Illuminate\Support\Facades\Storage::url($f->path) : ($f->disk_path ? \Illuminate\Support\Facades\Storage::url($f->disk_path) : '#'));
              $createdFile = isset($f->created_at) ? \Carbon\Carbon::parse($f->created_at)->format('Y-m-d') : null;
            @endphp

            <li class="py-2 border-bottom d-flex justify-content-between align-items-center">
              <div>
                <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer" class="text-decoration-none">
                  {{ $fileName }}
                </a>
                <div class="small text-muted">{{ $fileUrl !== '#' ? $fileUrl : '' }}</div>
              </div>

              @if($createdFile)
                <small class="text-muted">{{ $createdFile }}</small>
              @endif
            </li>
          @endforeach
        </ul>
      @endif

      @if($meetings->count() > 0)
        <h6 class="mt-3 mb-1">— Meetings</h6>
        <ul class="list-unstyled mb-3">
          @foreach($meetings as $m)
            <li class="py-2 border-bottom d-flex justify-content-between align-items-center">
              <div>
                <a href="{{ route('meetings.show', $m->id) }}" class="text-decoration-none">{{ $m->title }}</a>
              </div>
              <small class="text-muted">{{ isset($m->start_at) ? \Carbon\Carbon::parse($m->start_at)->format('Y-m-d H:i') : '' }}</small>
            </li>
          @endforeach
        </ul>
      @endif

      @if($receipts->count() > 0)
        <h6 class="mt-3 mb-1">— Receipts</h6>
        <ul class="list-unstyled mb-3">
          @foreach($receipts as $r)
            <li class="py-2 border-bottom d-flex justify-content-between align-items-center">
              <div>{{ $r->subject }}</div>
              <small class="text-muted">{{ number_format($r->amount, 2) }} • {{ $r->created_at->format('Y-m-d') }}</small>
            </li>
          @endforeach
        </ul>
      @endif

      @if($groups->count() > 0)
        <h6 class="mt-3 mb-1">— Groups</h6>
        <ul class="list-unstyled mb-3">
          @foreach($groups as $g)
            <li class="py-2 border-bottom d-flex justify-content-between align-items-center">
              <div><a href="{{ route('groups.show', $g->id) }}" class="text-decoration-none">{{ $g->name }}</a></div>
              <small class="text-muted">{{ isset($g->created_at) ? \Carbon\Carbon::parse($g->created_at)->format('Y-m-d') : '' }}</small>
            </li>
          @endforeach
        </ul>
      @endif

      {{-- If everything empty, show a single message --}}
      @if($sanghs->count() === 0 && $files->count() === 0 && $meetings->count() === 0 && $receipts->count() === 0 && $groups->count() === 0)
        <div class="alert alert-warning">No results found for <strong>{{ $q }}</strong>.</div>
      @endif

    @endif
  </div>
@endsection
