@extends('layouts.app')

@section('content')
  <div class="container mt-4">

    {{-- Header + actions --}}
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
      <h2 class="mb-0 d-flex align-items-center">
        <i class="ti-comments mr-2 d-none d-sm-inline"></i> Groups
      </h2>

      <div class="d-flex gap-2">
        <a href="{{ route('groups.create') }}" class="btn btn-primary btn-sm">
          <span class="mr-1">➕</span> New Group
        </a>
        <a href="{{ route('groups.export') }}" class="btn btn-warning btn-sm">
          <span class="mr-1">⬇</span> Export CSV
        </a>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Optional quick search (filters client-side by group name/desc) --}}
    <div class="mb-3">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text"><i class="icon-search"></i></span>
        </div>
        <input id="groupFilter" type="text" class="form-control" placeholder="Search groups…" autocomplete="off">
      </div>
    </div>

    {{-- Chat-style list --}}
    <div class="card shadow-sm">
      <ul class="list-group list-group-flush" id="groupList">
        @forelse($groups as $g)
          @php
            $initial = mb_strtoupper(mb_substr($g->name ?? 'G', 0, 1));
            // Pick a soft avatar color based on group id to add variety
            $palette = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
            $color = $palette[($g->id ?? 0) % count($palette)];
          @endphp

          <li class="list-group-item p-0">
            <a href="{{ route('groups.show', $g) }}"
              class="d-flex align-items-center p-3 text-reset text-decoration-none chat-item"
              data-name="{{ Str::lower($g->name) }}" data-desc="{{ Str::lower($g->description) }}">
              {{-- Avatar / initials --}}
              <div class="avatar {{ $color }} text-white flex-shrink-0 mr-3" aria-hidden="true">
                {{ $initial }}
              </div>

              {{-- Main content --}}
              <div class="flex-grow-1 min-w-0">
                <div class="d-flex align-items-center justify-content-between">
                  <h5 class="mb-0 text-truncate">{{ $g->name }}</h5>

                  {{-- Meta badges (right aligned on wider screens) --}}
                  <div class="ml-2 d-none d-sm-flex align-items-center">
                    <span class="badge badge-light border mr-2" title="Members">
                      <i class="ti-user mr-1"></i>{{ $g->users_count }}
                    </span>
                    <span class="badge badge-light border" title="Tabs/Chats">
                      <i class="ti-comment mr-1"></i>{{ $g->chats_count }}
                    </span>
                  </div>
                </div>

                <div class="text-muted small mt-1 text-truncate-2">
                  {{ $g->description ?: 'No description' }}
                </div>

                {{-- Mobile-friendly meta row --}}
                <div class="d-sm-none mt-2">
                  <span class="badge badge-light border mr-2">
                    <i class="ti-user mr-1"></i>{{ $g->users_count }} members
                  </span>
                  <span class="badge badge-light border">
                    <i class="ti-comment mr-1"></i>{{ $g->chats_count }} tabs
                  </span>
                </div>
              </div>

              {{-- Chevron --}}
              <div class="ml-3 d-none d-sm-block text-muted">
                <i class="ti-angle-right"></i>
              </div>
            </a>
          </li>
        @empty
          <li class="list-group-item py-4 text-center text-muted">
            No groups yet. Create your first one!
          </li>
        @endforelse
      </ul>
    </div>

    <style>
      .chat-item:hover {
        background: rgba(0, 0, 0, 0.03);
      }

      .avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
      }

      .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }

      /* Small gap utility for older BS */
      .gap-2>*+* {
        margin-left: .5rem;
      }

      @media (max-width: 575.98px) {
        .gap-2>*+* {
          margin-left: .5rem;
        }
      }
    </style>

    <script>
      // Lightweight client-side filter
      (function () {
        const q = document.getElementById('groupFilter');
        const list = document.getElementById('groupList');
        if (!q || !list) return;

        q.addEventListener('input', function () {
          const term = (this.value || '').trim().toLowerCase();
          list.querySelectorAll('.chat-item').forEach(item => {
            const hay = (item.dataset.name + ' ' + item.dataset.desc).trim();
            item.parentElement.style.display = hay.includes(term) ? '' : 'none';
          });
        });
      })();
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/themify-icons/1.0.1/css/themify-icons.css">

@endsection