@extends('layouts.app')

@section('title', 'Inicio - Mercado Electrónico Venezuela')
@section('description', 'Compra smartphones, laptops, televisores y más con envío a todo Venezuela. Garantía y soporte.')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div id="carouselProductos" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="{{ asset('images/banner-home.png') }}" class="d-block w-100" alt="Ofertas" style="height: 300px; object-fit: cover;">
                    <div class="carousel-caption d-none d-md-block">
                        <h3>¡Ofertas Exclusivas Hoy!</h3>
                        <p>Hasta 50% de descuento en electrónicos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($destacados->count() > 0)
<h2 class="mb-3"><i class="fas fa-fire text-danger"></i> Productos Destacados</h2>
<div class="row">
    @foreach($destacados as $producto)
    <div class="col-6 col-md-3 mb-3">
        <div class="card h-100 shadow-sm">
            @if($producto->imagenes->count() > 0)
                <img src="{{ $producto->imagenes[0]->url }}" class="card-img-top" alt="{{ $producto->nombre }}" style="height: 150px; object-fit: cover;">
            @else
                <img src="{{ asset('images/default-product.jpg') }}" class="card-img-top" alt="Sin imagen" style="height: 150px; object-fit: cover;">
            @endif
            <div class="card-body p-2">
                <h6 class="card-title fw-bold text-truncate">{{ $producto->nombre }}</h6>
                <p class="text-muted mb-1"><small>Vendido por: {{ $producto->vendedor->nombre_comercial ?? 'Sin vendedor' }}</small></p>
                <p class="mb-0"><strong>Bs. {{ number_format($producto->precio_actual, 2, ',', '.') }}</strong></p>
                @if($producto->oferta && $producto->descuento_porcentaje)
                    <small class="text-success">-{{ $producto->descuento_porcentaje }}% OFF</small>
                @endif
            </div>
            <div class="card-footer p-2">
                <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-sm btn-outline-primary">Ver</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if($categorias->count() > 0)
<h2 class="mt-4 mb-3"><i class="fas fa-tags text-warning"></i> Categorías</h2>
<div class="row">
    @foreach($categorias as $categoria)
    <div class="col-6 col-md-2 mb-3">
        <a href="{{ route('categorias.show', $categoria->slug) }}" class="text-decoration-none">
            <div class="card text-center border-0">
                <div class="card-body py-2">
                    @if($categoria->icono)
                        <i class="{{ $categoria->icono }} fa-2x text-primary"></i>
                    @else
                        <i class="fas fa-box fa-2x text-primary"></i>
                    @endif
                    <p class="mb-0 mt-1 small">{{ $categoria->nombre }}</p>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>
@endif
@endsection