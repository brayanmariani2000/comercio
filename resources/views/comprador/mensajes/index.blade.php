@extends('layouts.app')

@section('title', 'Mis Mensajes | Monagas Vende')

@section('content')
<div class="container fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-font text-white">Mis Mensajes</h1>
            <p class="text-secondary">Gestiona tus conversaciones con vendedores</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <!-- Lista de conversaciones sidebar -->
            <div class="glass-card h-100 p-0 overflow-hidden">
                <div class="p-3 border-bottom border-secondary bg-dark-opacity">
                    <input type="text" class="form-control bg-transparent border-secondary text-white" placeholder="Buscar conversación...">
                </div>
                
                <div class="conversations-list custom-scroll" style="max-height: 600px; overflow-y: auto;">
                    @forelse($conversaciones as $conv)
                        <a href="#" class="conversation-item p-3 d-block text-decoration-none border-bottom border-secondary {{ $loop->first ? 'active-gold' : '' }} hover-bg-dark">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="text-gold mb-0 font-weight-bold">{{ $conv->vendedor->nombre_comercial }}</h6>
                                <span class="small text-muted">{{ $conv->ultimo_mensaje_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-white small mb-1 text-truncate">{{ $conv->producto->nombre ?? 'Consulta General' }}</p>
                            <p class="text-secondary small mb-0 text-truncate">
                                @if($conv->ultimoMensaje)
                                    {{ $conv->ultimoMensaje->user_id == auth()->id() ? 'Tú: ' : '' }} {{ $conv->ultimoMensaje->contenido }}
                                @else
                                    Inicio de conversación
                                @endif
                            </p>
                        </a>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="far fa-comments fa-2x mb-3"></i>
                            <p>No tienes mensajes aún.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Área de chat (Placeholder por ahora, se cargaría dinámicamente) -->
            <div class="glass-card h-100 p-0 d-flex flex-column" style="min-height: 500px;">
                @if($conversaciones->count() > 0)
                    <div class="chat-header p-3 border-bottom border-secondary d-flex align-items-center justify-content-between bg-dark-opacity">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3 bg-gold text-dark font-weight-bold">
                                {{ substr($conversaciones->first()->vendedor->nombre_comercial, 0, 1) }}
                            </div>
                            <div>
                                <h5 class="text-white mb-0">{{ $conversaciones->first()->vendedor->nombre_comercial }}</h5>
                                <small class="text-success"><i class="fas fa-circle x-small me-1"></i> En línea</small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-ellipsis-v"></i></button>
                    </div>

                    <div class="chat-body p-4 flex-grow-1 custom-scroll" style="overflow-y: auto; background: rgba(0,0,0,0.2);">
                        <!-- Mensajes de ejemplo (se reemplazarían con real data) -->
                        <div class="text-center mb-4">
                            <span class="badge bg-secondary opacity-50">{{ $conversaciones->first()->created_at->format('d M Y') }}</span>
                        </div>
                        
                        <!-- Aquí iría el bucle de mensajes de la conversación seleccionada -->
                        <div class="alert alert-info bg-transparent border-gold text-gold text-center">
                            <i class="fas fa-info-circle me-2"></i> Selecciona una conversación para ver el historial.
                        </div>
                    </div>

                    <div class="chat-footer p-3 border-top border-secondary bg-dark-opacity">
                        <form class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary rounded-circle"><i class="fas fa-paperclip"></i></button>
                            <input type="text" class="form-control bg-transparent border-secondary text-white" placeholder="Escribe tu mensaje...">
                            <button type="submit" class="btn btn-gold"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                        <i class="far fa-paper-plane fa-4x mb-3 text-secondary opacity-50"></i>
                        <h4>Tus Mensajes</h4>
                        <p>Selecciona un producto y contacta al vendedor para iniciar un chat.</p>
                        <a href="{{ route('productos.index') }}" class="btn btn-outline-gold mt-3">Explorar Productos</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .glass-card {
        background: rgba(30, 30, 30, 0.6);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(212, 175, 55, 0.1);
        border-radius: 12px;
    }
    
    .bg-dark-opacity { background: rgba(0,0,0,0.2); }
    
    .text-gold { color: var(--primary-gold); }
    .border-gold { border-color: var(--primary-gold) !important; }
    .btn-gold { 
        background: var(--primary-gold); 
        color: #000;
        border: none;
    }
    .btn-outline-gold {
        color: var(--primary-gold);
        border: 1px solid var(--primary-gold);
        background: transparent;
    }
    .btn-outline-gold:hover {
        background: var(--primary-gold);
        color: #000;
    }

    .conversation-item:hover, .conversation-item.active-gold {
        background: rgba(212, 175, 55, 0.05);
        border-right: 3px solid var(--primary-gold);
    }
    
    .avatar-circle {
        width: 40px; height: 40px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .bg-gold { background-color: var(--primary-gold); }
    
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
</style>
@endpush
@endsection
