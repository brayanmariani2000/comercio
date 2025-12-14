@extends('layouts.app')

@section('title', 'Panel de Vendedor | MONAGAS.TECH')

@push('styles')
<style>
    /* Variables - Premium Gold Theme */
    :root {
        --primary-gold: #D4AF37;
        --secondary-gold: #FFD700;
        --dark-bg: #121212;
        --card-bg: rgba(30, 30, 30, 0.7);
        --text-white: #FFFFFF;
        --text-muted: #C0C0C0;
        --success: #28a745;
        --warning: #ffc107;
        --danger: #dc3545;
    }

    .vendor-dashboard {
        min-height: calc(100vh - 100px);
        padding: 30px 0;
    }

    /* Welcome Header */
    .welcome-banner {
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(0, 0, 0, 0.4));
        border-left: 4px solid var(--primary-gold);
        padding: 25px;
        border-radius: 8px;
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }

    .welcome-title {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: var(--text-white);
        margin-bottom: 5px;
    }

    .welcome-subtitle {
        color: var(--text-muted);
        font-family: 'Montserrat', sans-serif;
    }

    /* Stat Cards */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: var(--card-bg);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 15px;
        padding: 20px;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        border-color: rgba(212, 175, 55, 0.3);
    }

    .stat-label {
        font-size: 0.9rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-white);
        margin: 10px 0;
        font-family: 'Montserrat', sans-serif;
    }

    .stat-meta {
        font-size: 0.8rem;
        color: var(--primary-gold);
    }

    /* Sections */
    .section-title {
        font-size: 1.5rem;
        color: var(--text-white);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title::after {
        content: '';
        height: 1px;
        background: var(--primary-gold);
        flex-grow: 1;
        opacity: 0.3;
    }

    /* Tables */
    .table-responsive {
        background: var(--card-bg);
        border-radius: 15px;
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .table {
        color: var(--text-muted);
        margin-bottom: 0;
    }

    .table th {
        border-top: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--primary-gold);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        padding: 15px;
    }

    .table td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        padding: 15px;
        vertical-align: middle;
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    .product-img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 8px;
    }

    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-success { background: rgba(40, 167, 69, 0.15); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.2); }
    .badge-warning { background: rgba(255, 193, 7, 0.15); color: #ffc107; border: 1px solid rgba(255, 193, 7, 0.2); }
    .badge-danger { background: rgba(220, 53, 69, 0.15); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.2); }

    .btn-action {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--text-muted);
        padding: 5px 10px;
        border-radius: 5px;
        transition: all 0.3s;
    }

    .btn-action:hover {
        border-color: var(--primary-gold);
        color: var(--primary-gold);
    }
</style>
@endpush

@section('content')
<div class="vendor-dashboard container">
    <!-- Welcome -->
    <div class="welcome-banner d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="welcome-title">Bienvenido, {{ $vendedor->nombre_comercial }}</h1>
            <p class="welcome-subtitle mb-0">Resumen de tu actividad comercial este mes.</p>
        </div>
        <a href="{{ route('vendedor.productos.create') }}" class="btn btn-submit" style="background: var(--primary-gold); color: #000; font-weight: bold; border-radius: 25px; padding: 10px 25px; text-decoration: none;">
            + Subir Producto
        </a>
    </div>

    <!-- Stats -->
    <div class="stats-container">
        <!-- Ventas -->
        <div class="stat-card">
            <div class="stat-label">Ventas del Mes</div>
            <div class="stat-value">${{ number_format($estadisticas['ventas_total'], 2) }}</div>
            <div class="stat-meta">{{ $estadisticas['pedidos_mes'] }} pedidos procesados</div>
        </div>

        <!-- Productos -->
        <div class="stat-card">
            <div class="stat-label">Productos Activos</div>
            <div class="stat-value">{{ $estadisticas['productos_activos'] }}</div>
            <div class="stat-meta">de {{ $estadisticas['total_productos'] }} en total</div>
        </div>

        <!-- Calificación -->
        <div class="stat-card">
            <div class="stat-label">Reputación</div>
            <div class="stat-value">{{ number_format($estadisticas['calificacion'], 1) }}</div>
            <div class="stat-meta">
                @for($i = 1; $i <= 5; $i++)
                    <span style="color: {{ $i <= $estadisticas['calificacion'] ? 'var(--secondary-gold)' : '#444' }}">★</span>
                @endfor
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pedidos Recientes -->
        <div class="col-lg-8 mb-4">
            <h2 class="section-title">Pedidos Recientes</h2>
            <div class="table-responsive">
                @if($pedidosRecientes->count() > 0)
                <table class="table">
                    <thead>
                        <tr>
                            <th>Orden #</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedidosRecientes as $pedido)
                        <tr>
                            <td>#{{ $pedido->id }}</td>
                            <td>{{ $pedido->user->name }}</td>
                            <td style="color: var(--secondary-gold); font-weight: 600;">${{ number_format($pedido->total, 2) }}</td>
                            <td>{{ $pedido->created_at->format('d M') }}</td>
                            <td>
                                <span class="status-badge {{ $pedido->estado_pedido == 'entregado' ? 'badge-success' : ($pedido->estado_pedido == 'cancelado' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ ucfirst($pedido->estado_pedido) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('comprador.pedidos.show', $pedido->id) }}" class="btn-action">Ver</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center py-4 text-muted">No tienes pedidos recientes.</div>
                @endif
            </div>
        </div>

        <!-- Alertas de Stock -->
        <div class="col-lg-4 mb-4">
            <h2 class="section-title">Alertas de Stock</h2>
            <div class="table-responsive">
                @if($productosStockBajo->count() > 0)
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Stock</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productosStockBajo as $producto)
                        <tr>
                            <td class="d-flex align-items-center gap-2">
                                <img src="{{ $producto->imagen_url }}" alt="" class="product-img">
                                <span class="text-truncate" style="max-width: 150px;">{{ $producto->nombre }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-danger fw-bold">{{ $producto->stock }}</span> / {{ $producto->stock_minimo }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('productos.show', $producto->id) }}" class="btn-action">Ir</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center py-4 text-muted">
                    <span style="font-size: 2rem; display: block; margin-bottom: 10px;">✓</span>
                    Tu inventario está en orden.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
