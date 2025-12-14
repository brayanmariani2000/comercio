@extends('layouts.auth')

@section('title', 'Verificación de Seguridad')

@section('content')
<div class="col-md-6 col-lg-5">
    <div class="glass-card text-center fade-in-up">
        <i class="fas fa-shield-alt fa-4x text-neon mb-4"></i>
        
        <h3 class="mb-3">VERIFICACIÓN REQUERIDA</h3>
        
        @if (session('message'))
            <div class="alert alert-info border-neon bg-transparent text-light">
                {{ session('message') }}
            </div>
        @endif

        <p class="text-muted mb-4">
            Hemos enviado un código de acceso seguro a tu correo <strong>{{ $email ?? 'registrado' }}</strong>.
            Introdúcelo para sincronizar tu cuenta.
        </p>

        <form method="POST" action="{{ route('auth.verify') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email ?? '' }}">

            <div class="mb-4">
                <input type="text" class="form-control text-center fs-3 tracking-widest text-neon border-neon" 
                       id="codigo" name="codigo" required autofocus 
                       maxlength="6" placeholder="000-000">
            </div>

            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-neon-primary btn-lg">
                    VALIDAR ACCESO
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('auth.resend') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email ?? '' }}">
            <button type="submit" class="btn btn-link text-muted text-decoration-none">
                <i class="fas fa-sync-alt me-1"></i> Reenviar código
            </button>
        </form>
    </div>
</div>
@endsection
