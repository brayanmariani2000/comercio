@extends('layouts.app')

@section('title', 'Inicio - Mercado Electrónico Venezuela')
@section('description', 'Compra smartphones, laptops, televisores y más con envío a todo Venezuela. Garantía y soporte.')

@push('styles')
<style>
    /* Variables de diseño elegante */
    :root {
        --primary-gold: #D4AF37;
        --secondary-gold: #FFD700;
        --dark-charcoal: #121212;
        --deep-navy: #0A1931;
        --light-charcoal: #1E1E1E;
        --platinum: #E5E4E2;
        --silver: #C0C0C0;
        --crystal-white: #FFFFFF;
        --glass-white: rgba(255, 255, 255, 0.1);
        --shadow-elegant: 0 20px 60px rgba(0, 0, 0, 0.3);
        --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        /* Background moved to layout */
        color: var(--platinum);
        font-family: 'Montserrat', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* Animaciones sutiles */
    @keyframes floatElegant {
        0%, 100% { transform: translateY(0) scale(1); }
        50% { transform: translateY(-8px) scale(1.02); }
    }

    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }

    @keyframes borderGlow {
        0%, 100% { border-color: rgba(212, 175, 55, 0.3); }
        50% { border-color: rgba(212, 175, 55, 0.7); }
    }

    /* Header de secciones */
    .elegant-header {
        position: relative;
        padding-bottom: 25px;
        margin-bottom: 50px;
    }

    .elegant-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 120px;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-gold), transparent);
        border-radius: 2px;
    }

    .section-title {
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        font-size: 2.2rem;
        color: var(--crystal-white);
        letter-spacing: 1px;
        position: relative;
        display: inline-block;
    }

    .section-title::before {
        content: '';
        position: absolute;
        width: 40px;
        height: 2px;
        background: var(--primary-gold);
        bottom: -10px;
        left: 0;
    }

    .title-accent {
        color: var(--primary-gold);
        font-style: italic;
    }

    /* Carousel de lujo */
    .luxury-carousel {
        border-radius: 0;
        overflow: hidden;
        position: relative;
        margin-bottom: 80px;
    }

    .luxury-carousel::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(18, 18, 18, 0.8), rgba(10, 25, 49, 0.6));
        z-index: 1;
    }

    .luxury-carousel img {
        height: 600px;
        object-fit: cover;
        filter: brightness(0.7);
        transition: filter 0.8s ease;
    }

    .carousel-item:hover img {
        filter: brightness(0.8);
    }

    .luxury-caption {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 60px 80px;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        z-index: 2;
    }

    .luxury-caption h2 {
        font-family: 'Playfair Display', serif;
        font-size: 3.5rem;
        font-weight: 700;
        color: var(--crystal-white);
        margin-bottom: 20px;
        letter-spacing: 2px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }

    .luxury-caption .lead {
        font-size: 1.3rem;
        color: var(--silver);
        max-width: 600px;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    /* Cards de productos premium */
    .product-card {
        background: var(--light-charcoal);
        border: none;
        border-radius: 0;
        overflow: hidden;
        transition: var(--transition-smooth);
        height: 100%;
        position: relative;
    }

    .product-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--primary-gold), transparent);
        z-index: 2;
    }

    .product-card:hover {
        transform: translateY(-15px);
        box-shadow: var(--shadow-elegant);
    }

    .product-image-container {
        position: relative;
        overflow: hidden;
        height: 250px;
        background: var(--dark-charcoal);
    }

    .product-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.8s ease;
    }

    .product-card:hover .product-image-container img {
        transform: scale(1.05);
    }

    .product-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.8));
        opacity: 0;
        transition: opacity 0.4s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-card:hover .product-overlay {
        opacity: 1;
    }

    .quick-view-btn {
        background: rgba(212, 175, 55, 0.9);
        color: var(--dark-charcoal);
        border: none;
        padding: 12px 30px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        font-size: 0.85rem;
        cursor: pointer;
        transition: var(--transition-smooth);
        transform: translateY(20px);
        opacity: 0;
    }

    .product-card:hover .quick-view-btn {
        transform: translateY(0);
        opacity: 1;
    }

    .quick-view-btn:hover {
        background: var(--crystal-white);
        letter-spacing: 2px;
    }

    .product-info {
        padding: 25px;
    }

    .product-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        color: var(--crystal-white);
        font-size: 1rem;
        margin-bottom: 10px;
        line-height: 1.5;
        min-height: 48px;
    }

    .product-vendor {
        color: var(--silver);
        font-size: 0.85rem;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .product-vendor::before {
        content: '✦';
        color: var(--primary-gold);
        font-size: 0.9rem;
    }

    .product-price {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        color: var(--primary-gold);
        margin-bottom: 15px;
    }

    .product-price span {
        font-size: 1rem;
        color: var(--silver);
        text-decoration: line-through;
        margin-left: 10px;
    }

    .elegant-btn {
        background: transparent;
        color: var(--primary-gold);
        border: 1px solid var(--primary-gold);
        padding: 12px 25px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        font-size: 0.85rem;
        width: 100%;
        cursor: pointer;
        transition: var(--transition-smooth);
        position: relative;
        overflow: hidden;
    }

    .elegant-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: var(--primary-gold);
        transition: left 0.4s ease;
        z-index: -1;
    }

    .elegant-btn:hover {
        color: var(--dark-charcoal);
        letter-spacing: 2px;
    }

    .elegant-btn:hover::before {
        left: 0;
    }

    /* Badge de descuento elegante */
    .discount-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        background: var(--primary-gold);
        color: var(--dark-charcoal);
        padding: 8px 15px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 0.9rem;
        letter-spacing: 1px;
        z-index: 2;
    }

    /* Cards de categorías sofisticadas */
    .category-card {
        position: relative;
        height: 180px;
        overflow: hidden;
        transition: var(--transition-smooth);
    }

    .category-card:hover {
        transform: translateY(-10px);
    }

    .category-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        filter: brightness(0.4);
        transition: filter 0.4s ease;
    }

    .category-card:hover .category-image {
        filter: brightness(0.6);
    }

    .category-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 25px;
        background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
    }

    .category-icon {
        font-size: 2rem;
        color: var(--primary-gold);
        margin-bottom: 10px;
    }

    .category-name {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        color: var(--crystal-white);
        font-size: 1.1rem;
        letter-spacing: 1px;
    }

    /* Banner de servicios premium */
    .services-banner {
        background: linear-gradient(135deg, var(--dark-charcoal) 0%, var(--light-charcoal) 100%);
        padding: 60px 0;
        margin: 80px 0;
        position: relative;
        overflow: hidden;
    }

    .services-banner::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--primary-gold), transparent);
    }

    .service-item {
        text-align: center;
        padding: 20px;
    }

    .service-icon {
        font-size: 2.5rem;
        color: var(--primary-gold);
        margin-bottom: 20px;
    }

    .service-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        color: var(--crystal-white);
        font-size: 1.1rem;
        margin-bottom: 10px;
    }

    .service-description {
        color: var(--silver);
        font-size: 0.9rem;
        line-height: 1.6;
    }

    /* Grid decorativo */
    .grid-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
        background-size: 50px 50px;
        pointer-events: none;
        z-index: 9999;
    }

    /* Efectos de brillo sutil */
    .gold-glow {
        position: relative;
    }

    .gold-glow::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at center, transparent 30%, rgba(212, 175, 55, 0.03) 70%);
        pointer-events: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .luxury-carousel img {
            height: 400px;
        }
        
        .luxury-caption {
            padding: 40px 20px;
        }
        
        .luxury-caption h2 {
            font-size: 2.2rem;
        }
        
        .luxury-caption .lead {
            font-size: 1.1rem;
        }
        
        .section-title {
            font-size: 1.8rem;
        }
        
        .product-image-container {
            height: 200px;
        }
    }

    @media (max-width: 576px) {
        .luxury-carousel img {
            height: 300px;
        }
        
        .product-image-container {
            height: 180px;
        }
        
        .product-info {
            padding: 20px;
        }
    }
</style>
@endpush

@section('content')
<!-- Grid decorativo de fondo -->
<div class="grid-overlay"></div>

<div class="container-fluid px-lg-5">
    <!-- Carousel de lujo -->
    <div class="row">
        <div class="col-12">
            <div id="carouselProductos" class="carousel slide luxury-carousel" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="{{ asset('images/banner-home.png') }}" class="d-block w-100" alt="Tecnología de Vanguardia">
                        <div class="luxury-caption">
                            <h2>TECNOLOGÍA <span class="title-accent">ELEGANTE</span></h2>
                            <p class="lead">Descubre productos exclusivos donde el diseño sofisticado se encuentra con la innovación tecnológica.</p>
                            <button class="elegant-btn" style="width: auto; padding: 15px 40px;">
                                EXPLORAR COLECCIÓN
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos Destacados -->
    @if($destacados->count() > 0)
    <div class="row mb-5">
        <div class="col-12">
            <div class="elegant-header">
                <h2 class="section-title">
                    <span class="title-accent">Colección</span> Destacada
                </h2>
            </div>
            
            <div class="row g-4">
                @foreach($destacados as $producto)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card gold-glow">
                        <div class="product-image-container">
                            @if($producto->imagenes->count() > 0)
                                <img src="{{ $producto->imagenes[0]->url }}" alt="{{ $producto->nombre }}">
                            @else
                                <img src="{{ asset('images/default-product.jpg') }}" alt="Producto destacado">
                            @endif
                            
                            <div class="product-overlay">
                                <button class="quick-view-btn">
                                    VISTA RÁPIDA
                                </button>
                            </div>
                            
                            @if($producto->oferta && $producto->descuento_porcentaje)
                                <div class="discount-badge">
                                    -{{ $producto->descuento_porcentaje }}%
                                </div>
                            @endif
                        </div>
                        
                        <div class="product-info">
                            <h6 class="product-title">{{ $producto->nombre }}</h6>
                            
                            <p class="product-vendor">
                                {{ $producto->vendedor->nombre_comercial ?? 'Vendedor Premium' }}
                            </p>
                            
                            <div class="product-price">
                                Bs. {{ number_format($producto->precio_actual, 2, ',', '.') }}
                                @if($producto->precio_original && $producto->precio_original > $producto->precio_actual)
                                    <span>Bs. {{ number_format($producto->precio_original, 2, ',', '.') }}</span>
                                @endif
                            </div>
                            
                            <a href="{{ route('productos.show', $producto->id) }}" class="elegant-btn">
                                VER DETALLES
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Categorías Exclusivas -->
    @if($categorias->count() > 0)
    <div class="row mb-5">
        <div class="col-12">
            <div class="elegant-header">
                <h2 class="section-title">
                    Categorías <span class="title-accent">Exclusivas</span>
                </h2>
            </div>
            
            <div class="row g-4">
                @foreach($categorias as $categoria)
                <div class="col-6 col-md-3">
                    <a href="{{ route('categorias.show', $categoria->slug) }}" class="text-decoration-none">
                        <div class="category-card">
                            <img src="{{ $categoria->imagen ?? asset('images/category-default.jpg') }}" 
                                 alt="{{ $categoria->nombre }}" 
                                 class="category-image">
                            
                            <div class="category-content">
                                <div class="category-icon">
                                    @if($categoria->icono)
                                        <i class="{{ $categoria->icono }}"></i>
                                    @else
                                        <i class="fas fa-gem"></i>
                                    @endif
                                </div>
                                <div class="category-name">{{ $categoria->nombre }}</div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Servicios Premium -->
    <div class="services-banner">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="section-title">Servicios <span class="title-accent">Premium</span></h2>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="service-title">Garantía Extendida</h4>
                        <p class="service-description">
                            Cobertura completa y soporte técnico especializado para tu tranquilidad.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <h4 class="service-title">Entrega Express</h4>
                        <p class="service-description">
                            Envío prioritario a todo el territorio nacional con seguimiento en tiempo real.
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="service-item">
                        <div class="service-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4 class="service-title">Asesoría Personal</h4>
                        <p class="service-description">
                            Expertos disponibles para guiarte en la selección ideal de tecnología.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Newsletter Elegante -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="text-center p-5" style="
                background: linear-gradient(135deg, 
                    rgba(18, 18, 18, 0.95), 
                    rgba(30, 30, 30, 0.95));
                border-top: 1px solid rgba(212, 175, 55, 0.3);
                position: relative;
            ">
                <h3 class="mb-4" style="
                    font-family: 'Playfair Display', serif;
                    color: var(--crystal-white);
                    font-size: 2rem;
                ">
                    Únete al <span style="color: var(--primary-gold);">Círculo</span> Exclusivo
                </h3>
                <p class="mb-4" style="color: var(--silver); max-width: 600px; margin: 0 auto;">
                    Recibe acceso anticipado a lanzamientos, ofertas especiales y contenido exclusivo.
                </p>
                
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="email" 
                                   class="form-control" 
                                   placeholder="tu@email.com"
                                   style="
                                       background: rgba(255,255,255,0.05);
                                       border: 1px solid rgba(212, 175, 55, 0.3);
                                       color: var(--crystal-white);
                                       padding: 15px;
                                       border-radius: 0;
                                   ">
                            <button class="elegant-btn" style="width: auto; padding: 15px 30px;">
                                SUSCRIBIRSE
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
    // Efecto de aparición elegante
    document.addEventListener('DOMContentLoaded', function() {
        // Animación de entrada para elementos
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Aplicar a elementos que queremos animar
        document.querySelectorAll('.product-card, .category-card, .service-item').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    });
</script>
@endpush
@endsection