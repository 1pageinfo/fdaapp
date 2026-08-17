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
                                {{ $role->slug }}
                            </label>
                        @endforeach
                        <button class="btn btn-sm btn-primary ms-2">Save</button>
                    </form>
                </td>
                <td>
                    @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.user_roles.destroy', $user) }}" class="d-inline"
                            onsubmit="return confirm('Are you sure you want to delete this user? All personal information will be permanently removed, but their messages and uploads will be preserved.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    @else
                        <span class="badge badge-secondary text-dark">Current User</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $users->links() }}
</div>
@endsection
