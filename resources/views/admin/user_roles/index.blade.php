@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">

    {{-- Header + search --}}
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
        <h2 class="mb-0 d-flex align-items-center">
            <i class="ti-shield mr-2 d-none d-sm-inline"></i> User Role Management
        </h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-3">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="icon-search"></i></span>
            </div>
            <input id="userFilter" type="text" class="form-control" placeholder="Search by name or email…" autocomplete="off">
        </div>
    </div>

    {{-- User list --}}
    <div class="card shadow-sm">
        <div class="px-3 py-2 border-bottom bg-light small text-muted d-flex justify-content-between">
            <span>{{ $users->total() }} user(s)</span>
            <span>Toggle a role chip to grant/revoke it, then click Save.</span>
        </div>

        <ul class="list-group list-group-flush" id="userList">
            @forelse($users as $user)
                @php
                    $initial = mb_strtoupper(mb_substr($user->name ?? 'U', 0, 1));
                    $palette = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
                    $color = $palette[($user->id ?? 0) % count($palette)];
                @endphp
                <li class="list-group-item user-row" data-name="{{ Str::lower($user->name) }}" data-email="{{ Str::lower($user->email) }}">
                    <form method="POST" action="{{ route('admin.user_roles.update',$user) }}" class="d-flex flex-wrap align-items-center py-2">
                        @csrf @method('PUT')

                        <div class="avatar {{ $color }} text-white flex-shrink-0 mr-3" aria-hidden="true">
                            {{ $initial }}
                        </div>

                        <div class="mr-3 mb-2 mb-md-0" style="min-width:180px">
                            <div class="font-weight-bold text-truncate">{{ $user->name }}</div>
                            <div class="text-muted small text-truncate">{{ $user->email }}</div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center flex-grow-1 role-chip-group mb-2 mb-md-0">
                            @foreach($roles as $role)
                                @php $chipId = "role-{$user->id}-{$role->id}"; @endphp
                                <input type="checkbox" id="{{ $chipId }}" name="roles[]" value="{{ $role->id }}"
                                       class="role-chip-input"
                                       {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                <label for="{{ $chipId }}" class="role-chip role-chip-{{ $role->slug }}">
                                    {{ $role->slug }}
                                </label>
                            @endforeach
                        </div>

                        <div class="d-flex align-items-center ml-md-2">
                            <button class="btn btn-sm btn-primary mr-2">
                                <i class="ti-save mr-1"></i>Save
                            </button>

                            @if($user->id !== auth()->id())
                                <button type="button" class="btn btn-sm btn-outline-danger delete-user-btn"
                                        data-form-action="{{ route('admin.user_roles.destroy', $user) }}"
                                        title="Delete user">
                                    <i class="ti-trash"></i>
                                </button>
                            @else
                                <span class="badge badge-light border text-dark">You</span>
                            @endif
                        </div>
                    </form>
                </li>
            @empty
                <li class="list-group-item py-4 text-center text-muted">No users found.</li>
            @endforelse
        </ul>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
</div>

{{-- Hidden delete form (submitted via JS after confirmation) --}}
<form id="deleteUserForm" method="POST" class="d-none">
    @csrf @method('DELETE')
</form>

<style>
    .avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .user-row:hover {
        background: rgba(0, 0, 0, 0.02);
    }

    .role-chip-group {
        gap: .4rem;
    }

    .role-chip-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .role-chip {
        margin: 0;
        padding: .3rem .75rem;
        border-radius: 999px;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        color: #6c757d;
        font-size: .78rem;
        text-transform: capitalize;
        cursor: pointer;
        user-select: none;
        transition: all .15s ease-in-out;
    }

    .role-chip:hover {
        border-color: #adb5bd;
    }

    .role-chip-input:checked + .role-chip {
        color: #fff;
        border-color: transparent;
        background: #6c757d;
    }

    .role-chip-input:checked + .role-chip-superadmin { background: #dc3545; }
    .role-chip-input:checked + .role-chip-member { background: #198754; }

    .role-chip-input:focus-visible + .role-chip {
        box-shadow: 0 0 0 .15rem rgba(13, 110, 253, .35);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Client-side filter
    const q = document.getElementById('userFilter');
    const list = document.getElementById('userList');
    if (q && list) {
        q.addEventListener('input', function () {
            const term = (this.value || '').trim().toLowerCase();
            list.querySelectorAll('.user-row').forEach(row => {
                const hay = (row.dataset.name + ' ' + row.dataset.email);
                row.style.display = hay.includes(term) ? '' : 'none';
            });
        });
    }

    // Delete confirmation
    document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!confirm('Are you sure you want to delete this user? All personal information will be permanently removed, but their messages and uploads will be preserved.')) {
                return;
            }
            const form = document.getElementById('deleteUserForm');
            form.action = btn.dataset.formAction;
            form.submit();
        });
    });
});
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/themify-icons/1.0.1/css/themify-icons.css">
@endsection
