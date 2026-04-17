@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>User Role Management</h2>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <table class="table table-bordered">
        <thead>
            <tr><th>User</th><th>Email</th><th>Roles</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.user_roles.update',$user) }}">
                        @csrf @method('PUT')
                        @foreach($roles as $role)
                            <label class="me-2">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                       {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                {{ $role->name }}
                            </label>
                        @endforeach
                        <button class="btn btn-sm btn-primary">Save</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $users->links() }}
</div>
@endsection
