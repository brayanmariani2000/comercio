@extends('layouts.app')

@section('title', 'Configuración de Cuenta | Monagas Vende')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center fade-in-up">
        <div class="col-md-8">
            <div class="glass-card p-5 text-center">
                <i class="fas fa-cogs fa-4x text-neon-purple mb-4"></i>
                <h2 class="display-font text-white mb-3">PANEL DE CONFIGURACIÓN</h2>
                <p class="text-muted lead mb-4">Módulo en construcción. Pronto podrás gestionar tus preferencias avanzadas, notificaciones y privacidad desde esta interfaz neural.</p>
                
                <div class="alert alert-dark border-neon-purple d-inline-block px-4 py-2 mb-4">
                    <i class="fas fa-hammer me-2 text-warning"></i> Work in Progress
                </div>

                <div>
                    <a href="{{ route('perfil') }}" class="btn btn-outline-light me-2">
                        <i class="fas fa-arrow-left me-2"></i> Volver al Perfil
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-neon-purple">
                        <i class="fas fa-home me-2"></i> Ir al Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.text-neon-purple { color: #bc13fe; text-shadow: 0 0 15px rgba(188, 19, 254, 0.5); }
.border-neon-purple { border: 1px solid #bc13fe; box-shadow: 0 0 10px rgba(188, 19, 254, 0.2); }

.btn-neon-purple {
    background-color: transparent;
    border: 1px solid #bc13fe;
    color: #bc13fe;
    transition: all 0.3s ease;
}
.btn-neon-purple:hover {
    background-color: #bc13fe;
    color: white;
    box-shadow: 0 0 20px rgba(188, 19, 254, 0.6);
}
.glass-card {
    background: rgba(16, 23, 41, 0.85); /* Slightly darker/more opaque */
    backdrop-filter: blur(10px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.display-font { font-family: 'Orbitron', sans-serif; }
</style>
@endsection
