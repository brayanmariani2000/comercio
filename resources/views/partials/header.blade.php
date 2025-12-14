<nav class="navbar navbar-expand-lg navbar-elegant" style="
    background: linear-gradient(135deg, 
        rgba(18, 18, 18, 0.98) 0%,
        rgba(30, 30, 30, 0.98) 100%);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(212, 175, 55, 0.2);
    box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 1000;
    padding: 0;
">
    <!-- Línea de acento dorado -->
    <div class="gold-accent-line" style="
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, 
            transparent, 
            var(--primary-gold), 
            transparent);
        opacity: 0.6;
    "></div>
    
    <div class="container">
        <!-- Logo elegante -->
        <a class="navbar-brand" href="{{ route('home') }}" style="
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--crystal-white);
            position: relative;
            padding: 20px 0;
            margin-right: 50px;
        ">
            <span class="logo-icon" style="
                display: inline-block;
                width: 40px;
                height: 40px;
                background: var(--primary-gold);
                border-radius: 50%;
                margin-right: 12px;
                position: relative;
                vertical-align: middle;
                overflow: hidden;
            ">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="20" fill="#D4AF37"/>
                    <path d="M15 25L25 15" stroke="#121212" stroke-width="2" stroke-linecap="round"/>
                    <path d="M20 12L28 20L20 28L12 20L20 12Z" stroke="#121212" stroke-width="2" fill="none"/>
                </svg>
            </span>
            <span style="position: relative;">
                MONAGAS
                <span style="
                    color: var(--primary-gold);
                    font-style: italic;
                    font-weight: 400;
                ">.TECH</span>
                <span class="logo-underline" style="
                    position: absolute;
                    bottom: -5px;
                    left: 0;
                    width: 100%;
                    height: 1px;
                    background: var(--primary-gold);
                    transform: scaleX(0);
                    transform-origin: left;
                    transition: transform 0.4s ease;
                "></span>
            </span>
        </a>
        
        <!-- Botón hamburguesa minimalista -->
        <button class="navbar-toggler elegant-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" style="
            border: none;
            background: transparent;
            padding: 10px;
            border-radius: 50%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        ">
            <span class="toggle-icon" style="
                display: block;
                width: 25px;
                height: 2px;
                background: var(--crystal-white);
                position: relative;
                transition: all 0.3s ease;
            "></span>
            <span class="toggle-icon" style="
                display: block;
                width: 25px;
                height: 2px;
                background: var(--crystal-white);
                margin: 5px 0;
                position: relative;
                transition: all 0.3s ease;
            "></span>
            <span class="toggle-icon" style="
                display: block;
                width: 25px;
                height: 2px;
                background: var(--crystal-white);
                position: relative;
                transition: all 0.3s ease;
            "></span>
            <div class="toggle-overlay" style="
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(212, 175, 55, 0.1);
                border-radius: 50%;
                transform: scale(0);
                transition: transform 0.3s ease;
            "></div>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Menú principal -->
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link elegant-link" href="{{ route('categorias.index') }}" style="position: relative;">
                        <span class="nav-number" style="
                            font-family: 'Playfair Display', serif;
                            color: var(--primary-gold);
                            font-size: 0.8rem;
                            margin-right: 8px;
                            opacity: 0.7;
                        "></span>
                        <span class="nav-text">COLECCIONES</span>
                        <span class="nav-indicator" style="
                            position: absolute;
                            bottom: 0;
                            left: 0;
                            width: 0;
                            height: 1px;
                            background: var(--primary-gold);
                            transition: width 0.4s ease;
                        "></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link elegant-link" href="{{ route('productos.index') }}">
                        <span class="nav-number" style="
                            font-family: 'Playfair Display', serif;
                            color: var(--primary-gold);
                            font-size: 0.8rem;
                            margin-right: 8px;
                            opacity: 0.7;
                        "></span>
                        <span class="nav-text">PRODUCTOS</span>
                        <span class="nav-indicator" style="
                            position: absolute;
                            bottom: 0;
                            left: 0;
                            width: 0;
                            height: 1px;
                            background: var(--primary-gold);
                            transition: width 0.4s ease;
                        "></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link elegant-link" href="{{ route('busqueda') }}">
                        <span class="nav-number" style="
                            font-family: 'Playfair Display', serif;
                            color: var(--primary-gold);
                            font-size: 0.8rem;
                            margin-right: 8px;
                            opacity: 0.7;
                        "></span>
                        <span class="nav-text">DISCOVER</span>
                        <span class="nav-indicator" style="
                            position: absolute;
                            bottom: 0;
                            left: 0;
                            width: 0;
                            height: 1px;
                            background: var(--primary-gold);
                            transition: width 0.4s ease;
                        "></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link elegant-link" href="{{ route('ofertas') }}">
                        <span class="nav-number" style="
                            font-family: 'Playfair Display', serif;
                            color: var(--primary-gold);
                            font-size: 0.8rem;
                            margin-right: 8px;
                            opacity: 0.7;
                        "></span>
                        <span class="nav-text" style="
                            background: linear-gradient(45deg, var(--primary-gold), var(--secondary-gold));
                            -webkit-background-clip: text;
                            -webkit-text-fill-color: transparent;
                            background-clip: text;
                        ">EXCLUSIVOS</span>
                        <span class="nav-indicator" style="
                            position: absolute;
                            bottom: 0;
                            left: 0;
                            width: 0;
                            height: 1px;
                            background: linear-gradient(90deg, var(--primary-gold), var(--secondary-gold));
                            transition: width 0.4s ease;
                        "></span>
                    </a>
                </li>
            </ul>
            
            <!-- Menú derecho -->
            <ul class="navbar-nav">
                @guest
                    <!-- Usuario no autenticado -->
                    <li class="nav-item me-3">
                        <a class="nav-link elegant-link" href="{{ route('login') }}">
                            <span class="nav-text">INGRESAR</span>
                            <span class="nav-indicator" style="
                                position: absolute;
                                bottom: 0;
                                left: 0;
                                width: 0;
                                height: 1px;
                                background: var(--primary-gold);
                                transition: width 0.4s ease;
                            "></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-elegant-registro" href="{{ route('register') }}">
                            <span>CREAR CUENTA</span>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" style="margin-left: 10px;">
                                <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </li>
                @else
                    <!-- Mensajes (solo si autenticado) -->
                    <li class="nav-item me-3">
                        <a class="nav-link elegant-link-icon" href="{{ route('comprador.mensajes.index') }}" title="Mis Mensajes">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <path d="M22 6l-10 7L2 6" />
                            </svg>
                        </a>
                    </li>

                    <!-- Carrito de compras elegante -->
                    @if(auth()->user()->esComprador())
                        <li class="nav-item me-4">
                            <a class="nav-link elegant-cart" href="{{ route('comprador.carrito') }}" style="position: relative;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                @if(auth()->user()->carrito && auth()->user()->carrito->items->count() > 0)
                                    <span class="cart-count" style="
                                        position: absolute;
                                        top: -5px;
                                        right: -5px;
                                        background: var(--primary-gold);
                                        color: var(--dark-charcoal);
                                        font-family: 'Montserrat', sans-serif;
                                        font-weight: 600;
                                        font-size: 0.7rem;
                                        width: 18px;
                                        height: 18px;
                                        border-radius: 50%;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        border: 2px solid var(--light-charcoal);
                                    ">
                                        {{ auth()->user()->carrito->items->count() }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endif
                    
                    <!-- Avatar de usuario -->
                    <li class="nav-item dropdown">
                        <a class="nav-link user-avatar-dropdown" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" style="
                            padding: 0;
                            display: flex;
                            align-items: center;
                        ">
                            <div class="user-avatar-container" style="
                                position: relative;
                                width: 40px;
                                height: 40px;
                                border-radius: 50%;
                                overflow: hidden;
                                border: 2px solid transparent;
                                transition: all 0.3s ease;
                            ">
                                <div class="user-avatar-initial" style="
                                    width: 100%;
                                    height: 100%;
                                    background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    font-family: 'Montserrat', sans-serif;
                                    font-weight: 600;
                                    color: var(--dark-charcoal);
                                    font-size: 1.2rem;
                                ">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="avatar-status" style="
                                    position: absolute;
                                    bottom: 0;
                                    right: 0;
                                    width: 12px;
                                    height: 12px;
                                    background: var(--primary-gold);
                                    border-radius: 50%;
                                    border: 2px solid var(--light-charcoal);
                                "></div>
                            </div>
                            
                            <span class="user-name ms-3" style="
                                font-family: 'Montserrat', sans-serif;
                                font-weight: 500;
                                color: var(--crystal-white);
                                font-size: 0.9rem;
                                transition: color 0.3s ease;
                            ">
                                {{ auth()->user()->name }}
                            </span>
                            
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="ms-2" style="
                                transition: transform 0.3s ease;
                            ">
                                <path d="M4 6L8 10L12 6" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        
                        <!-- Dropdown menu elegante -->
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" style="
                            background: rgba(30, 30, 30, 0.98);
                            backdrop-filter: blur(20px);
                            border: 1px solid rgba(212, 175, 55, 0.2);
                            border-radius: 10px;
                            padding: 15px;
                            margin-top: 15px;
                            min-width: 250px;
                            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
                        ">
                            <!-- Header del dropdown -->
                            <li class="dropdown-header" style="
                                padding: 15px 20px;
                                border-bottom: 1px solid rgba(212, 175, 55, 0.1);
                                margin-bottom: 10px;
                            ">
                                <div class="d-flex align-items-center">
                                    <div class="dropdown-avatar me-3" style="
                                        width: 45px;
                                        height: 45px;
                                        border-radius: 50%;
                                        background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        font-family: 'Montserrat', sans-serif;
                                        font-weight: 600;
                                        color: var(--dark-charcoal);
                                        font-size: 1.3rem;
                                    ">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="
                                            font-family: 'Montserrat', sans-serif;
                                            font-weight: 600;
                                            color: var(--crystal-white);
                                            font-size: 0.95rem;
                                        ">
                                            {{ auth()->user()->name }}
                                        </div>
                                        <div style="
                                            font-size: 0.75rem;
                                            color: var(--primary-gold);
                                            margin-top: 3px;
                                            display: flex;
                                            align-items: center;
                                            gap: 5px;
                                        ">
                                            @if(auth()->user()->esComprador())
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                    <circle cx="6" cy="6" r="5.5" fill="#D4AF37"/>
                                                </svg>
                                                CLIENTE PREMIUM
                                            @elseif(auth()->user()->esVendedor())
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                    <path d="M3 9L9 3M9 9L3 3" stroke="#D4AF37" stroke-width="1.5"/>
                                                </svg>
                                                VENDEDOR
                                            @elseif(auth()->user()->esAdministrador())
                                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                    <path d="M6 1L8 4L11 5L8 6L6 9L4 6L1 5L4 4L6 1Z" stroke="#D4AF37" stroke-width="1.5" fill="none"/>
                                                </svg>
                                                ADMINISTRADOR
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                            
                            <!-- Dashboard según rol -->
                            @if(auth()->user()->esComprador())
                                <li>
                                    <a class="dropdown-item elegant-dropdown-item" href="{{ route('comprador.dashboard') }}">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" class="me-3">
                                            <rect x="2" y="2" width="6" height="6" rx="1" stroke="var(--primary-gold)" stroke-width="1.5"/>
                                            <rect x="10" y="2" width="6" height="6" rx="1" stroke="var(--primary-gold)" stroke-width="1.5"/>
                                            <rect x="2" y="10" width="6" height="6" rx="1" stroke="var(--primary-gold)" stroke-width="1.5"/>
                                            <rect x="10" y="10" width="6" height="6" rx="1" stroke="var(--primary-gold)" stroke-width="1.5"/>
                                        </svg>
                                        <span>Mi Dashboard</span>
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="ms-auto">
                                            <path d="M6 12L10 8L6 4" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </li>
                                
                                <li>
                                    <a class="dropdown-item elegant-dropdown-item" href="{{ route('comprador.pedidos.index') }}">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" class="me-3">
                                            <rect x="2" y="4" width="14" height="12" rx="1" stroke="var(--primary-gold)" stroke-width="1.5"/>
                                            <path d="M6 8H12" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M6 11H10" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M5 1H13V4H5V1Z" stroke="var(--primary-gold)" stroke-width="1.5"/>
                                        </svg>
                                        <span>Mis Pedidos</span>
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="ms-auto">
                                            <path d="M6 12L10 8L6 4" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </li>
                            @elseif(auth()->user()->esVendedor())
                                <li>
                                    <a class="dropdown-item elegant-dropdown-item" href="{{ route('vendedor.dashboard') }}">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" class="me-3">
                                            <path d="M3 15H15" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M4 15L4 7L8 3H10L14 7V15" stroke="var(--primary-gold)" stroke-width="1.5" fill="none"/>
                                            <circle cx="9" cy="10" r="2" stroke="var(--primary-gold)" stroke-width="1.5" fill="none"/>
                                        </svg>
                                        <span>Panel Vendedor</span>
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="ms-auto">
                                            <path d="M6 12L10 8L6 4" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </li>
                            @elseif(auth()->user()->esAdministrador())
                                <li>
                                    <a class="dropdown-item elegant-dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" class="me-3">
                                            <circle cx="9" cy="6" r="3" stroke="var(--primary-gold)" stroke-width="1.5" fill="none"/>
                                            <path d="M3 15C3 11.6863 5.68629 9 9 9C12.3137 9 15 11.6863 15 15" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        <span>Panel Admin</span>
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="ms-auto">
                                            <path d="M6 12L10 8L6 4" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </li>
                            @endif
                            
                            <!-- Items comunes -->
                            <li>
                                <a class="dropdown-item elegant-dropdown-item" href="{{ route('perfil') }}">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" class="me-3">
                                        <rect x="2" y="2" width="14" height="14" rx="3" stroke="var(--primary-gold)" stroke-width="1.5"/>
                                        <path d="M7 6H11" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M7 9H11" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M7 12H9" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    <span>Mi Perfil</span>
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="ms-auto">
                                        <path d="M6 12L10 8L6 4" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                            </li>
                            
                            <li>
                                <a class="dropdown-item elegant-dropdown-item" href="{{ route('configuracion') }}">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" class="me-3">
                                        <circle cx="9" cy="9" r="2" stroke="var(--primary-gold)" stroke-width="1.5" fill="none"/>
                                        <path d="M13.5 7.5L15.5 5.5M15.5 12.5L13.5 10.5M10.5 13.5L12.5 15.5M5.5 15.5L7.5 13.5M2.5 12.5L4.5 10.5M4.5 7.5L2.5 5.5M7.5 4.5L5.5 2.5M12.5 2.5L10.5 4.5" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                    <span>Configuración</span>
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="ms-auto">
                                        <path d="M6 12L10 8L6 4" stroke="var(--primary-gold)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                            </li>
                            
                            <li class="dropdown-divider" style="border-color: rgba(212, 175, 55, 0.1); margin: 10px 0;"></li>
                            
                            <!-- Logout -->
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline w-100">
                                    @csrf
                                    <button type="submit" class="dropdown-item elegant-dropdown-item logout-item w-100">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" class="me-3">
                                            <path d="M7 16H3C2.44772 16 2 15.5523 2 15V3C2 2.44772 2.44772 2 3 2H7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M13 13L17 9L13 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M17 9H7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        <span>Cerrar Sesión</span>
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="ms-auto">
                                            <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>

<style>
    /* Variables del navbar elegante */
    :root {
        --primary-gold: #D4AF37;
        --secondary-gold: #FFD700;
        --dark-charcoal: #121212;
        --light-charcoal: #1E1E1E;
        --crystal-white: #FFFFFF;
        --silver: #C0C0C0;
    }
    
    /* Estilos para enlaces del navbar */
    .elegant-link {
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        font-size: 0.9rem;
        color: var(--crystal-white) !important;
        padding: 25px 20px !important;
        position: relative;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .elegant-link:hover {
        color: var(--primary-gold) !important;
    }
    
    .elegant-link:hover .nav-indicator {
        width: 100%;
    }
    
    .navbar-brand:hover .logo-underline {
        transform: scaleX(1);
    }
    
    /* Botón de registro elegante */
    .btn-elegant-registro {
        background: transparent;
        color: var(--primary-gold);
        border: 1px solid var(--primary-gold);
        padding: 12px 25px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        font-size: 0.9rem;
        letter-spacing: 1px;
        border-radius: 0;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
        text-transform: uppercase;
    }
    
    .btn-elegant-registro:hover {
        background: var(--primary-gold);
        color: var(--dark-charcoal);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(212, 175, 55, 0.2);
    }
    
    /* Carrito elegante */
    .elegant-cart {
        color: var(--crystal-white);
        padding: 25px 15px !important;
        transition: color 0.3s ease;
    }
    
    .elegant-cart:hover {
        color: var(--primary-gold) !important;
    }

    .elegant-link-icon {
        color: var(--crystal-white);
        padding: 25px 15px !important;
        transition: color 0.3s ease;
    }
    
    .elegant-link-icon:hover {
        color: var(--primary-gold) !important;
    }
    
    /* Dropdown items elegantes */
    .elegant-dropdown-item {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: var(--crystal-white);
        border-radius: 5px;
        margin: 3px 0;
        transition: all 0.2s ease;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.9rem;
    }
    
    .elegant-dropdown-item:hover {
        background: rgba(212, 175, 55, 0.1);
        color: var(--primary-gold);
        transform: translateX(5px);
    }
    
    /* Item de logout */
    .logout-item {
        color: #ff6b8b;
        border: 1px solid rgba(255, 107, 139, 0.2);
        margin-top: 5px;
    }
    
    .logout-item:hover {
        background: rgba(255, 107, 139, 0.1);
        color: #ff3b6b;
    }
    
    /* Botón hamburguesa */
    .elegant-toggle:hover .toggle-overlay {
        transform: scale(1);
    }
    
    .elegant-toggle:hover .toggle-icon {
        background: var(--primary-gold);
    }
    
    /* Avatar hover */
    .user-avatar-dropdown:hover .user-avatar-container {
        border-color: var(--primary-gold);
    }
    
    .user-avatar-dropdown:hover .user-name {
        color: var(--primary-gold);
    }
    
    .user-avatar-dropdown:hover svg {
        transform: rotate(180deg);
    }
    
    /* Responsive */
    @media (max-width: 992px) {
        .navbar-nav {
            padding: 20px 0;
        }
        
        .elegant-link {
            padding: 15px 20px !important;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        }
        
        .btn-elegant-registro {
            margin-top: 15px;
            width: 100%;
            justify-content: center;
        }
        
        .elegant-cart {
            padding: 15px !important;
        }
        
        .user-avatar-dropdown {
            padding: 15px 20px !important;
            border-top: 1px solid rgba(212, 175, 55, 0.1);
        }
    }
</style>