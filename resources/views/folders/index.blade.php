@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">

    {{-- Header + actions --}}
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
        <h2 class="mb-0 d-flex align-items-center">
            <i class="ti-folder mr-2 d-none d-sm-inline"></i> File Manager
        </h2>
        <a href="{{ route('folders.create') }}" class="btn btn-primary btn-sm" data-perm="folders.create">
            <i class="ti-plus mr-1"></i> Create Folder
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Search --}}
    <div class="mb-3">
        <div class="input-group" style="max-width: 420px">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="icon-search"></i></span>
            </div>
            <input id="folderFilter" type="text" class="form-control" placeholder="Search folders…" autocomplete="off">
        </div>
    </div>

    <div class="row g-3" id="folderList" data-reorder-url="{{ route('folders.reorder') }}">
        @forelse ($folders as $folder)
            @php
                $palette = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
                $color = $palette[($folder->id ?? 0) % count($palette)];
            @endphp
            <div class="col-6 col-md-4 col-xl-3 mb-3 folder-item sortable-item" data-id="{{ $folder->id }}"
                 data-name="{{ Str::lower($folder->name) }}" draggable="true">
                <div class="card shadow-sm h-100 border-0 folder-card position-relative">

                    <div class="d-flex align-items-center justify-content-between px-2 pt-2">
                        <span class="text-muted drag-handle" title="Drag to reorder">
                            <i class="ti-move"></i>
                        </span>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-circle folder-menu-btn" type="button"
                                data-bs-toggle="dropdown" data-toggle="dropdown" aria-expanded="false" title="More options">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                                    class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                    <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0" />
                                </svg>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item" href="{{ route('folders.edit', $folder->id) }}">
                                        <i class="ti-pencil mr-2"></i>Edit
                                    </a>
                                </li>
                                <li>
                                    <form action="{{ route('folders.destroy', $folder->id) }}" method="POST"
                                        onsubmit="return confirm('Delete this folder?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="ti-trash mr-2"></i>Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <a href="{{ route('folders.show', $folder->id) }}"
                       class="text-decoration-none text-reset d-flex flex-column align-items-center text-center px-3 pb-3 pt-1 flex-grow-1">
                        <div class="folder-icon {{ $color }} text-white">
                            <i class="ti-folder"></i>
                        </div>
                        <h6 class="mt-2 mb-1 folder-name" title="{{ $folder->name }}">{{ $folder->name }}</h6>

                        <div class="mt-auto pt-1 d-flex gap-1 flex-wrap justify-content-center">
                            @if($folder->year)
                                <span class="badge badge-light border">{{ $folder->year }}</span>
                            @endif
                            @if($folder->group)
                                <span class="badge badge-light border text-truncate" style="max-width:120px" title="{{ $folder->group->name }}">
                                    <i class="ti-comments mr-1"></i>{{ $folder->group->name }}
                                </span>
                            @endif
                            @if($folder->subfolders_count)
                                <span class="badge badge-light border" title="Subfolders">
                                    <i class="ti-folder mr-1"></i>{{ $folder->subfolders_count }}
                                </span>
                            @endif
                            @if($folder->files_count)
                                <span class="badge badge-light border" title="Files">
                                    <i class="ti-file mr-1"></i>{{ $folder->files_count }}
                                </span>
                            @endif
                        </div>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="ti-folder" style="font-size: 2.5rem;"></i>
                    <p class="mt-2 mb-3">No folders yet.</p>
                    <a href="{{ route('folders.create') }}" class="btn btn-primary btn-sm">
                        <i class="ti-plus mr-1"></i> Create your first folder
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>

<style>
    .folder-card {
        border-radius: 14px;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .folder-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 .5rem 1.25rem rgba(0, 0, 0, .1) !important;
    }

    .folder-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-top: .25rem;
    }

    .folder-name {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.6em;
    }

    .folder-menu-btn {
        width: 28px;
        height: 28px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sortable-item.is-dragging {
        opacity: 0.55;
    }

    .drag-handle {
        cursor: grab;
    }
</style>

<script>
    (function () {
        const list = document.getElementById('folderList');
        if (!list) return;

        let dragged = null;

        const persistOrder = async () => {
            const order = [...list.querySelectorAll('.sortable-item')].map(item => Number(item.dataset.id));

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
                throw new Error('Folder reorder save failed');
            }
        };

        list.querySelectorAll('.sortable-item').forEach(item => {
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
                const relX = event.clientX - rect.left;
                const relY = event.clientY - rect.top;
                const shouldInsertAfter = (relX > rect.width / 2) || (relY > rect.height / 2);

                list.insertBefore(dragged, shouldInsertAfter ? item.nextSibling : item);
            });
        });

        // Client-side search filter
        const filter = document.getElementById('folderFilter');
        filter?.addEventListener('input', function () {
            const term = (this.value || '').trim().toLowerCase();
            list.querySelectorAll('.folder-item').forEach(item => {
                item.style.display = item.dataset.name.includes(term) ? '' : 'none';
            });
        });
    })();
</script>
@endsection
