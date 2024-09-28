@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Add User</h1>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="/{{ app()->getlocale() }}/admin/users" method="POST">
        @csrf

        <div class="form-group position-relative">
            <label>Name</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group position-relative">
            <label>INN</label>
            <input type="text" name="inn" class="form-control" value="{{ old('inn') }}" required>
            @error('inn')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>


        <div class="form-group position-relative">
            <label>Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group position-relative">
            <label>Password</label>
            <div class="input-group">
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group position-relative">
            <label>Confirm Password</label>
            <div class="input-group">
                <input type="password" name="password_confirmation" class="form-control" id="confirmPassword" required>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Create User</button>
    </form>
</div>
@endsection
