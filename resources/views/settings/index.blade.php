@extends('layouts.app')

@section('content')

<div class="container-fluid mt-4">

    <div class="mb-4">
        <h2 class="d-flex align-items-center mb-1">
            <i class="ti-settings mr-2 d-none d-sm-inline"></i> Settings
        </h2>
        <p class="text-muted mb-0">Manage application preferences, Sangh fee slabs, user roles, and permissions.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ implode(', ', $errors->all()) }}</div>
    @endif

    @php
        $activeTab = request('tab') === 'fields' && $canManagePermissions
            ? 'fields'
            : ($selectedUser ? 'access' : 'general');
    @endphp

    {{-- Tab navigation --}}
    <ul class="nav nav-pills settings-tabs mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}" data-toggle="tab" href="#tab-general" role="tab">
                <i class="ti-control-shuffle mr-1"></i> General
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'access' ? 'active' : '' }}" data-toggle="tab" href="#tab-access" role="tab">
                <i class="ti-shield mr-1"></i> Roles &amp; Access
            </a>
        </li>
        @if($canManagePermissions)
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'fields' ? 'active' : '' }}" data-toggle="tab" href="#tab-fields" role="tab">
                <i class="ti-lock mr-1"></i> Sangh Form Fields
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-fees" role="tab">
                <i class="ti-money mr-1"></i> Sangh Fees
            </a>
        </li>
    </ul>

    <div class="tab-content" id="settingsTabsContent">

        {{-- ===================== GENERAL ===================== --}}
        <div class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}" id="tab-general" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="d-flex align-items-center mb-1">
                        <i class="ti-control-shuffle mr-2 text-primary"></i> Application Details
                    </h5>
                    <p class="text-muted small mb-4">Basic information shown across the app.</p>

                    <form method="POST" action="{{ route('settings.update') }}" class="col-lg-6 p-0">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label small text-muted">Application Name</label>
                            <input type="text" name="app_name" class="form-control"
                                value="{{ old('app_name', $settings['app_name'] ?? config('app.name')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control"
                                value="{{ old('contact_email', $settings['contact_email'] ?? '') }}">
                        </div>

                        <button class="btn btn-primary btn-sm">
                            <i class="ti-save mr-1"></i>Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===================== ROLES & ACCESS ===================== --}}
        <div class="tab-pane fade {{ $activeTab === 'access' ? 'show active' : '' }}" id="tab-access" role="tabpanel">

            {{-- Shortcut to user role management --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center">
                        <div class="settings-tile bg-danger text-white mr-3">
                            <i class="ti-shield"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">User Roles</h6>
                            <p class="text-muted small mb-0">Assign superadmin or member roles, or remove a user account.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.user_roles.index') }}" class="btn btn-outline-danger btn-sm">
                        <i class="ti-user mr-1"></i> Manage User Roles
                    </a>
                </div>
            </div>

            @if($canManagePermissions)
            {{-- Permission Manager --}}
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="d-flex align-items-center mb-1">
                        <i class="ti-key mr-2 text-warning"></i> User Permissions
                    </h5>
                    <p class="text-muted small mb-1">Grant a specific user (Member or Admin) access per feature. They only ever see their own / assigned records.</p>
                    <p class="text-muted small mb-3"><i class="ti-info-alt"></i> Always available to everyone: Dashboard (own counts), Contacts, Profile, Search, Notifications, Activity Logs (own). Settings, Sangh Fees and Manage Users are superadmin only.</p>

                    {{-- User search / select --}}
                    <div class="mb-1" style="max-width: 420px; position: relative;">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="icon-search"></i></span>
                            </div>
                            <input type="text" id="userSearchBox" class="form-control" autocomplete="off"
                                   placeholder="Search user by name or email…"
                                   value="{{ $selectedUser ? $selectedUser->name.' ('.$selectedUser->email.')' : '' }}">
                            @if($selectedUser)
                                <div class="input-group-append">
                                    <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary" title="Clear">
                                        <i class="ti-close"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div id="userSearchResults" class="list-group shadow-sm" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1050; max-height:260px; overflow-y:auto;"></div>
                    </div>

                    @if($selectedUser)
                @php
                    $assignedSlugs = $assignedSlugs ?? [];
                    $initial = mb_strtoupper(mb_substr($selectedUser->name ?? 'U', 0, 1));
                    $actionColors = ['create' => 'primary', 'view' => 'info', 'edit' => 'warning', 'delete' => 'danger'];
                @endphp

                <div class="d-flex align-items-center bg-light rounded p-2 mt-3 mb-3">
                    <div class="avatar bg-warning text-white flex-shrink-0 mr-2">{{ $initial }}</div>
                    <div>
                        <div class="font-weight-bold">{{ $selectedUser->name }}</div>
                        <div class="text-muted small">{{ $selectedUser->email }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.update') }}">
                @csrf @method('PUT')
                <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">

                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div class="input-group input-group-sm" style="max-width: 260px">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="icon-search"></i></span>
                        </div>
                        <input id="featureFilter" type="text" class="form-control" placeholder="Filter features…" autocomplete="off">
                    </div>

                    <div class="btn-group">
                        <button type="button" id="check-all" class="btn btn-sm btn-outline-primary">
                            <i class="ti-check-box mr-1"></i>Grant All
                        </button>
                        <button type="button" id="uncheck-all" class="btn btn-sm btn-outline-danger">
                            <i class="ti-na mr-1"></i>Revoke All
                        </button>
                    </div>
                </div>

                @foreach($categories as $category)
                    <div class="perm-category mb-4">
                        <h6 class="text-uppercase text-muted small font-weight-bold mb-2 d-flex align-items-center">
                            <i class="{{ $category['icon'] }} mr-2"></i>{{ $category['label'] }}
                        </h6>
                        <div class="card border">
                            <ul class="list-group list-group-flush">
                                @foreach($category['features'] as $featureKey => $feature)
                                    <li class="list-group-item feature-row d-flex flex-wrap align-items-center justify-content-between"
                                        data-feature="{{ Str::lower($feature['label']) }} {{ $featureKey }}">
                                        <div class="d-flex align-items-center mb-2 mb-md-0 mr-3" style="min-width:180px">
                                            <i class="{{ $feature['icon'] }} mr-2 text-muted"></i>
                                            <span>{{ $feature['label'] }}</span>
                                        </div>

                                        <div class="d-flex flex-wrap perm-chip-group">
                                            @foreach($feature['actions'] as $action)
                                                @php $slug = "{$featureKey}.{$action}"; @endphp
                                                <input type="checkbox" id="perm-{{ $slug }}" name="permissions[]"
                                                       class="perm-chip-input" value="{{ $slug }}"
                                                       {{ in_array($slug, $assignedSlugs) ? 'checked' : '' }}>
                                                <label for="perm-{{ $slug }}" class="perm-chip perm-chip-{{ $actionColors[$action] ?? 'secondary' }}">
                                                    {{ ucfirst($action) }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex gap-2">
                    <button class="btn btn-success">
                        <i class="ti-save mr-1"></i>Save
                    </button>
                    <a href="{{ route('settings.index', ['user_id' => $selectedUser->id]) }}" class="btn btn-secondary">
                        Reset
                    </a>
                </div>

                </form>
                    @else
                        <p class="text-muted small mb-0 mt-3">Search for a user above to view or edit their permissions.</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- ===================== SANGH FORM FIELDS (superadmin) ===================== --}}
        @if($canManagePermissions)
        @php
            $sanghFieldGroups = \App\Support\SanghFormFields::GROUPS;
            $fieldUser = $activeTab === 'fields' ? $selectedUser : null;
            $lockedForSelected = $fieldUser ? \App\Support\SanghFormFields::sanitize((array) ($fieldUser->locked_sangh_fields ?? [])) : [];
            $usersWithLocks = $users->filter(fn ($u) => count(\App\Support\SanghFormFields::sanitize((array) ($u->locked_sangh_fields ?? []))) > 0);
        @endphp
        <div class="tab-pane fade {{ $activeTab === 'fields' ? 'show active' : '' }}" id="tab-fields" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="d-flex align-items-center mb-1">
                        <i class="ti-lock mr-2 text-danger"></i> Sangh Form Fields
                    </h5>
                    <p class="text-muted small mb-3">
                        Choose which Sangh form fields each user can fill in or change. Switched-off fields are shown
                        locked (🔒) on the Sangh form: when editing, the saved value is kept; when creating, the field is
                        left empty. Superadmins can always edit every field.
                    </p>

                    {{-- User picker --}}
                    <div class="mb-3" style="max-width: 420px;">
                        <label class="form-label small text-muted mb-1">Select user</label>
                        <select class="form-control" id="fieldUserSelect">
                            <option value="">— Select a user —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" @selected($fieldUser && $fieldUser->id === $u->id)>
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(! $fieldUser)
                        {{-- Overview of who currently has locked fields --}}
                        @if($usersWithLocks->isEmpty())
                            <p class="text-muted small mb-0">No fields are locked for anyone yet — every user can edit every Sangh form field. Select a user above to lock fields.</p>
                        @else
                            <h6 class="small text-uppercase text-muted font-weight-bold mt-4 mb-2">Users with locked fields</h6>
                            <ul class="list-group">
                                @foreach($usersWithLocks as $u)
                                    @php($uLocked = \App\Support\SanghFormFields::sanitize((array) $u->locked_sangh_fields))
                                    <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center">
                                        <div>
                                            <div class="font-weight-bold">{{ $u->name }}</div>
                                            <div class="small text-muted">
                                                🔒 {{ implode(', ', array_map([\App\Support\SanghFormFields::class, 'label'], $uLocked)) }}
                                            </div>
                                        </div>
                                        <a href="{{ route('settings.index', ['user_id' => $u->id, 'tab' => 'fields']) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @elseif($fieldUser->hasRole('superadmin'))
                        <div class="alert alert-info small mb-0">
                            <i class="ti-info-alt mr-1"></i> {{ $fieldUser->name }} is a superadmin and can always edit every field.
                        </div>
                    @else
                    <form method="POST" action="{{ route('settings.sangh_fields.update') }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="user_id" value="{{ $fieldUser->id }}">

                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <span class="small text-muted">
                                <strong>{{ $fieldUser->name }}</strong> —
                                <span id="sanghFieldsLockedCount">{{ count($lockedForSelected) }}</span> locked
                            </span>
                            <div class="btn-group">
                                <button type="button" id="sangh-fields-allow-all" class="btn btn-sm btn-outline-primary">
                                    <i class="ti-unlock mr-1"></i>Allow All
                                </button>
                                <button type="button" id="sangh-fields-lock-all" class="btn btn-sm btn-outline-danger">
                                    <i class="ti-lock mr-1"></i>Lock All
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            @foreach($sanghFieldGroups as $groupLabel => $fields)
                                <div class="col-md-6 col-xl-4 mb-3">
                                    <div class="card border h-100">
                                        <div class="card-header bg-light py-2 small font-weight-bold">{{ $groupLabel }}</div>
                                        <ul class="list-group list-group-flush">
                                            @foreach($fields as $field => $label)
                                                <li class="list-group-item py-2 d-flex justify-content-between align-items-center">
                                                    <label for="sf-{{ $field }}" class="mb-0 small">{{ $label }}</label>
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input sangh-field-toggle"
                                                               id="sf-{{ $field }}" name="editable_fields[]" value="{{ $field }}"
                                                               {{ in_array($field, $lockedForSelected, true) ? '' : 'checked' }}>
                                                        <label class="custom-control-label" for="sf-{{ $field }}"></label>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button class="btn btn-success">
                            <i class="ti-save mr-1"></i>Save Field Access
                        </button>
                        <a href="{{ route('settings.index', ['tab' => 'fields']) }}" class="btn btn-secondary">Back</a>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ===================== SANGH FEES ===================== --}}
        <div class="tab-pane fade" id="tab-fees" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center">
                        <div class="settings-tile bg-success text-white mr-3">
                            <i class="ti-money"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">Sangh Fee Slabs</h6>
                            <p class="text-muted small mb-0">
                                Manage प्रवेश शुल्क, वार्षिक शुल्क (by member count) and विकास निधी शुल्क used in Sangh registrations.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('settings.sangh_fees.edit') }}" class="btn btn-outline-success btn-sm"
                       data-perm="sangh_fee.view|sangh_fee.edit">
                        <i class="ti-settings mr-1"></i> Manage Fee Slabs
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

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

    .settings-tile {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .settings-tabs .nav-link {
        border-radius: 999px;
        padding: .5rem 1.1rem;
        color: #495057;
        font-weight: 500;
    }

    .settings-tabs .nav-link.active {
        background: #0d6efd;
        color: #fff;
    }

    .perm-category .list-group-item {
        padding-top: .6rem;
        padding-bottom: .6rem;
    }

    .perm-category .list-group-item:hover {
        background: rgba(0, 0, 0, .02);
    }

    .perm-chip-group {
        gap: .4rem;
    }

    .perm-chip-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .perm-chip {
        margin: 0;
        padding: .3rem .7rem;
        border-radius: 999px;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        color: #6c757d;
        font-size: .76rem;
        cursor: pointer;
        user-select: none;
        transition: all .15s ease-in-out;
    }

    .perm-chip:hover {
        border-color: #adb5bd;
    }

    .perm-chip-input:checked + .perm-chip-primary { background: #0d6efd; border-color: transparent; color: #fff; }
    .perm-chip-input:checked + .perm-chip-info { background: #0dcaf0; border-color: transparent; color: #063542; }
    .perm-chip-input:checked + .perm-chip-warning { background: #fd7e14; border-color: transparent; color: #fff; }
    .perm-chip-input:checked + .perm-chip-danger { background: #dc3545; border-color: transparent; color: #fff; }

    .perm-chip-input:focus-visible + .perm-chip {
        box-shadow: 0 0 0 .15rem rgba(13, 110, 253, .35);
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const permCB = 'input.perm-chip-input';

    document.getElementById("check-all")?.addEventListener("click", () => {
        document.querySelectorAll(permCB).forEach(cb => cb.checked = true);
    });

    document.getElementById("uncheck-all")?.addEventListener("click", () => {
        document.querySelectorAll(permCB).forEach(cb => cb.checked = false);
    });

    // Sangh form field access: Allow all / Lock all + live "N locked" count
    const fieldToggles = () => document.querySelectorAll('input.sangh-field-toggle');
    const refreshLockedCount = () => {
        const out = document.getElementById('sanghFieldsLockedCount');
        if (out) out.textContent = [...fieldToggles()].filter(cb => !cb.checked).length;
    };
    document.getElementById('sangh-fields-allow-all')?.addEventListener('click', () => {
        fieldToggles().forEach(cb => cb.checked = true); refreshLockedCount();
    });
    document.getElementById('sangh-fields-lock-all')?.addEventListener('click', () => {
        fieldToggles().forEach(cb => cb.checked = false); refreshLockedCount();
    });
    fieldToggles().forEach(cb => cb.addEventListener('change', refreshLockedCount));
    document.getElementById('fieldUserSelect')?.addEventListener('change', function () {
        window.location.href = '{{ route('settings.index') }}?tab=fields' + (this.value ? '&user_id=' + this.value : '');
    });

    // Feature filter
    const filter = document.getElementById('featureFilter');
    filter?.addEventListener('input', function () {
        const term = (this.value || '').trim().toLowerCase();
        document.querySelectorAll('.feature-row').forEach(row => {
            row.style.display = row.dataset.feature.includes(term) ? '' : 'none';
        });
        document.querySelectorAll('.perm-category').forEach(cat => {
            const anyVisible = [...cat.querySelectorAll('.feature-row')].some(r => r.style.display !== 'none');
            cat.style.display = anyVisible ? '' : 'none';
        });
    });

    // User search / select combobox
    const allUsers = @json($users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]));
    const userBox = document.getElementById('userSearchBox');
    const userResults = document.getElementById('userSearchResults');

    const renderUserResults = (term) => {
        const t = term.trim().toLowerCase();
        const matches = allUsers.filter(u =>
            u.name.toLowerCase().includes(t) || u.email.toLowerCase().includes(t)
        ).slice(0, 20);

        if (!matches.length) {
            userResults.innerHTML = '<div class="list-group-item text-muted small">No users found</div>';
        } else {
            userResults.innerHTML = matches.map(u => `
                <a href="#" class="list-group-item list-group-item-action py-2" data-id="${u.id}">
                    <div class="font-weight-bold">${u.name}</div>
                    <div class="text-muted small">${u.email}</div>
                </a>
            `).join('');
        }
        userResults.style.display = 'block';
    };

    userBox?.addEventListener('focus', () => renderUserResults(userBox.value));
    userBox?.addEventListener('input', () => renderUserResults(userBox.value));

    userResults?.addEventListener('click', (e) => {
        const item = e.target.closest('[data-id]');
        if (!item) return;
        e.preventDefault();
        window.location.href = '{{ route('settings.index') }}?user_id=' + item.dataset.id;
    });

    document.addEventListener('click', (e) => {
        if (userBox && userResults && !userBox.contains(e.target) && !userResults.contains(e.target)) {
            userResults.style.display = 'none';
        }
    });

});
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/themify-icons/1.0.1/css/themify-icons.css">

@endsection
