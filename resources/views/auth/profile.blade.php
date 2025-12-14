@extends('layouts.app')

@section('title', 'Perfil de Usuario | Monagas Vende')

@section('content')
<div class="py-4">
    <div class="row fade-in-up">
        <!-- Sidebar Perfil -->
        <div class="col-md-4 mb-4">
            <div class="glass-card text-center p-4 h-100">
                <div class="position-relative d-inline-block mb-3">
                    <div class="avatar-glow"></div>
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle border border-2 border-neon-cyan" style="width: 120px; height: 120px; object-fit: cover; position: relative; z-index: 2;">
                    @else
                        <div class="rounded-circle border border-2 border-neon-cyan d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; background: rgba(0,0,0,0.5); position: relative; z-index: 2;">
                            <span class="display-4 text-neon-cyan fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                    @endif
                </div>

                <h4 class="text-white mb-1 display-font">{{ $user->name }}</h4>
                <p class="text-neon-cyan small mb-3">
                    @if($user->esAdministrador()) <i class="fas fa-crown me-1"></i> ADMINISTRADOR
                    @elseif($user->esVendedor()) <i class="fas fa-store me-1"></i> VENDEDOR
                    @else <i class="fas fa-user me-1"></i> COMPRADOR
                    @endif
                </p>

                <div class="border-top border-secondary pt-3 mt-3 text-start">
                    <p class="mb-2 text-muted small"><i class="fas fa-envelope me-2 text-neon-pink"></i> {{ $user->email }}</p>
                    <p class="mb-2 text-muted small"><i class="fas fa-phone me-2 text-neon-green"></i> {{ $user->telefono ?? 'Sin teléfono' }}</p>
                     @if($user->ciudad)
                    <p class="mb-2 text-muted small"><i class="fas fa-map-marker-alt me-2 text-neon-gold"></i> {{ $user->ciudad->nombre }}, {{ $user->estado->nombre }}</p>
                    @endif
                    <p class="mb-0 text-muted small"><i class="fas fa-calendar me-2 text-primary"></i> Miembro desde {{ $user->created_at->format('M Y') }}</p>
                </div>
                
                <div class="mt-4">
                    @if(!$user->esVendedor())
                        <a href="{{ route('vendedor.registro') }}" class="btn btn-outline-warning w-100 btn-sm">
                            <i class="fas fa-store me-2"></i> ¡Quiero Vender!
                        </a>
                    @else
                        <a href="{{ route('vendedor.dashboard') }}" class="btn btn-outline-success w-100 btn-sm">
                            <i class="fas fa-tachometer-alt me-2"></i> Panel de Vendedor
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="col-md-8">
            <div class="glass-card p-4">
                <ul class="nav nav-tabs border-secondary mb-4" id="perfilTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active bg-transparent text-neon-cyan border-0 border-bottom border-2 border-neon-cyan" id="actividad-tab" data-bs-toggle="tab" data-bs-target="#actividad" type="button" role="tab">Actividad Reciente</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link bg-transparent text-muted border-0" id="seguridad-tab" data-bs-toggle="tab" data-bs-target="#seguridad" type="button" role="tab">Seguridad</button>
                    </li>
                </ul>

                <div class="tab-content" id="perfilContent">
                    <!-- Tab Actividad -->
                    <div class="tab-pane fade show active" id="actividad" role="tabpanel">
                        <div class="row g-3 mb-4">
                            <div class="col-sm-4">
                                <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary text-center">
                                    <h3 class="text-neon-pink mb-0">{{ $user->pedidos->count() ?? 0 }}</h3>
                                    <small class="text-muted">Pedidos</small>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary text-center">
                                    <h3 class="text-neon-green mb-0">{{ $user->wishlists->count() ?? 0 }}</h3>
                                    <small class="text-muted">Wishlists</small>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="bg-dark bg-opacity-50 p-3 rounded border border-secondary text-center">
                                    <h3 class="text-neon-gold mb-0">0</h3>
                                    <small class="text-muted">Puntos</small>
                                </div>
                            </div>
                        </div>

                        <h5 class="text-white border-bottom border-secondary pb-2 mb-3">Últimas Interacciones</h5>
                        <p class="text-muted text-center py-4">No hay actividad reciente registrada en el sistema.</p>
                    </div>

                    <!-- Tab Seguridad -->
                    <div class="tab-pane fade" id="seguridad" role="tabpanel">
                        <form action="{{ route('auth.logout') }}" method="POST" class="mb-4">
                             @csrf
                             <h6 class="text-white mb-3">Sesión Actual</h6>
                             <button type="submit" class="btn btn-outline-danger w-100 mb-3">
                                 <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión Actual
                             </button>
                        </form>
                         
                         <div class="border-top border-secondary pt-3">
                             <h6 class="text-white mb-2">Eliminar Cuenta</h6>
                             <p class="text-muted small mb-3">Esta acción no se puede deshacer. Se eliminarán todos tus datos permanentemente.</p>
                             <button class="btn btn-danger btn-sm" disabled>Eliminar Cuenta (Contactar Admin)</button>
                         </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 text-end">
                <a href="{{ route('configuracion') }}" class="btn btn-neon-secondary btn-sm">
                    <i class="fas fa-cog me-2"></i> Editar Perfil Completo
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos Perfil Futurista */
:root {
    --neon-cyan: #00f3ff;
    --neon-pink: #bc13fe;
    --neon-green: #0aff00;
    --neon-gold: #ffd700;
    --card-bg: rgba(16, 23, 41, 0.8);
}

.glass-card {
    background: var(--card-bg);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(0, 243, 255, 0.1);
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
}

.display-font { font-family: 'Orbitron', sans-serif; letter-spacing: 1px; }

.text-neon-cyan { color: var(--neon-cyan) !important; text-shadow: 0 0 10px rgba(0, 243, 255, 0.5); }
.text-neon-pink { color: var(--neon-pink) !important; text-shadow: 0 0 10px rgba(188, 19, 254, 0.5); }
.text-neon-green { color: var(--neon-green) !important; text-shadow: 0 0 10px rgba(10, 255, 0, 0.5); }
.text-neon-gold { color: var(--neon-gold) !important; text-shadow: 0 0 10px rgba(255, 215, 0, 0.5); }

.border-neon-cyan { border-color: var(--neon-cyan) !important; box-shadow: 0 0 10px rgba(0, 243, 255, 0.2); }

.avatar-glow {
    position: absolute;
    top: -5px; left: -5px; right: -5px; bottom: -5px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0,243,255,0.4), transparent 70%);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
    100% { transform: scale(1); opacity: 0.5; }
}

.btn-neon-secondary {
    background: transparent;
    border: 1px solid var(--neon-cyan);
    color: var(--neon-cyan);
    transition: all 0.3s ease;
}

.btn-neon-secondary:hover {
    background: var(--neon-cyan);
    color: #000;
    box-shadow: 0 0 15px var(--neon-cyan);
}
</style>
@endsection
