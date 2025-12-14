@extends('layouts.auth')

@section('title', 'Nueva Contraseña de Seguridad')

@section('content')
<div class="col-md-5 col-lg-4">
    <div class="glass-card fade-in-up">
        <div class="text-center mb-4">
            <i class="fas fa-lock-open fa-3x text-neon mb-3"></i>
            <h4>RESTABLECER CREDENCIALES</h4>
            <p class="text-muted small">Define tu nueva clave de acceso</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="{{ $email ?? old('email') }}" required readonly>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Nueva Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required autofocus placeholder="Nueva clave">
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Repetir clave">
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-neon-primary">
                    ACTUALIZAR CONTRASEÑA
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
