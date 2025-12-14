@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: #0f172a; min-height: 100vh;">
    <div class="row">
        <!-- Sidebar Navigation (Optional, could be part of layout or added here) -->
        <div class="col-md-3 col-lg-2 d-none d-md-block">
            @include('partials.comprador-sidebar')
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <!-- Welcome Section -->
            <div class="d-flex justify-content-between align-items-center mb-4 text-white">
                <div>
                    <h2 class="fw-bold mb-1">Hola, {{ auth()->user()->name }} 👋</h2>
                    <p class="text-muted mb-0">Aquí tienes el resumen de tu actividad.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('comprador.pedidos.index') }}" class="btn btn-outline-light rounded-pill">
                        <i class="fas fa-list me-2"></i> Mis Pedidos
                    </a>
                    <a href="{{ route('comprador.carrito') }}" class="btn btn-primary rounded-pill">
                        <i class="fas fa-shopping-cart me-2"></i> Ir al Carrito
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <!-- Total Orders -->
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 h-100 bg-dark text-white shadow-sm" style="background: linear-gradient(145deg, #1e293b, #0f172a);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 small text-uppercase fw-bold">Compras Totales</p>
                                    <h3 class="fw-bold mb-0">{{ $estadisticas['compras']['total'] }}</h3>
                                    <small class="text-success"><i class="fas fa-arrow-up me-1"></i>{{ $estadisticas['compras']['compras_mes'] }} este mes</small>
                                </div>
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                                    <i class="fas fa-shopping-bag fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Spent -->
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 h-100 bg-dark text-white shadow-sm" style="background: linear-gradient(145deg, #1e293b, #0f172a);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 small text-uppercase fw-bold">Total Gastado</p>
                                    <h3 class="fw-bold mb-0">${{ number_format($estadisticas['compras']['total_monto'], 2) }}</h3>
                                    <small class="text-muted">Acumulado histórico</small>
                                </div>
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                                    <i class="fas fa-dollar-sign fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Orders -->
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 h-100 bg-dark text-white shadow-sm" style="background: linear-gradient(145deg, #1e293b, #0f172a);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 small text-uppercase fw-bold">Pedidos Pendientes</p>
                                    <h3 class="fw-bold mb-0">{{ $estadisticas['compras']['pedidos_pendientes'] }}</h3>
                                    <small class="text-warning">En proceso de entrega</small>
                                </div>
                                <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                                    <i class="fas fa-clock fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wishlist -->
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 h-100 bg-dark text-white shadow-sm" style="background: linear-gradient(145deg, #1e293b, #0f172a);">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="text-muted mb-1 small text-uppercase fw-bold">En Wishlist</p>
                                    <h3 class="fw-bold mb-0">{{ $estadisticas['interacciones']['wishlist_items'] }}</h3>
                                    <small class="text-info">Productos guardados</small>
                                </div>
                                <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                                    <i class="fas fa-heart fa-lg"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Recent Orders & Notifications -->
                <div class="col-lg-8">
                    <!-- Recent Orders -->
                    <div class="card border-0 shadow-sm mb-4" style="background-color: #1e293b;">
                        <div class="card-header border-0 bg-transparent py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white fw-bold">Pedidos Recientes</h5>
                            <a href="{{ route('comprador.pedidos.index') }}" class="text-primary text-decoration-none small fw-bold">Ver todos <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover mb-0 align-middle">
                                    <thead class="bg-dark text-uppercase small text-muted">
                                        <tr>
                                            <th class="ps-4">Pedido #</th>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th class="text-end pe-4">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pedidosRecientes as $pedido)
                                            <tr>
                                                <td class="ps-4 fw-bold">
                                                    <span class="text-white">#{{ $pedido->numero_pedido }}</span>
                                                    <div class="small text-muted">{{ $pedido->items->count() }} ítems</div>
                                                </td>
                                                <td>{{ $pedido->created_at->format('d M, Y') }}</td>
                                                <td>${{ number_format($pedido->total, 2) }}</td>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'pendiente' => 'warning',
                                                            'pagado' => 'info',
                                                            'enviado' => 'primary',
                                                            'entregado' => 'success',
                                                            'cancelado' => 'danger'
                                                        ];
                                                        $color = $statusColors[$pedido->estado_pedido] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $color }} bg-opacity-25 text-{{ $color }} rounded-pill px-3">
                                                        {{ ucfirst($pedido->estado_pedido) }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="{{ route('comprador.pedidos.show', $pedido->id) }}" class="btn btn-sm btn-outline-light rounded-circle" title="Ver detalle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="fas fa-shopping-basket fa-3x mb-3 opacity-50"></i>
                                                    <p class="mb-0">No has realizado pedidos aún.</p>
                                                    <a href="{{ route('home') }}" class="btn btn-sm btn-primary mt-3">Comenzar a comprar</a>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recommended Products -->
                    @if(isset($recomendaciones['basado_en_compras']) && $recomendaciones['basado_en_compras']->isNotEmpty())
                        <div class="mb-4">
                            <h5 class="text-white fw-bold mb-3">Recomendado para ti</h5>
                            <div class="row g-3">
                                @foreach($recomendaciones['basado_en_compras'] as $producto)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="card h-100 border-0 shadow-sm hover-elevate bg-dark">
                                            <div class="position-relative">
                                                <img src="{{ $producto->imagen_url }}" class="card-img-top" alt="{{ $producto->nombre }}" style="height: 180px; object-fit: cover;">
                                                <button class="btn btn-sm btn-light rounded-circle position-absolute top-0 end-0 m-2 shadow-sm">
                                                    <i class="far fa-heart text-danger"></i>
                                                </button>
                                            </div>
                                            <div class="card-body p-3">
                                                <h6 class="card-title text-white text-truncate mb-1">{{ $producto->nombre }}</h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold text-primary">${{ number_format($producto->precio, 2) }}</span>
                                                    <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-sm btn-outline-light rounded-pill px-3">Ver</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar (Notifications & Wishlist) -->
                <div class="col-lg-4">
                    <!-- Notifications -->
                    <div class="card border-0 shadow-sm mb-4" style="background-color: #1e293b;">
                        <div class="card-header border-0 bg-transparent py-3">
                            <h5 class="mb-0 text-white fw-bold">Notificaciones Recientes</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush rounded-bottom">
                                @forelse($notificacionesRecientes as $notificacion)
                                    <div class="list-group-item bg-transparent border-secondary border-opacity-25 py-3 d-flex gap-3 align-items-start">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle bg-dark p-2 text-center" style="width: 40px; height: 40px;">
                                                <i class="{{ $notificacion->obtenerIcono() }} text-{{ $notificacion->obtenerColor() }}"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                                <h6 class="mb-0 text-white small fw-bold">{{ $notificacion->titulo }}</h6>
                                                <small class="text-muted" style="font-size: 0.75rem">{{ $notificacion->created_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-0 text-muted small lh-sm">{{ $notificacion->mensaje }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted">
                                        <i class="far fa-bell fa-2x mb-2 opacity-50"></i>
                                        <p class="mb-0 small">No tienes notificaciones nuevas</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Wishlist Preview -->
                    <div class="card border-0 shadow-sm" style="background-color: #1e293b;">
                        <div class="card-header border-0 bg-transparent py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-white fw-bold">Tu Wishlist</h5>
                            <a href="#" class="text-primary text-decoration-none small fw-bold">Ver todo</a>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                @forelse($wishlistProductos as $item)
                                    <div class="col-4">
                                        <a href="{{ route('productos.show', $item->producto->id) }}" class="d-block position-relative rounded overflow-hidden shadow-sm ratio ratio-1x1 border border-secondary border-opacity-25">
                                            <img src="{{ $item->producto->imagen_url }}" class="img-fluid position-absolute top-0 start-0 w-100 h-100" style="object-fit: cover;" alt="{{ $item->producto->nombre }}">
                                        </a>
                                    </div>
                                @empty
                                    <div class="col-12 text-center py-4 text-muted">
                                        <p class="mb-2 small">Tu lista de deseos está vacía</p>
                                        <a href="{{ route('home') }}" class="btn btn-sm btn-outline-light rounded-pill">Explorar productos</a>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-elevate {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-elevate:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2) !important;
    }
    .card {
        border-radius: 1rem;
    }
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #0f172a; 
    }
    ::-webkit-scrollbar-thumb {
        background: #334155; 
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #475569; 
    }
</style>
@endsection
