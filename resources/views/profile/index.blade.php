@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>My Profile</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Profile Details --}}
    <form method="POST" action="{{ route('profile.update') }}" class="mb-5" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="row g-3">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name',$user->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email',$user->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone',$user->phone) }}"
                                   placeholder="+91-XXXXXXXXXX">
                            <small class="text-muted">Include country/area code if needed.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" class="form-control"
                                   value="{{ old('designation',$user->designation) }}"
                                   placeholder="e.g., Coordinator, Member, Admin">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" rows="3" class="form-control"
                                      placeholder="Street, City, District, State, PIN">{{ old('address',$user->address) }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-success">Save Profile</button>
                            <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Photo card --}}
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <label class="form-label">Profile Photo</label>

                        <div class="mb-3 text-center">
                            @php
                                $photoUrl = $user->photo_path ? asset('storage/app/public/'.$user->photo_path) : 'theme/images/default-avatar.png';
                            @endphp
                            <img id="preview" src="{{ $photoUrl }}" alt="Profile photo"
                                 class="rounded-circle border" style="width:160px;height:160px;object-fit:cover;">
                        </div>

                        <input type="file" name="photo" class="form-control mb-2" accept="image/*" onchange="previewPhoto(event)">
                        <small class="text-muted d-block mb-2">PNG/JPG up to 2 MB.</small>

                        @if($user->photo_path)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="remove_photo" name="remove_photo">
                                <label class="form-check-label" for="remove_photo">
                                    Remove current photo
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Password --}}
    <h3>Change Password</h3>
    <form method="POST" action="{{ route('profile.password') }}" class="card card-body shadow-sm">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-warning">Change Password</button>
        </div>
    </form>
</div>

{{-- Small preview script --}}
<script>
function previewPhoto(e){
    const file = e.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => { document.getElementById('preview').src = ev.target.result; };
    reader.readAsDataURL(file);
}
</script>
@endsection
