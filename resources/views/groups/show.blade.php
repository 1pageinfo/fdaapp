@extends('layouts.app')

@section('content')
  <div class="container py-3">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-start align-items-sm-center justify-content-between mb-2">
      <div class="mr-2">
        <a href="{{ route('groups.index') }}" class="btn btn-link px-0 d-inline-flex align-items-center mb-2">
          <i class="ti-angle-left mr-1"></i> Back
        </a>
        <h3 class="mb-1 d-flex align-items-center">
          <i class="ti-comments mr-2 d-none d-sm-inline"></i>{{ $group->name }}
        </h3>
        <p class="text-muted mb-0">{{ $group->description }}</p>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <!-- Trigger button -->
        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addTabModal">
          <i class="ti-plus mr-1"></i> New Tab
        </button>
        &nbsp; <a href="{{ route('groups.edit', $group) }}" class="btn btn-sm btn-outline-primary">
          <i class="ti-pencil mr-1"></i> Edit 
        </a>
         &nbsp; <form action="{{ route('groups.destroy', $group) }}" method="POST"
          onsubmit="return confirm('Are you sure? This will delete the group and all chats/messages.');">
          @csrf
          @method('DELETE')

          <button class="btn btn-danger btn-sm">
            <i class="ti-trash"></i>
          </button>
        </form>
      </div>
    </div>

    <div class="row">
      {{-- LEFT: Chat Tabs (Channels) --}}
      <div class="col-lg-8 mb-4">
        @error('tab') <div class="text-danger small mb-2">{{ $message }}</div> @enderror


        {{-- Channel list --}}
        <div class="card shadow-sm">
          <div class="px-3 py-2 border-bottom bg-light small text-muted">
            Drag tabs to change their order in this group and in the chat panel.
          </div>
          <ul class="list-group list-group-flush" id="tabList" data-reorder-url="{{ route('groups.tabs.reorder', $group) }}">
            @forelse($group->chats as $c)
              @php
                $initial = mb_strtoupper(mb_substr($c->tab ?? 'T', 0, 1));
                $palette = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
                $color = $palette[($c->id ?? 0) % count($palette)];
              @endphp
              <li class="list-group-item p-0 sortable-tab-item" data-id="{{ $c->id }}" draggable="true">
                <div class="d-flex align-items-center p-3 channel-row" data-tab="{{ Str::lower($c->tab) }}">
                  <button type="button" class="btn btn-link text-muted px-0 mr-3 drag-handle" aria-label="Reorder tab" title="Drag to reorder" draggable="true">
                    <i class="ti-move"></i>
                  </button>
                  {{-- Avatar/initial --}}
                  <div class="avatar {{ $color }} text-white flex-shrink-0 mr-3" aria-hidden="true">{{ $initial }}</div>

                  {{-- Main --}}
                  <div class="flex-grow-1 min-w-0">
                    <a href="{{ route('groups.chat.show', [$group, $c]) }}" class="d-block text-reset text-decoration-none" draggable="false">
                      <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-1 text-truncate"># {{ $c->tab }}</h5>
                        <i class="ti-angle-right text-muted d-none d-sm-inline"></i>
                      </div>
                      <div class="small text-muted text-truncate">
                        Open chat
                      </div>
                    </a>
                  </div>

                  {{-- Actions: dropdown (mobile-friendly) --}}
                  <div class="ml-2">
                    <div class="dropdown">
                      <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">

                      </button>
                      <div class="dropdown-menu dropdown-menu-right p-2" style="min-width: 260px;">
                        {{-- Inline rename --}}
                        <form action="{{ route('groups.tabs.update', [$group, $c]) }}" method="POST" class="mb-2">
                          @csrf @method('PUT')
                          <div class="input-group input-group-sm">
                            <input name="tab" value="{{ $c->tab }}" class="form-control" required>
                            <div class="input-group-append">
                              <button class="btn btn-warning">Rename</button>
                            </div>
                          </div>
                        </form>

                        {{-- Delete --}}
                        <form action="{{ route('groups.tabs.destroy', [$group, $c]) }}" method="POST"
                          onsubmit="return confirm('Delete this tab and its messages?');">
                          @csrf @method('DELETE')
                          <button class="dropdown-item text-danger d-flex align-items-center" type="submit">
                            <i class="ti-trash mr-2"></i> Delete tab
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            @empty
              <li class="list-group-item py-4 text-center text-muted">No chat tabs yet. Create one above.</li>
            @endforelse
          </ul>
        </div>
      </div>

      {{-- RIGHT: Members --}}
      <div class="col-lg-4">

        <div class="card shadow-sm mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Group Members</span>
            <span class="badge badge-light border">{{ $group->users->count() }}</span>
          </div>

          <ul class="list-group list-group-flush">
            @forelse($group->users as $u)
              @php
                $initial = mb_strtoupper(mb_substr($u->name ?? 'U', 0, 1));
                $palette = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
                $color = $palette[($u->id ?? 0) % count($palette)];
              @endphp
              <li class="list-group-item d-flex align-items-center">

                {{-- Avatar --}}
                <div class="avatar {{ $color }} text-white flex-shrink-0 mr-3">{{ $initial }}</div>

                {{-- Name + Email --}}
                <div class="flex-grow-1 min-w-0">
                  <div class="text-truncate">{{ $u->name }}</div>
                  <div class="small text-muted text-truncate">{{ $u->email }}</div>
                </div>

                {{-- Admin Toggle --}}
                <form method="POST" action="{{ route('groups.users.admin', [$group, $u]) }}" class="mr-2">
                  @csrf
                  <input type="hidden" name="is_admin" value="{{ $u->pivot->is_admin ? 0 : 1 }}">

                  <button class="btn btn-sm {{ $u->pivot->is_admin ? 'btn-success' : 'btn-outline-secondary' }}">
                    {{ $u->pivot->is_admin ? 'Admin' : 'Make Admin' }}
                  </button>
                </form>

                {{-- Remove Member --}}
                <form method="POST" action="{{ route('groups.members.remove', [$group, $u]) }}"
                  onsubmit="return confirm('Remove {{ $u->name }} from this group?');">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger" title="Remove">
                    <i class="ti-close"></i>
                  </button>
                </form>

              </li>
            @empty
              <li class="list-group-item text-center text-muted">No members yet.</li>
            @endforelse
          </ul>
        </div>

        {{-- Add Member --}}
        <div class="card shadow-sm">
          <div class="card-body">
            <form method="POST" action="{{ route('groups.members.add', $group) }}">
              @csrf
              <div class="form-group">
                <label class="form-label">Add user</label>
                <select name="user_id" class="form-control" required>
                  <option value="">Select user…</option>
                  @foreach($allUsers as $u)
                    @if(!$group->users->contains('id', $u->id))
                      <option value="{{ $u->id }}">{{ $u->name }} – {{ $u->email }}</option>
                    @endif
                  @endforeach
                </select>
              </div>
              <button class="btn btn-primary btn-block">Add Member</button>
            </form>
          </div>
        </div>

      </div>

    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="addTabModal" tabindex="-1" role="dialog" aria-labelledby="addTabModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="addTabModalLabel">Create New Tab</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <form action="{{ route('groups.tabs.store', $group) }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="ti-plus"></i></span>
              </div>
              <input name="tab" class="form-control" placeholder="New tab name" required autofocus>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
            <button class="btn btn-primary">Add</button>
          </div>
        </form>

      </div>
    </div>
  </div>
@endsection

@push('styles')
  <style>
    .channel-row:hover {
      background: rgba(0, 0, 0, 0.03);
    }

    .sortable-tab-item.is-dragging {
      opacity: .55;
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

    .min-w-0 {
      min-width: 0;
    }

    /* for text truncation in flex */
  </style>
@endpush

@push('scripts')
  <script>
    // Filter tabs by name
    (function () {
      const list = document.getElementById('tabList');
      if (!list) return;

      let dragged = null;

      const persistOrder = async () => {
        const order = [...list.querySelectorAll('.sortable-tab-item')].map(item => Number(item.dataset.id));

        const response = await fetch(list.dataset.reorderUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
          },
          body: JSON.stringify({ order }),
        });

        if (!response.ok) {
          throw new Error('Tab reorder save failed');
        }
      };

      list.querySelectorAll('.sortable-tab-item').forEach(item => {
        item.addEventListener('dragstart', event => {
          dragged = item;
          item.classList.add('is-dragging');
          if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', String(item.dataset.id));
          }
        });

        item.addEventListener('dragend', async () => {
          item.classList.remove('is-dragging');
          if (!dragged) {
            return;
          }

          try {
            await persistOrder();
          } catch (error) {
            window.location.reload();
          }

          dragged = null;
        });

        item.addEventListener('dragover', event => {
          event.preventDefault();
          if (!dragged || dragged === item) {
            return;
          }

          const rect = item.getBoundingClientRect();
          const shouldInsertAfter = event.clientY > rect.top + rect.height / 2;
          list.insertBefore(dragged, shouldInsertAfter ? item.nextSibling : item);
        });
      });
    })();

    (function () {
      const q = document.getElementById('tabFilter');
      const list = document.getElementById('tabList');
      if (!q || !list) return;

      q.addEventListener('input', function () {
        const term = (this.value || '').trim().toLowerCase();
        list.querySelectorAll('.channel-row').forEach(row => {
          const hay = (row.dataset.tab || '').toLowerCase();
          row.parentElement.style.display = hay.includes(term) ? '' : 'none';
        });
      });
    })();
  </script>
@endpush