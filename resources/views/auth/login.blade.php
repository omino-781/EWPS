@extends('layouts.guest')
@section('title', 'Login')
@section('content')
<h4 class="mb-3">Role-Based Login</h4>
<form method="POST" action="{{ secure_url(route('login', [], false)) }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
            <option value="">Select your role</option>
            @foreach($roleOptions as $role)
            <option value="{{ $role['slug'] }}" {{ old('role') === $role['slug'] ? 'selected' : '' }}>
                {{ $role['label'] }}
            </option>
            @endforeach
        </select>
        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" name="remember" class="form-check-input" id="remember">
        <label class="form-check-label" for="remember">Remember me</label>
    </div>
    <button class="btn btn-primary w-100">Login</button>
</form>
<p class="mt-3 mb-0 text-center"><a href="{{ route('register') }}">Create an account</a></p>
@endsection
