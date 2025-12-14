@extends('layouts.app')

@section('title', 'Centro de Control Exclusivo | MONAGAS.TECH')
@push('styles')
<style>
    /* Variables para el dashboard elegante */
    :root {
        --primary-gold: #D4AF37;
        --secondary-gold: #FFD700;
        --dark-charcoal: #121212;
        --light-charcoal: #1E1E1E;
        --crystal-white: #FFFFFF;
        --silver: #C0C0C0;
        --success-green: #28a745;
        --warning-orange: #ffc107;
        --danger-red: #dc3545;
        --info-blue: #17a2b8;
        --glass-bg: rgba(30, 30, 30, 0.7);
        --shadow-elegant: 0 20px 60px rgba(0, 0, 0, 0.3);
        --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Estilos específicos del dashboard */
    .dashboard-container {
        min-height: calc(100vh - 120px);
        position: relative;
        z-index: 1;
    }
    
    /* Header del dashboard */
    .dashboard-header {
        background: linear-gradient(90deg, 
            rgba(30, 30, 30, 0.8) 0%,
            rgba(40, 40, 40, 0.8) 100%);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-elegant);
    }
    
    .dashboard-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, 
            var(--primary-gold),
            var(--secondary-gold),
            var(--primary-gold));
    }
    
    .admin-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        color: var(--crystal-white);
        margin-bottom: 10px;
        position: relative;
        display: inline-block;
    }
    
    .admin-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 80px;
        height: 2px;
        background: var(--primary-gold);
    }
    
    .admin-subtitle {
        font-family: 'Montserrat', sans-serif;
        color: var(--silver);
        font-size: 1.1rem;
        letter-spacing: 1px;
    }
    
    .admin-status {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 10px 20px;
        background: rgba(212, 175, 55, 0.1);
        border: 1px solid rgba(212, 175, 55, 0.3);
        border-radius: 25px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        color: var(--primary-gold);
    }
    
    .status-dot {
        width: 10px;
        height: 10px;
        background: var(--success-green);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    /* Cards de estadísticas */
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
    
    .stat-card {
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 25px;
        position: relative;
        overflow: hidden;
        transition: var(--transition-smooth);
        display: flex;
        flex-direction: column;
    }
    
    .stat-card:hover {
        transform: translateY(-10px);
        border-color: rgba(212, 175, 55, 0.3);
        box-shadow: 0 15px 35px rgba(212, 175, 55, 0.2);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-gold), transparent);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        background: rgba(212, 175, 55, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }
    
    .stat-icon svg {
        width: 24px;
        height: 24px;
        color: var(--primary-gold);
    }
    
    .stat-value {
        font-family: 'Montserrat', sans-serif;
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--crystal-white);
        line-height: 1;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.9rem;
        color: var(--silver);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
    }
    
    .stat-trend {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.85rem;
        margin-top: auto;
    }
    
    .trend-up {
        color: var(--success-green);
    }
    
    .trend-down {
        color: var(--danger-red);
    }
    
    /* Tablas elegantes */
    .data-table-container {
        background: var(--glass-bg);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: var(--shadow-elegant);
    }
    
    .table-header {
        padding: 25px 30px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: linear-gradient(90deg, 
            rgba(40, 40, 40, 0.5) 0%,
            rgba(30, 30, 30, 0.5) 100%);
    }
    
    .table-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--crystal-white);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .table-title svg {
        width: 20px;
        height: 20px;
        color: var(--primary-gold);
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
        color: var(--crystal-white);
    }
    
    .data-table thead {
        background: rgba(255, 255, 255, 0.05);
    }
    
    .data-table th {
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
    
    .data-table td {
        padding: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        vertical-align: middle;
    }
    
    .data-table tbody tr {
        transition: background-color 0.3s ease;
    }
    
    .data-table tbody tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }
    
    .data-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    /* Avatar de usuario en tabla */
    .user-avatar-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .user-avatar-small {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        color: var(--dark-charcoal);
        font-size: 0.9rem;
    }
    
    .user-info {
        display: flex;
        flex-direction: column;
    }
    
    .user-name {
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        color: var(--crystal-white);
        font-size: 0.95rem;
    }
    
    .user-email {
        font-family: 'Montserrat', sans-serif;
        color: var(--silver);
        font-size: 0.8rem;
        margin-top: 2px;
    }
    
    /* Badges elegantes */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 20px;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.8rem;
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    
    .badge-pending {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning-orange);
        border: 1px solid rgba(255, 193, 7, 0.3);
    }
    
    .badge-active {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-green);
        border: 1px solid rgba(40, 167, 69, 0.3);
    }
    
    .badge-inactive {
        background: rgba(220, 53, 69, 0.1);
        color: var(--danger-red);
        border: 1px solid rgba(220, 53, 69, 0.3);
    }
    
    /* Botones de acción */
    .action-buttons {
        display: flex;
        gap: 8px;
    }
    
    .btn-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: var(--transition-smooth);
        background: transparent;
    }
    
    .btn-approve {
        border: 1px solid rgba(40, 167, 69, 0.3);
        color: var(--success-green);
    }
    
    .btn-approve:hover {
        background: rgba(40, 167, 69, 0.1);
        border-color: var(--success-green);
        transform: translateY(-2px);
    }
    
    .btn-reject {
        border: 1px solid rgba(220, 53, 69, 0.3);
        color: var(--danger-red);
    }
    
    .btn-reject:hover {
        background: rgba(220, 53, 69, 0.1);
        border-color: var(--danger-red);
        transform: translateY(-2px);
    }
    
    .btn-view {
        border: 1px solid rgba(23, 162, 184, 0.3);
        color: var(--info-blue);
    }
    
    .btn-view:hover {
        background: rgba(23, 162, 184, 0.1);
        border-color: var(--info-blue);
        transform: translateY(-2px);
    }
    
    /* Timeline de actividad */
    .activity-timeline {
        padding: 25px;
        max-height: 500px;
        overflow-y: auto;
    }
    
    .timeline-item {
        position: relative;
        padding-left: 25px;
        margin-bottom: 20px;
    }
    
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: -20px;
        width: 2px;
        background: rgba(212, 175, 55, 0.2);
    }
    
    .timeline-item:last-child::before {
        bottom: 0;
    }
    
    .timeline-dot {
        position: absolute;
        left: -6px;
        top: 0;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: var(--primary-gold);
        border: 3px solid var(--dark-charcoal);
    }
    
    .timeline-content {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        padding: 15px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .timeline-time {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.8rem;
        color: var(--primary-gold);
        margin-bottom: 5px;
        display: block;
    }
    
    .timeline-text {
        font-family: 'Montserrat', sans-serif;
        color: var(--crystal-white);
        font-size: 0.9rem;
        line-height: 1.5;
    }
    
    .timeline-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
    }
    
    .meta-badge {
        padding: 3px 8px;
        background: rgba(212, 175, 55, 0.1);
        border: 1px solid rgba(212, 175, 55, 0.3);
        border-radius: 12px;
        font-size: 0.75rem;
        color: var(--silver);
    }
    
    /* Grid layout principal */
    .main-content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        margin-bottom: 40px;
    }
    
    @media (max-width: 992px) {
        .main-content-grid {
            grid-template-columns: 1fr;
        }
    }
    
    /* Panel derecho */
    .side-panel {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }
    
    /* Estados vacíos */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--silver);
    }
    
    .empty-icon {
        font-size: 3rem;
        margin-bottom: 20px;
        opacity: 0.3;
    }
    
    .empty-text {
        font-family: 'Montserrat', sans-serif;
        font-size: 1rem;
    }
    
    /* Scrollbar personalizada para timeline */
    .activity-timeline::-webkit-scrollbar {
        width: 6px;
    }
    
    .activity-timeline::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 3px;
    }
    
    .activity-timeline::-webkit-scrollbar-thumb {
        background: var(--primary-gold);
        border-radius: 3px;
    }
    
    /* Animaciones */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .fade-in {
        animation: fadeIn 0.6s ease-out forwards;
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