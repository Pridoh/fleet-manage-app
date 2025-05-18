@extends('layouts.auth')

@section('title', 'Login - Sistem Manajemen Armada')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5 col-xxl-4">
            <div class="card shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-sm-5">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-truck-moving fa-3x text-primary"></i>
                        </div>
                        <h3 class="fw-bold">Fleet Management</h3>
                        <p class="text-muted mb-0">Masuk ke sistem manajemen armada</p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-4">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text rounded-start"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="nama@perusahaan.com" required autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label">Kata Sandi</label>
                                @if (Route::has('password.request'))
                                    <a class="small text-decoration-none" href="{{ route('password.request') }}">
                                        Lupa kata sandi?
                                    </a>
                                @endif
                            </div>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text rounded-start"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Masukkan kata sandi" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Ingat saya
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg py-3">
                                <i class="fas fa-sign-in-alt me-2"></i> Masuk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
</script>
@endpush
@endsection 