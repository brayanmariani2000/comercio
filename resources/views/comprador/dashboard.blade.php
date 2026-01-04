@extends('layouts.app')

@section('title', 'Dashboard del Comprador | MONAGAS.TECH')

@push('styles')
<style>
    /* Variables del dashboard del comprador */
    :root {
        --primary-gold: #D4AF37;
        --secondary-gold: #FFD700;
        --deep-navy: #0A1931;
        --dark-charcoal: #121212;
        --midnight-blue: #1e293b;
        --light-slate: #334155;
        --crystal-white: #FFFFFF;
        --silver: #C0C0C0;
        --platinum: #E5E4E2;
        --success-green: #10B981;
        --warning-orange: #F59E0B;
        --danger-red: #EF4444;
        --info-blue: #3B82F6;
        --glass-bg: rgba(30, 41, 59, 0.85);
        --shadow-elegant: 0 20px 60px rgba(0, 0, 0, 0.4);
        --shadow-subtle: 0 10px 30px rgba(0, 0, 0, 0.25);
        --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-fast: all 0.2s ease;
    }

    /* Fondo general */
    body {
        background: linear-gradient(135deg, 
            var(--dark-charcoal) 0%, 
            var(--deep-navy) 100%);
        min-height: 100vh;
    }

    /* Dashboard Container */
    .dashboard-container {
        min-height: 100vh;
        position: relative;
        z-index: 1;
    }

    /* Welcome Header elegante */
    .welcome-header {
        background: linear-gradient(135deg, 
            rgba(30, 41, 59, 0.95) 0%,
            rgba(15, 23, 42, 0.95) 100%);
        backdrop-filter: blur(25px);
        border: 1px solid rgba(212, 175, 55, 0.3);
        border-radius: 20px;
        padding: 35px 40px;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-elegant);
    }

    .welcome-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, 
            transparent,
            var(--primary-gold),
            var(--secondary-gold),
            var(--primary-gold),
            transparent);
        animation: shimmer 3s infinite linear;
        background-size: 200% 100%;
    }

    .welcome-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        color: var(--crystal-white);
        margin-bottom: 10px;
        position: relative;
        display: inline-block;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
    }

    .welcome-subtitle {
        font-family: 'Montserrat', sans-serif;
        color: var(--silver);
        font-size: 1.1rem;
        letter-spacing: 1px;
        line-height: 1.6;
    }

    /* Stats Cards elegantes */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
        margin-bottom: 40px;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .stat-card-elegant {
        background: linear-gradient(145deg, 
            rgba(30, 41, 59, 0.95), 
            rgba(15, 23, 42, 0.95));
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        padding: 30px;
        position: relative;
        overflow: hidden;
        transition: var(--transition-smooth);
        min-height: 180px;
    }

    .stat-card-elegant::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, 
            var(--primary-gold), 
            var(--secondary-gold),
            var(--primary-gold));
        background-size: 200% 100%;
        animation: shimmer 4s infinite linear;
    }

    .stat-card-elegant:hover {
        transform: translateY(-10px) scale(1.02);
        border-color: rgba(212, 175, 55, 0.4);
        box-shadow: 0 20px 40px rgba(212, 175, 55, 0.15);
    }

    .stat-icon-elegant {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, 
            rgba(212, 175, 55, 0.15), 
            rgba(255, 215, 0, 0.1));
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        border: 1px solid rgba(212, 175, 55, 0.2);
        transition: var(--transition-smooth);
    }

    .stat-card-elegant:hover .stat-icon-elegant {
        background: linear-gradient(135deg, 
            rgba(212, 175, 55, 0.25), 
            rgba(255, 215, 0, 0.2));
        transform: rotate(5deg) scale(1.1);
    }

    .stat-icon-elegant i {
        font-size: 1.5rem;
        color: var(--primary-gold);
        transition: var(--transition-smooth);
    }

    .stat-value-elegant {
        font-family: 'Montserrat', sans-serif;
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--crystal-white);
        line-height: 1;
        margin-bottom: 8px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .stat-label-elegant {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.85rem;
        color: var(--silver);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }

    .stat-trend-elegant {
        font-size: 0.85rem;
        margin-top: auto;
    }

    /* Cards de contenido */
    .content-card {
        background: linear-gradient(145deg, 
            rgba(30, 41, 59, 0.95), 
            rgba(15, 23, 42, 0.95));
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: var(--shadow-elegant);
    }

    .card-header-elegant {
        padding: 25px 30px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: linear-gradient(90deg, 
            rgba(40, 40, 40, 0.6) 0%,
            rgba(30, 30, 30, 0.6) 100%);
        position: relative;
    }

    .card-header-elegant::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 30px;
        right: 30px;
        height: 1px;
        background: linear-gradient(90deg, 
            transparent,
            rgba(212, 175, 55, 0.5),
            transparent);
    }

    .card-title-elegant {
        font-family: 'Montserrat', sans-serif;
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--crystal-white);
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    /* Tablas elegantes */
    .elegant-table {
        width: 100%;
        border-collapse: collapse;
        color: var(--crystal-white);
    }

    .elegant-table thead {
        background: rgba(255, 255, 255, 0.05);
    }

    .elegant-table th {
        padding: 20px;
        text-align: left;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--silver);
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .elegant-table td {
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        vertical-align: middle;
    }

    .elegant-table tbody tr {
        transition: background-color 0.3s ease;
        position: relative;
    }

    .elegant-table tbody tr::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 1px;
        background: linear-gradient(90deg, 
            transparent,
            rgba(212, 175, 55, 0.1),
            transparent);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .elegant-table tbody tr:hover {
        background: linear-gradient(90deg, 
            rgba(255, 255, 255, 0.05) 0%,
            rgba(212, 175, 55, 0.02) 50%,
            rgba(255, 255, 255, 0.05) 100%);
    }

    .elegant-table tbody tr:hover::after {
        opacity: 1;
    }

    /* Badges elegantes */
    .badge-elegant {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 8px 16px;
        border-radius: 20px;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.85rem;
        font-weight: 500;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .badge-pending {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning-orange);
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .badge-paid {
        background: rgba(59, 130, 246, 0.1);
        color: var(--info-blue);
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    .badge-shipped {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-green);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .badge-delivered {
        background: rgba(34, 197, 94, 0.1);
        color: #22C55E;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .badge-cancelled {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger-red);
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    /* Botones elegantes */
    .btn-elegant {
        background: transparent;
        color: var(--primary-gold);
        border: 1px solid var(--primary-gold);
        padding: 10px 24px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        font-size: 0.85rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        position: relative;
        overflow: hidden;
        border-radius: 12px;
    }

    .btn-elegant::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: var(--primary-gold);
        transform: translate(-50%, -50%);
        transition: width 0.3s ease, height 0.3s ease;
        opacity: 0.1;
    }

    .btn-elegant:hover {
        color: var(--dark-charcoal);
        letter-spacing: 2px;
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(212, 175, 55, 0.2);
    }

    .btn-elegant:hover::before {
        width: 300%;
        height: 300%;
    }

    .btn-elegant-outline {
        background: transparent;
        color: var(--crystal-white);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 10px 24px;
        border-radius: 12px;
        font-size: 0.85rem;
        transition: var(--transition-smooth);
    }

    .btn-elegant-outline:hover {
        border-color: var(--primary-gold);
        color: var(--primary-gold);
        transform: translateY(-2px);
    }

    /* Product cards elegantes */
    .product-card-elegant {
        background: linear-gradient(145deg, 
            rgba(30, 41, 59, 0.95), 
            rgba(15, 23, 42, 0.95));
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 16px;
        overflow: hidden;
        transition: var(--transition-smooth);
        height: 100%;
    }

    .product-card-elegant:hover {
        transform: translateY(-10px);
        border-color: rgba(212, 175, 55, 0.4);
        box-shadow: 0 20px 40px rgba(212, 175, 55, 0.15);
    }

    .product-image-container {
        position: relative;
        overflow: hidden;
        height: 180px;
        background: var(--dark-charcoal);
    }

    .product-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.8s ease;
    }

    .product-card-elegant:hover .product-image-container img {
        transform: scale(1.05);
    }

    /* Notificaciones elegantes */
    .notification-item {
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        transition: var(--transition-fast);
    }

    .notification-item:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    .notification-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, 
            rgba(212, 175, 55, 0.15), 
            rgba(255, 215, 0, 0.1));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(212, 175, 55, 0.2);
    }

    .notification-icon i {
        font-size: 1.2rem;
        color: var(--primary-gold);
    }

    /* Wishlist preview */
    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .wishlist-item {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 1/1;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: var(--transition-fast);
    }

    .wishlist-item:hover {
        transform: scale(1.05);
        border-color: rgba(212, 175, 55, 0.3);
    }

    .wishlist-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Estados vacíos */
    .empty-state-elegant {
        text-align: center;
        padding: 60px 30px;
        color: var(--silver);
    }

    .empty-icon-elegant {
        font-size: 3rem;
        margin-bottom: 20px;
        opacity: 0.2;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
    }

    .empty-text-elegant {
        font-family: 'Montserrat', sans-serif;
        font-size: 1rem;
        letter-spacing: 1px;
        max-width: 300px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* Animaciones */
    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(40px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.8s ease-out forwards;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .welcome-header {
            padding: 25px;
        }
        
        .welcome-title {
            font-size: 2rem;
        }
        
        .stat-value-elegant {
            font-size: 1.8rem;
        }
        
        .elegant-table th,
        .elegant-table td {
            padding: 15px;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .stat-card-elegant {
            min-height: 160px;
            padding: 25px;
        }
        
        .wishlist-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .welcome-header {
            margin-bottom: 30px;
            padding: 20px;
        }
        
        .welcome-title {
            font-size: 1.8rem;
        }
        
        .btn-elegant,
        .btn-elegant-outline {
            padding: 8px 16px;
            font-size: 0.8rem;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container fade-in-up">
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3 col-lg-2 d-none d-md-block">
                @include('partials.comprador-sidebar')
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <!-- Welcome Section Elegante -->
                <div class="welcome-header fade-in-up">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="welcome-title">Hola, {{ auth()->user()->name }} </h1>
                            <p class="welcome-subtitle">
                                Bienvenido a tu panel de control personal. Aquí tienes el resumen de tu actividad y compras.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="d-flex gap-2 justify-content-md-end">
                                <a href="{{ route('comprador.pedidos.index') }}" class="btn-elegant-outline">
                                    <i class="fas fa-list me-2"></i>Mis Pedidos
                                </a>
                                <a href="{{ route('comprador.carrito') }}" class="btn-elegant">
                                    <i class="fas fa-shopping-cart me-2"></i>Ir al Carrito
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards Elegantes -->
                <div class="stats-grid">
                    <!-- Total Orders -->
                    <div class="stat-card-elegant">
                        <div class="stat-icon-elegant">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-value-elegant">{{ $estadisticas['compras']['total'] }}</div>
                        <div class="stat-label-elegant">Compras Totales</div>
                        <div class="stat-trend-elegant text-success">
                            <i class="fas fa-arrow-up me-1"></i>
                            {{ $estadisticas['compras']['compras_mes'] }} este mes
                        </div>
                    </div>

                    <!-- Total Spent -->
                    <div class="stat-card-elegant">
                        <div class="stat-icon-elegant">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-value-elegant">${{ number_format($estadisticas['compras']['total_monto'], 2) }}</div>
                        <div class="stat-label-elegant">Total Gastado</div>
                        <div class="stat-trend-elegant text-silver">
                            <i class="fas fa-chart-line me-1"></i>
                            Acumulado histórico
                        </div>
                    </div>

                    <!-- Pending Orders -->
                    <div class="stat-card-elegant">
                        <div class="stat-icon-elegant">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-value-elegant">{{ $estadisticas['compras']['pedidos_pendientes'] }}</div>
                        <div class="stat-label-elegant">Pedidos Pendientes</div>
                        <div class="stat-trend-elegant text-warning">
                            <i class="fas fa-truck me-1"></i>
                            En proceso de entrega
                        </div>
                    </div>

                    <!-- Wishlist -->
                    <div class="stat-card-elegant">
                        <div class="stat-icon-elegant">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-value-elegant">{{ $estadisticas['interacciones']['wishlist_items'] }}</div>
                        <div class="stat-label-elegant">En Wishlist</div>
                        <div class="stat-trend-elegant text-info">
                            <i class="fas fa-star me-1"></i>
                            Productos guardados
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Main Content Column -->
                    <div class="col-lg-8">
                        <!-- Recent Orders -->
                        <div class="content-card fade-in-up" style="animation-delay: 0.1s;">
                            <div class="card-header-elegant d-flex justify-content-between align-items-center">
                                <h5 class="card-title-elegant mb-0">Pedidos Recientes</h5>
                                <a href="{{ route('comprador.pedidos.index') }}" class="text-decoration-none text-gold small fw-bold">
                                    Ver todos <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                            <div class="table-responsive">
                                <table class="elegant-table">
                                    <thead>
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
                                                <td class="ps-4">
                                                    <div class="fw-bold text-white">#{{ $pedido->numero_pedido }}</div>
                                                    <div class="small text-silver">{{ $pedido->items->count() }} ítems</div>
                                                </td>
                                                <td class="text-silver">{{ $pedido->created_at->format('d M, Y') }}</td>
                                                <td class="fw-bold text-primary">${{ number_format($pedido->total, 2) }}</td>
                                                <td>
                                                    @php
                                                        $statusClasses = [
                                                            'pendiente' => 'badge-pending',
                                                            'pagado' => 'badge-paid',
                                                            'enviado' => 'badge-shipped',
                                                            'entregado' => 'badge-delivered',
                                                            'cancelado' => 'badge-cancelled'
                                                        ];
                                                        $badgeClass = $statusClasses[$pedido->estado_pedido] ?? 'badge-pending';
                                                    @endphp
                                                    <span class="badge-elegant {{ $badgeClass }}">
                                                        {{ ucfirst($pedido->estado_pedido) }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="{{ route('comprador.pedidos.show', $pedido->id) }}" 
                                                       class="btn-icon" 
                                                       style="width: 36px; height: 36px; border-color: rgba(255,255,255,0.3); color: var(--silver);"
                                                       title="Ver detalle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5">
                                                    <div class="empty-state-elegant py-5">
                                                        <div class="empty-icon-elegant">
                                                            <i class="fas fa-shopping-basket"></i>
                                                        </div>
                                                        <p class="empty-text-elegant">No has realizado pedidos aún.</p>
                                                        <a href="{{ route('home') }}" class="btn-elegant mt-3">
                                                            Comenzar a comprar
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Recommended Products -->
                        @if(isset($recomendaciones['basado_en_compras']) && $recomendaciones['basado_en_compras']->isNotEmpty())
                            <div class="content-card fade-in-up" style="animation-delay: 0.2s;">
                                <div class="card-header-elegant">
                                    <h5 class="card-title-elegant mb-0">Recomendado para ti</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        @foreach($recomendaciones['basado_en_compras'] as $producto)
                                            <div class="col-md-4 col-sm-6">
                                                <div class="product-card-elegant">
                                                    <div class="product-image-container">
                                                        <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}">
                                                        <button class="btn-icon position-absolute top-0 end-0 m-2" 
                                                                style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.3); color: var(--danger-red);"
                                                                title="Agregar a favoritos">
                                                            <i class="far fa-heart"></i>
                                                        </button>
                                                    </div>
                                                    <div class="p-3">
                                                        <h6 class="text-white mb-2" style="font-size: 0.95rem; min-height: 40px;">{{ $producto->nombre }}</h6>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="fw-bold text-primary">${{ number_format($producto->precio, 2) }}</span>
                                                            <a href="{{ route('productos.show', $producto->id) }}" class="btn-elegant-outline btn-sm">
                                                                Ver
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Sidebar Column -->
                    <div class="col-lg-4">
                        <!-- Notifications -->
                        <div class="content-card fade-in-up" style="animation-delay: 0.3s;">
                            <div class="card-header-elegant">
                                <h5 class="card-title-elegant mb-0">Notificaciones Recientes</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="notifications-list">
                                    @forelse($notificacionesRecientes as $notificacion)
                                        <div class="notification-item">
                                            <div class="d-flex gap-3 align-items-start">
                                                <div class="notification-icon flex-shrink-0">
                                                    <i class="{{ $notificacion->obtenerIcono() }}"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-baseline mb-1">
                                                        <h6 class="mb-0 text-white small fw-bold">{{ $notificacion->titulo }}</h6>
                                                        <small class="text-silver" style="font-size: 0.75rem">{{ $notificacion->created_at->diffForHumans() }}</small>
                                                    </div>
                                                    <p class="mb-0 text-silver small lh-sm">{{ $notificacion->mensaje }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="empty-state-elegant py-4">
                                            <div class="empty-icon-elegant">
                                                <i class="far fa-bell"></i>
                                            </div>
                                            <p class="empty-text-elegant">No tienes notificaciones nuevas</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Wishlist Preview -->
                        <div class="content-card fade-in-up" style="animation-delay: 0.4s;">
                            <div class="card-header-elegant d-flex justify-content-between align-items-center">
                                <h5 class="card-title-elegant mb-0">Tu Wishlist</h5>
                                <a href="#" class="text-decoration-none text-gold small fw-bold">
                                    Ver todo
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="wishlist-grid">
                                    @forelse($wishlistProductos as $item)
                                        <a href="{{ route('productos.show', $item->producto->id) }}" class="wishlist-item">
                                            <img src="{{ $item->producto->imagen_url }}" alt="{{ $item->producto->nombre }}">
                                        </a>
                                    @empty
                                        <div class="col-12">
                                            <div class="empty-state-elegant py-4">
                                                <p class="empty-text-elegant">Tu lista de deseos está vacía</p>
                                                <a href="{{ route('home') }}" class="btn-elegant-outline btn-sm mt-2">
                                                    Explorar productos
                                                </a>
                                            </div>
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
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animación de contadores para stats
        const counters = document.querySelectorAll('.stat-value-elegant');
        counters.forEach(counter => {
            const target = parseInt(counter.textContent.replace('$', '').replace(',', ''));
            if (!isNaN(target)) {
                const duration = 1500;
                const step = target / (duration / 16);
                let current = 0;
                
                const updateCounter = () => {
                    current += step;
                    if (current < target) {
                        counter.textContent = counter.textContent.includes('$') 
                            ? '$' + Math.floor(current).toLocaleString()
                            : Math.floor(current);
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = counter.textContent.includes('$') 
                            ? '$' + target.toLocaleString()
                            : target;
                    }
                };
                
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            updateCounter();
                            observer.unobserve(entry.target);
                        }
                    });
                });
                
                observer.observe(counter);
            }
        });
        
        // Efecto hover para product cards
        const productCards = document.querySelectorAll('.product-card-elegant');
        productCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // Actualizar hora dinámica
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-VE', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            });
            const dateString = now.toLocaleDateString('es-VE', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            const timeElement = document.querySelector('.current-time');
            if (timeElement) {
                timeElement.textContent = `${dateString} - ${timeString}`;
            }
        }
        
        updateTime();
        setInterval(updateTime, 60000);
        
        // Smooth scroll para notificaciones
        const notificationList = document.querySelector('.notifications-list');
        if (notificationList && notificationList.scrollHeight > notificationList.clientHeight) {
            notificationList.style.maxHeight = '400px';
            notificationList.style.overflowY = 'auto';
        }
    });
</script>
@endpush
@endsection