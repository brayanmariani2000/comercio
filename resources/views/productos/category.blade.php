@extends('layouts.app')

@section('title', $categoria->nombre)

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('categorias.index') }}">Categorías</a></li>
                @if($categoria->padre)
                    <li class="breadcrumb-item"><a href="{{ route('categorias.show', $categoria->padre->slug) }}">{{ $categoria->padre->nombre }}</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ $categoria->nombre }}</li>
            </ol>
        </nav>
        
        <div class="card bg-light border-0 mb-4">
            <div class="card-body py-4 text-center">
                @if($categoria->icono)
                    <i class="{{ $categoria->icono }} fa-3x text-primary mb-3"></i>
                @endif
                <h1 class="display-6">{{ $categoria->nombre }}</h1>
                @if($categoria->descripcion)
                    <p class="lead text-muted">{{ $categoria->descripcion }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Subcategorías si existen -->
    @if($categoria->subcategorias->count() > 0)
    <div class="col-12 mb-4">
        <h5 class="mb-3">Subcategorías</h5>
        <div class="row">
            @foreach($categoria->subcategorias as $sub)
            <div class="col-6 col-md-3 col-lg-2 mb-3">
                <a href="{{ route('categorias.show', $sub->slug) }}" class="text-decoration-none">
                    <div class="card h-100 hover-shadow">
                        <div class="card-body text-center p-2">
                            <span class="d-block text-truncate fw-bold text-dark">{{ $sub->nombre }}</span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Productos de la categoría -->
    <div class="col-12">
        <h3 class="mb-3">Productos en {{ $categoria->nombre }}</h3>
        
        @if($productos->count() > 0)
        <div class="row">
            @foreach($productos as $producto)
            <div class="col-6 col-md-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <a href="{{ route('productos.show', $producto->id) }}">
                        @if($producto->imagenes->count() > 0)
                            <img src="{{ $producto->imagenes[0]->url }}" class="card-img-top" alt="{{ $producto->nombre }}" style="height: 180px; object-fit: cover;">
                        @else
                            <img src="{{ asset('images/default-product.jpg') }}" class="card-img-top" alt="Sin imagen" style="height: 180px; object-fit: cover;">
                        @endif
                    </a>
                    <div class="card-body">
                        <h5 class="card-title text-truncate">
                            <a href="{{ route('productos.show', $producto->id) }}" class="text-decoration-none text-dark">{{ $producto->nombre }}</a>
                        </h5>
                        <p class="card-text text-muted small">{{ $producto->vendedor->nombre_comercial ?? 'Vendedor' }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0 fw-bold">Bs. {{ number_format($producto->precio_actual, 2, ',', '.') }}</span>
                            @if($producto->oferta)
                                <span class="badge bg-success">-{{ $producto->descuento_porcentaje }}%</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0 d-grid">
                        <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-outline-primary btn-sm">Ver Detalles</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="d-flex justify-content-center">
            @if(method_exists($productos, 'links'))
                {{ $productos->links() }}
            @endif
        </div>
        @else
            <div class="alert alert-info">
                No hay productos disponibles en esta categoría por el momento.
            </div>
        @endif
    </div>
</div>
@endsection
