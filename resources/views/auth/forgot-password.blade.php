@extends('layouts.auth')

@section('title', 'Recuperación de Credenciales')

@section('content')
<div class="col-md-5 col-lg-4">
    <div class="glass-card fade-in-up">
        <div class="text-center mb-4">
            <i class="fas fa-key fa-3x text-neon mb-3"></i>
            <h4>RECUPERAR ACCESO</h4>
            <p class="text-muted small">Te enviaremos un enlace de restablecimiento seguro</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success bg-transparent border-neon text-light mb-4">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="form-label">Correo Electrónico</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-neon">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control border-start-0 ps-0" 
                           id="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="usuario@ejemplo.com">
                </div>
                @error('email')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-neon-primary">
                    ENVIAR ENLACE <i class="fas fa-paper-plane ms-2"></i>
                </button>
            </div>
        </form>

        <div class="text-center border-top border-secondary pt-3">
            <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left me-2"></i> Cancelar
            </a>
        </div>
    </div>
</div>
@endsection
