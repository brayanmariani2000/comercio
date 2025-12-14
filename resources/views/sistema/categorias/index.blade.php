@extends('layouts.app')

@section('title', 'Categorías | Monagas Vende')

@section('content')
<div class="container fade-in-up py-5">
    <div class="text-center mb-5">
        <h1 class="display-font text-neon-cyan mb-3">Explora Nuestras Categorías</h1>
        <p class="text-muted lead">Descubre miles de productos organizados para ti</p>
    </div>

    <div class="row g-4">
        @forelse($categorias as $categoria)
        <div class="col-6 col-md-4 col-lg-3">
            <a href="{{ route('categorias.show', $categoria->slug) }}" class="text-decoration-none">
                <div class="glass-card hover-glow h-100 p-4 text-center position-relative overflow-hidden">
                    <div class="circle-bg position-absolute top-50 start-50 translate-middle"></div>
                    
                    <div class="position-relative z-1">
                        @if($categoria->imagen_url)
                             <div class="category-icon-wrapper mb-3 mx-auto">
                                <img src="{{ $categoria->imagen_url }}" class="img-fluid rounded-circle p-1" alt="{{ $categoria->nombre }}">
                             </div>
                        @else
                            <div class="category-icon-wrapper mb-3 mx-auto d-flex align-items-center justify-content-center bg-dark rounded-circle border border-secondary text-neon-cyan">
                                <i class="{{ $categoria->icono ?? 'fas fa-box' }} fa-3x"></i>
                            </div>
                        @endif

                        <h5 class="text-white fw-bold mb-2">{{ $categoria->nombre }}</h5>
                        <p class="small text-muted mb-0">{{ $categoria->productos_count ?? 0 }} productos</p>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="fas fa-boxes fa-4x text-muted mb-3 opacity-25"></i>
            <h4 class="text-muted">No hay categorías disponibles</h4>
        </div>
        @endforelse
    </div>
</div>

<style>
    .category-icon-wrapper {
        width: 100px;
        height: 100px;
        transition: transform 0.3s ease;
    }
    .glass-card:hover .category-icon-wrapper {
        transform: scale(1.1);
    }
    .circle-bg {
        width: 80px;
        height: 80px;
        background: radial-gradient(circle, rgba(0, 243, 255, 0.2) 0%, rgba(0,0,0,0) 70%);
        opacity: 0;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .glass-card:hover .circle-bg {
        opacity: 1;
        transform: translate(-50%, -50%) scale(2);
    }
    .hover-glow:hover {
        border-color: rgba(0, 243, 255, 0.5);
        box-shadow: 0 0 20px rgba(0, 243, 255, 0.2);
    }
</style>
@endsection
