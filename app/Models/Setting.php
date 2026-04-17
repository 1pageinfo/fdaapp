@extends('layouts.app')

@section('content')

<div class="container mt-4">

    <h2 class="d-flex justify-content-between align-items-center mb-3">
        <span>Settings</span>
    </h2>
    <hr>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- App Settings --}}
    <form method="POST" action="{{ route('settings.update') }}">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Application Name</label>
            <input type="text" name="app_name" class="form-control"
                value="{{ $settings['app_name'] ?? config('app.name') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contact Email</label>
            <input type="email" name="contact_email" class="form-control"
                value="{{ $settings['contact_email'] ?? '' }}">
        </div>

        <button class="btn btn-success">Save</button>
    </form>
</div>

{{-- Permission Manager --}}
<div class="container mt-5">
    <h3>User Permissions Management</h3>
    <hr>

    @if($errors->any())
        <div class="alert alert-danger">{{ implode(', ', $errors->all()) }}</div>
    @endif

    {{-- User select --}}
    <form method="GET" action="{{ route('settings.index') }}" class="form-group">
        <div class="col-md-6">
            <select name="user_id" class="form-control form-select form-control-lg"
                    onchange="this.form.submit()">

                <option value="">-- Select User --</option>

                @foreach($users as $u)
                    <option value="{{ $u->id }}"
                        {{ optional($selectedUser)->id == $u->id ? 'selected' : '' }}>
                        {{ $u->name }} ({{ $u->email }})
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    @if($selectedUser)

        @php
            $assignedSlugs = \DB::table('permission_user')
                ->join('permissions', 'permission_user.permission_id', '=', 'permissions.id')
                ->where('permission_user.user_id', $selectedUser->id)
                ->pluck('permissions.slug')
                ->toArray();

            $actions = ['create', 'view', 'edit', 'update', 'delete'];

            $features = $features ?? [
                'dashboard','receipts','sanghs','meetings','folders','files','groups','chats',
                'users','settings','reports','export','profile','search','notifications','tabs',
                'pin','audit','sangh_fee','coordination','work_app'
            ];
        @endphp

        {{-- Permissions table --}}
        <form method="POST" action="{{ route('settings.update') }}">
        @csrf @method('PUT')
        <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4>List of features</h4>

            <div class="btn-group">
                <button type="button" id="check-all" class="btn btn-sm btn-outline-primary">
                    Check All
                </button>
                <button type="button" id="uncheck-all" class="btn btn-sm btn-outline-danger">
                    Uncheck All
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:220px">Name</th>
                        <th class="text-center">Create</th>
                        <th class="text-center">View</th>
                        <th class="text-center">Edit</th>
                        <th class="text-center">Update</th>
                        <th class="text-center">Delete</th>
                        <th class="text-center">All</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($features as $feature)
                        <tr>
                            <td class="text-capitalize">
                                {{ str_replace('_',' ', $feature) }}
                            </td>

                            @foreach($actions as $action)
                                @php
                                    $slug = "$feature.$action";
                                @endphp
                                <td class="text-center">
                                    <input type="checkbox" name="permissions[]"
                                           value="{{ $slug }}"
                                           {{ in_array($slug, $assignedSlugs) ? 'checked' : '' }}>
                                </td>
                            @endforeach

                            <td class="text-center">
                                <input type="checkbox" class="toggle-row-all">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <hr>

        <div class="d-flex gap-2">
            <button class="btn btn-success">💾 Save</button>
            <a href="{{ route('settings.index', ['user_id' => $selectedUser->id]) }}"
               class="btn btn-secondary">Reset</a>
        </div>

        </form>
    @endif
</div>

<br><br><br>

{{-- Clean & optimized JS --}}
<script>
document.addEventListener("DOMContentLoaded", () => {

    const permCB = 'input[name="permissions[]"]';
    const rowAll = '.toggle-row-all';

    // Global check/uncheck
    document.getElementById("check-all")?.addEventListener("click", () => {
        document.querySelectorAll(permCB).forEach(cb => cb.checked = true);
        document.querySelectorAll(rowAll).forEach(cb => cb.checked = true);
    });

    document.getElementById("uncheck-all")?.addEventListener("click", () => {
        document.querySelectorAll(permCB).forEach(cb => cb.checked = false);
        document.querySelectorAll(rowAll).forEach(cb => cb.checked = false);
    });

    // Row All toggle
    document.querySelectorAll(rowAll).forEach(allBox => {
        allBox.addEventListener("change", function () {
            const row = this.closest("tr");
            row.querySelectorAll(permCB).forEach(cb => cb.checked = this.checked);
        });
    });

    // Auto update Row-All
    document.querySelector("table")?.addEventListener("change", e => {
        if (e.target.matches(permCB)) {
            const row = e.target.closest("tr");
            const boxes = [...row.querySelectorAll(permCB)];
            const rowAllBox = row.querySelector(rowAll);
            rowAllBox.checked = boxes.every(cb => cb.checked);
        }
    });

});
</script>

@endsection
