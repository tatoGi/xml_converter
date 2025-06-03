@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Edit User</h1>

    <form action="/{{ app()->getlocale() }}/admin/users/{{ $user->id }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
        </div>
        <div class="form-group">
            <label>Inn</label>
            <input type="text" name="inn" class="form-control" value="{{ $user->inn }}" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
        </div>
        <div class="form-group position-relative">
            <label>Password (leave blank to keep current)</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="New password">
            <span class="position-absolute" style="right: 10px; top: 35%; cursor: pointer;" id="togglePassword">
                <i class="bi bi-eye" id="eyeIcon" aria-hidden="true"></i>
            </span>
        </div>
        <div class="form-group position-relative">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm new password">
            <span class="position-absolute" style="right: 10px; top: 35%; cursor: pointer;" id="toggleConfirmPassword">
                <i class="bi bi-eye" id="confirmEyeIcon" aria-hidden="true"></i>
            </span>
        </div>
        <button type="submit" class="btn btn-success">Update User</button>
    </form>
</div>

<script>
    // Functionality to toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        eyeIcon.classList.toggle('bi-eye-slash');
    });

    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const confirmEyeIcon = document.getElementById('confirmEyeIcon');

    toggleConfirmPassword.addEventListener('click', function () {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);
        confirmEyeIcon.classList.toggle('bi-eye-slash');
    });
</script>
@endsection
