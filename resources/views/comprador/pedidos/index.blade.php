@extends('layouts.app')

@section('title', 'Mis Pedidos | Monagas Vende')

@section('content')
<div class="container fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-neon-gold display-font">Mis Pedidos</h2>
            <p class="text-muted">Historial de tus compras y estado de envíos</p>
        </div>
        <div>
            <a href="{{ route('productos.index') }}" class="btn btn-outline-gold">
                <i class="fas fa-shopping-cart me-2"></i>Seguir Comprando
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="glass-card stat-card border-neon-gold">
                <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                <h3 class="mb-0 counter text-white">{{ $user->total_compras }}</h3>
                <span class="text-muted small">TOTAL COMPRAS</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card border-neon-cyan">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <h3 class="mb-0 counter text-white">{{ $user->pedidos()->where('estado_pedido', 'pendiente')->count() }}</h3>
                <span class="text-muted small">PENDIENTES</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card border-neon-magenta">
                <div class="stat-icon"><i class="fas fa-truck"></i></div>
                <h3 class="mb-0 counter text-white">{{ $user->pedidos()->whereIn('estado_pedido', ['confirmado', 'preparando', 'enviado'])->count() }}</h3>
                <span class="text-muted small">EN PROCESO</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="glass-card stat-card border-neon-green">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <h3 class="mb-0 counter text-white">{{ $user->pedidos()->where('estado_pedido', 'entregado')->count() }}</h3>
                <span class="text-muted small">COMPLETADOS</span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-card mb-4">
        <form action="{{ route('comprador.pedidos.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small">Estado</label>
                <select name="estado" class="form-select bg-dark text-white border-secondary">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="confirmado" {{ request('estado') == 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                    <option value="enviado" {{ request('estado') == 'enviado' ? 'selected' : '' }}>Enviado</option>
                    <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                    <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">Fecha Desde</label>
                <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="form-control bg-dark text-white border-secondary">
            </div>
             <div class="col-md-3">
                <label class="form-label text-muted small">Fecha Hasta</label>
                <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="form-control bg-dark text-white border-secondary">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Orders List -->
    <div class="glass-card">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr class="text-secondary small">
                        <th>PEDIDO #</th>
                        <th>FECHA</th>
                        <th>PRODUCTOS</th>
                        <th>TOTAL</th>
                        <th>ESTADO</th>
                        <th>ACCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                    <tr>
                        <td class="fw-bold text-neon-cyan">{{ $pedido->numero_pedido }}</td>
                        <td>
                            <div class="text-white">{{ $pedido->created_at->format('d/m/Y') }}</div>
                            <div class="small text-muted">{{ $pedido->created_at->format('H:i A') }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($pedido->items->first() && $pedido->items->first()->producto && $pedido->items->first()->producto->imagenes->first())
                                    <img src="{{ Storage::url($pedido->items->first()->producto->imagenes->first()->ruta) }}" class="rounded me-2" width="40" height="40" style="object-fit:cover">
                                @else
                                     <div class="rounded me-2 bg-secondary d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
                                        <i class="fas fa-box text-white-50"></i>
                                     </div>
                                @endif
                                <div>
                                    <div class="text-white text-truncate" style="max-width: 200px;">
                                        {{ $pedido->items->first()->producto->nombre ?? 'Producto no disponible' }}
                                    </div>
                                    @if($pedido->items->count() > 1)
                                        <div class="small text-muted">+ {{ $pedido->items->count() - 1 }} más</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="fw-bold text-neon-gold">${{ number_format($pedido->total, 2) }}</td>
                        <td>
                            @php
                                $statusClasses = [
                                    'pendiente' => 'bg-warning text-dark',
                                    'confirmado' => 'bg-info text-white',
                                    'preparando' => 'bg-primary text-white',
                                    'enviado' => 'bg-purple text-white',
                                    'entregado' => 'bg-success text-white',
                                    'cancelado' => 'bg-danger text-white',
                                ];
                                $statusLabels = [
                                    'pendiente' => 'Pendiente',
                                    'confirmado' => 'Confirmado',
                                    'preparando' => 'Preparando',
                                    'enviado' => 'Enviado',
                                    'entregado' => 'Entregado',
                                    'cancelado' => 'Cancelado',
                                ];
                            @endphp
                            <span class="badge {{ $statusClasses[$pedido->estado_pedido] ?? 'bg-secondary' }}">
                                {{ $statusLabels[$pedido->estado_pedido] ?? ucfirst($pedido->estado_pedido) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('comprador.pedidos.show', $pedido->id) }}" class="btn btn-sm btn-outline-light" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($pedido->estado_pedido == 'pendiente')
                            <button class="btn btn-sm btn-outline-danger" title="Cancelar Pedido">
                                <i class="fas fa-times"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-shopping-basket fa-3x text-muted opacity-25"></i>
                            </div>
                            <h5 class="text-muted">No has realizado ningún pedido aún</h5>
                            <a href="{{ route('productos.index') }}" class="btn btn-primary mt-3">
                                Comenzar a comprar
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            {{ $pedidos->links() }}
        </div>
    </div>
</div>

<style>
/* Estilos extra para esta vista */
.bg-purple { background-color: #6f42c1; }
.btn-outline-gold {
    color: var(--neon-gold);
    border-color: var(--neon-gold);
}
.btn-outline-gold:hover {
    background-color: var(--neon-gold);
    color: #000;
}
.text-neon-gold { color: #ffd700; text-shadow: 0 0 10px rgba(255, 215, 0, 0.3); }
.text-neon-cyan { color: #00f3ff; text-shadow: 0 0 10px rgba(0, 243, 255, 0.3); }

/* Glass Cards (reutilizados del dashboard admin pero asegurando consistencia) */
.glass-card {
    background: rgba(20, 20, 30, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
}

.table-dark {
    background-color: transparent;
    --bs-table-bg: transparent;
}
.table-dark td, .table-dark th {
    border-bottom-color: rgba(255, 255, 255, 0.05);
}
</style>
@endsection
