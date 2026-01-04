@extends('layouts.app')

@section('title', 'Centro de Control Exclusivo | MONAGAS.TECH')
@push('styles')
<style>
    /* Variables para el dashboard elegante */
/* Variables mejoradas para el dashboard */
:root {
    --primary-gold: #D4AF37;
    --secondary-gold: #FFD700;
    --dark-charcoal: #121212;
    --deep-navy: #0A1931;
    --light-charcoal: #1E1E1E;
    --crystal-white: #FFFFFF;
    --silver: #C0C0C0;
    --platinum: #E5E4E2;
    --success-green: #28a745;
    --warning-orange: #ffc107;
    --danger-red: #dc3545;
    --info-blue: #17a2b8;
    --glass-bg: rgba(30, 30, 30, 0.7);
    --glass-white: rgba(255, 255, 255, 0.1);
    --shadow-elegant: 0 20px 60px rgba(0, 0, 0, 0.3);
    --shadow-subtle: 0 10px 30px rgba(0, 0, 0, 0.2);
    --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    --transition-fast: all 0.2s ease;
}

/* Dashboard Container optimizado */
.dashboard-container {
    min-height: calc(100vh - 120px);
    position: relative;
    z-index: 1;
    padding-bottom: 80px;
}

/* Header más sofisticado */
.dashboard-header {
    background: linear-gradient(135deg, 
        rgba(30, 30, 30, 0.95) 0%,
        rgba(40, 40, 40, 0.95) 100%);
    backdrop-filter: blur(25px);
    border: 1px solid rgba(212, 175, 55, 0.3);
    border-radius: 20px;
    padding: 35px 40px;
    margin-bottom: 50px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-elegant);
    animation: borderGlow 3s infinite alternate;
}

.dashboard-header::before {
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

.admin-title {
    font-family: 'Playfair Display', serif;
    font-size: 2.8rem;
    color: var(--crystal-white);
    margin-bottom: 12px;
    position: relative;
    display: inline-block;
    text-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.admin-title::after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 0;
    width: 100px;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-gold), transparent);
    border-radius: 2px;
}

.admin-subtitle {
    font-family: 'Montserrat', sans-serif;
    color: var(--silver);
    font-size: 1.15rem;
    letter-spacing: 1.2px;
    line-height: 1.6;
}

/* Stats Grid mejorado */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
    margin-bottom: 50px;
}

.stat-card {
    background: linear-gradient(145deg, 
        rgba(30, 30, 30, 0.95), 
        rgba(25, 25, 25, 0.95));
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 30px;
    position: relative;
    overflow: hidden;
    transition: var(--transition-smooth);
    display: flex;
    flex-direction: column;
    min-height: 220px;
}

.stat-card::before {
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

.stat-card:hover {
    transform: translateY(-12px) scale(1.02);
    border-color: rgba(212, 175, 55, 0.4);
    box-shadow: 0 20px 40px rgba(212, 175, 55, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, 
        rgba(212, 175, 55, 0.15), 
        rgba(255, 215, 0, 0.1));
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 25px;
    border: 1px solid rgba(212, 175, 55, 0.2);
    transition: var(--transition-smooth);
}

.stat-card:hover .stat-icon {
    background: linear-gradient(135deg, 
        rgba(212, 175, 55, 0.25), 
        rgba(255, 215, 0, 0.2));
    transform: rotate(5deg) scale(1.1);
}

.stat-icon svg {
    width: 28px;
    height: 28px;
    color: var(--primary-gold);
    transition: var(--transition-smooth);
}

.stat-value {
    font-family: 'Montserrat', sans-serif;
    font-size: 2.8rem;
    font-weight: 700;
    color: var(--crystal-white);
    line-height: 1;
    margin-bottom: 8px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Tablas más elegantes */
.data-table-container {
    background: linear-gradient(145deg, 
        rgba(30, 30, 30, 0.95), 
        rgba(25, 25, 25, 0.95));
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 40px;
    box-shadow: var(--shadow-elegant);
}

.table-header {
    padding: 30px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: linear-gradient(90deg, 
        rgba(40, 40, 40, 0.6) 0%,
        rgba(30, 30, 30, 0.6) 100%);
    position: relative;
}

.table-header::before {
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

.table-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--crystal-white);
    display: flex;
    align-items: center;
    gap: 12px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.table-title svg {
    width: 24px;
    height: 24px;
    color: var(--primary-gold);
    filter: drop-shadow(0 2px 4px rgba(212, 175, 55, 0.3));
}

/* Botones de acción mejorados */
.action-buttons {
    display: flex;
    gap: 10px;
}

.btn-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid;
    cursor: pointer;
    transition: var(--transition-smooth);
    background: transparent;
    position: relative;
    overflow: hidden;
}

.btn-icon::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: currentColor;
    transform: translate(-50%, -50%);
    transition: width 0.3s ease, height 0.3s ease;
    opacity: 0.1;
}

.btn-icon:hover::before {
    width: 100%;
    height: 100%;
}

.btn-icon:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}

/* Timeline más sofisticado */
.timeline-item {
    position: relative;
    padding-left: 30px;
    margin-bottom: 25px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: -25px;
    width: 2px;
    background: linear-gradient(to bottom, 
        rgba(212, 175, 55, 0.3),
        rgba(212, 175, 55, 0.1));
}

.timeline-dot {
    position: absolute;
    left: -8px;
    top: 0;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: linear-gradient(135deg, 
        var(--primary-gold), 
        var(--secondary-gold));
    border: 3px solid var(--dark-charcoal);
    box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
    animation: pulseDot 2s infinite;
}

@keyframes pulseDot {
    0%, 100% { box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2); }
    50% { box-shadow: 0 0 0 6px rgba(212, 175, 55, 0.1); }
}

/* Grid de contenido principal mejorado */
.main-content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
    margin-bottom: 60px;
}

/* Scrollbar premium */
.activity-timeline::-webkit-scrollbar {
    width: 8px;
}

.activity-timeline::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 10px;
}

.activity-timeline::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, 
        var(--primary-gold), 
        var(--secondary-gold));
    border-radius: 10px;
    border: 2px solid var(--dark-charcoal);
}

.activity-timeline::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, 
        var(--secondary-gold), 
        var(--primary-gold));
}

/* Efectos de hover para filas de tabla */
.data-table tbody tr {
    transition: background-color 0.3s ease;
    position: relative;
}

.data-table tbody tr::after {
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

.data-table tbody tr:hover {
    background: linear-gradient(90deg, 
        rgba(255, 255, 255, 0.05) 0%,
        rgba(212, 175, 55, 0.02) 50%,
        rgba(255, 255, 255, 0.05) 100%);
}

.data-table tbody tr:hover::after {
    opacity: 1;
}

/* Estados vacíos más elegantes */
.empty-state {
    text-align: center;
    padding: 60px 30px;
    color: var(--silver);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 25px;
    opacity: 0.2;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
}

.empty-text {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.1rem;
    letter-spacing: 1px;
    max-width: 300px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Animaciones adicionales */
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

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

@keyframes borderGlow {
    0%, 100% { 
        border-color: rgba(212, 175, 55, 0.3);
        box-shadow: 0 20px 60px rgba(212, 175, 55, 0.1);
    }
    50% { 
        border-color: rgba(212, 175, 55, 0.5);
        box-shadow: 0 20px 60px rgba(212, 175, 55, 0.2);
    }
}

.fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
}

/* Efecto de vidrio mejorado */
.glass-effect {
    background: linear-gradient(135deg, 
        rgba(255, 255, 255, 0.05) 0%,
        rgba(255, 255, 255, 0.02) 100%);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Responsive mejorado */
@media (max-width: 1400px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
    }
}

@media (max-width: 992px) {
    .main-content-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .dashboard-header {
        padding: 25px;
    }
    
    .admin-title {
        font-size: 2.2rem;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .stat-card {
        min-height: 180px;
        padding: 25px;
    }
    
    .stat-value {
        font-size: 2.2rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 15px;
    }
}

@media (max-width: 576px) {
    .dashboard-header {
        margin-bottom: 30px;
        padding: 20px;
    }
    
    .admin-title {
        font-size: 1.8rem;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 8px;
    }
    
    .btn-icon {
        width: 36px;
        height: 36px;
    }
}
</style>
@endpush

@section('content')
<div class="dashboard-container fade-in">
    <!-- Header del Dashboard -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="admin-title">Centro de Control Exclusivo</h1>
                <p class="admin-subtitle">
                    Administrador: <span class="text-gold">{{ auth()->user()->name }}</span> | 
                    Último acceso: {{ now()->format('d M Y, H:i') }}
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="admin-status">
                    <div class="status-dot"></div>
                    <span>SISTEMA ACTIVO</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cards de Estadísticas -->
    <div class="stats-grid">
        <!-- Usuarios -->
        <div class="stat-card">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-value">{{ $estadisticas['usuarios']['total'] ?? 0 }}</div>
            <div class="stat-label">Usuarios Registrados</div>
            <div class="stat-trend trend-up">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M4 12L12 4M12 4H6M12 4V8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>12% este mes</span>
            </div>
        </div>
        
        <!-- Vendedores -->
        <div class="stat-card">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </div>
            <div class="stat-value">{{ $estadisticas['vendedores']['total'] ?? 0 }}</div>
            <div class="stat-label">Vendedores Activos</div>
            <div class="stat-trend trend-up">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M4 12L12 4M12 4H6M12 4V8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>8% este mes</span>
            </div>
        </div>
        
        <!-- Productos -->
        <div class="stat-card">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                    <path d="M3 9h18"/>
                    <path d="M9 21V9"/>
                </svg>
            </div>
            <div class="stat-value">{{ $estadisticas['productos']['total'] ?? 0 }}</div>
            <div class="stat-label">Productos en Catálogo</div>
            <div class="stat-trend trend-up">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M4 12L12 4M12 4H6M12 4V8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>24% este mes</span>
            </div>
        </div>
        
        <!-- Pedidos -->
        <div class="stat-card">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/>
                    <circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
            </div>
            <div class="stat-value">{{ $estadisticas['pedidos']['total'] ?? 0 }}</div>
            <div class="stat-label">Órdenes Procesadas</div>
            <div class="stat-trend trend-up">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M4 12L12 4M12 4H6M12 4V8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>18% este mes</span>
            </div>
        </div>
    </div>
    
    <!-- Contenido Principal -->
    <div class="main-content-grid">
        <!-- Columna Izquierda -->
        <div>
            <!-- Vendedores Pendientes -->
            <div class="data-table-container">
                <div class="table-header">
                    <h3 class="table-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="8.5" cy="7" r="4"/>
                            <path d="M20 8v6"/>
                            <path d="M23 11h-6"/>
                        </svg>
                        Solicitudes de Vendedores Pendientes
                    </h3>
                </div>
                
                @if($vendedores_pendientes->count() > 0)
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>RIF</th>
                                <th>Fecha Solicitud</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendedores_pendientes as $vendedor)
                            <tr>
                                <td>
                                    <div class="user-avatar-cell">
                                        <div class="user-avatar-small">
                                            {{ strtoupper(substr($vendedor->nombre_comercial, 0, 1)) }}
                                        </div>
                                        <div class="user-info">
                                            <span class="user-name">{{ $vendedor->nombre_comercial }}</span>
                                            <span class="user-email">{{ $vendedor->user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-gold">{{ $vendedor->rif }}</span>
                                </td>
                                <td>
                                    <span class="text-silver">{{ $vendedor->created_at->format('d M Y') }}</span>
                                    <br>
                                    <small class="text-muted">{{ $vendedor->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <span class="status-badge badge-pending">Pendiente</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <form action="{{ route('admin.vendedores.aprobar', $vendedor->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-icon btn-approve" title="Aprobar">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M13 4L6 12L3 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.vendedores.rechazar', $vendedor->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-icon btn-reject" title="Rechazar">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <button class="btn-icon btn-view" title="Ver detalles">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M1 8C1 8 3.90909 3 9 3C14.0909 3 17 8 17 8C17 8 14.0909 13 9 13C3.90909 13 1 8 1 8Z" stroke="currentColor" stroke-width="1.5"/>
                                                <circle cx="9" cy="8" r="2" stroke="currentColor" stroke-width="1.5"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <p class="empty-text">No hay solicitudes pendientes de aprobación</p>
                </div>
                @endif
            </div>
            
            <!-- Productos Pendientes -->
            <div class="data-table-container">
                <div class="table-header">
                    <h3 class="table-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <path d="M3 9h18"/>
                            <path d="M9 21V9"/>
                        </svg>
                        Productos Pendientes de Aprobación
                    </h3>
                </div>
                
                @if($productos_pendientes->count() > 0)
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Categoría</th>
                                <th>Vendedor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productos_pendientes as $producto)
                            <tr>
                                <td>
                                    <div class="user-avatar-cell">
                                        @if($producto->imagenes->first())
                                        <img src="{{ $producto->imagenes->first()->url }}" 
                                             alt="{{ $producto->nombre }}"
                                             class="user-avatar-small"
                                             style="object-fit: cover; border: none;">
                                        @else
                                        <div class="user-avatar-small">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                                <polyline points="21 15 16 10 5 21"/>
                                            </svg>
                                        </div>
                                        @endif
                                        <div class="user-info">
                                            <span class="user-name">{{ Str::limit($producto->nombre, 30) }}</span>
                                            <span class="user-email">{{ Str::limit($producto->descripcion, 40) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-gold" style="font-weight: 600;">${{ number_format($producto->precio, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-silver">{{ $producto->categoria->nombre ?? 'Sin categoría' }}</span>
                                </td>
                                <td>
                                    <span class="text-silver">{{ $producto->vendedor->nombre_comercial ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <form action="{{ route('admin.productos.aprobar', $producto->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="accion" value="aprobar">
                                            <button type="submit" class="btn-icon btn-approve" title="Aprobar producto">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M13 4L6 12L3 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <button class="btn-icon btn-view" title="Vista previa">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path d="M1 8C1 8 3.90909 3 9 3C14.0909 3 17 8 17 8C17 8 14.0909 13 9 13C3.90909 13 1 8 1 8Z" stroke="currentColor" stroke-width="1.5"/>
                                                <circle cx="9" cy="8" r="2" stroke="currentColor" stroke-width="1.5"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <p class="empty-text">Todos los productos están aprobados y publicados</p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Columna Derecha -->
        <div class="side-panel">
            <!-- Actividad Reciente -->
            <div class="data-table-container">
                <div class="table-header">
                    <h3 class="table-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        Actividad Reciente del Sistema
                    </h3>
                </div>
                
                <div class="activity-timeline">
                    @forelse($actividad_reciente as $actividad)
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <span class="timeline-time">{{ $actividad->created_at->format('H:i') }}</span>
                            <p class="timeline-text">{{ $actividad->descripcion }}</p>
                            <div class="timeline-meta">
                                <span class="meta-badge">{{ $actividad->entidad_tipo }}</span>
                                <span class="text-muted" style="font-size: 0.75rem;">{{ $actividad->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <p class="empty-text">No hay actividad reciente</p>
                    </div>
                    @endforelse
                </div>
            </div>
            
            <!-- Acciones Rápidas -->
            <div class="data-table-container">
                <div class="table-header">
                    <h3 class="table-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        Acciones Rápidas
                    </h3>
                </div>
                
                <div style="padding: 25px;">
                    <div class="row g-3">
                        <div class="col-6">
                            <button class="btn w-100" style="
                                background: rgba(212, 175, 55, 0.1);
                                border: 1px solid rgba(212, 175, 55, 0.3);
                                color: var(--primary-gold);
                                padding: 12px;
                                border-radius: 10px;
                                transition: var(--transition-smooth);
                            ">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-1">
                                    <path d="M12 20v-6M6 20v-4M18 20v-8"/>
                                </svg>
                                <br>
                                <small>Reportes</small>
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn w-100" style="
                                background: rgba(40, 167, 69, 0.1);
                                border: 1px solid rgba(40, 167, 69, 0.3);
                                color: var(--success-green);
                                padding: 12px;
                                border-radius: 10px;
                                transition: var(--transition-smooth);
                            ">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-1">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                                </svg>
                                <br>
                                <small>Backup</small>
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn w-100" style="
                                background: rgba(23, 162, 184, 0.1);
                                border: 1px solid rgba(23, 162, 184, 0.3);
                                color: var(--info-blue);
                                padding: 12px;
                                border-radius: 10px;
                                transition: var(--transition-smooth);
                            ">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-1">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                                <br>
                                <small>Usuarios</small>
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn w-100" style="
                                background: rgba(108, 117, 125, 0.1);
                                border: 1px solid rgba(108, 117, 125, 0.3);
                                color: #6c757d;
                                padding: 12px;
                                border-radius: 10px;
                                transition: var(--transition-smooth);
                            ">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-1">
                                    <path d="M18 20a6 6 0 0 0-12 0"/>
                                    <circle cx="12" cy="10" r="4"/>
                                    <circle cx="12" cy="12" r="10"/>
                                </svg>
                                <br>
                                <small>Config.</small>
                            </button>
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
        // Animación de contadores
        const counters = document.querySelectorAll('.stat-value');
        counters.forEach(counter => {
            const target = parseInt(counter.textContent);
            const duration = 2000; // 2 segundos
            const step = target / (duration / 16); // 60fps
            let current = 0;
            
            const updateCounter = () => {
                current += step;
                if (current < target) {
                    counter.textContent = Math.floor(current);
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target;
                }
            };
            
            // Iniciar animación cuando el elemento es visible
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        updateCounter();
                        observer.unobserve(entry.target);
                    }
                });
            });
            
            observer.observe(counter);
        });
        
        // Confirmación para acciones
        document.querySelectorAll('.btn-reject').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('¿Está seguro de que desea rechazar esta solicitud?')) {
                    e.preventDefault();
                }
            });
        });
        
        // Efectos hover mejorados
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                const icon = this.querySelector('.stat-icon svg');
                if (icon) {
                    icon.style.transform = 'scale(1.2)';
                    icon.style.transition = 'transform 0.3s ease';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                const icon = this.querySelector('.stat-icon svg');
                if (icon) {
                    icon.style.transform = 'scale(1)';
                }
            });
        });
        
        // Actualizar hora en tiempo real
        function updateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            document.querySelector('.admin-subtitle').innerHTML = `
                Administrador: <span class="text-gold">{{ auth()->user()->name }}</span> | 
                Último acceso: ${now.toLocaleDateString('es-ES', options)}
            `;
        }
        
        // Actualizar cada minuto
        setInterval(updateTime, 60000);
        
        // Scroll suave para timeline
        const timeline = document.querySelector('.activity-timeline');
        if (timeline) {
            let isScrolling = false;
            
            timeline.addEventListener('wheel', function(e) {
                if (!isScrolling) {
                    isScrolling = true;
                    this.scrollBy({
                        top: e.deltaY > 0 ? 100 : -100,
                        behavior: 'smooth'
                    });
                    setTimeout(() => isScrolling = false, 300);
                }
                e.preventDefault();
            });
        }
        
        // Notificación de nuevas actividades (simulación)
        function checkNewActivity() {
            // En una implementación real, aquí harías una petición AJAX
            console.log('Verificando nuevas actividades...');
        }
        
        // Verificar cada 30 segundos
        setInterval(checkNewActivity, 30000);
    });
</script>
@endpush
@endsection