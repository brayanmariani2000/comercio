@extends('layouts.app')

@section('title', 'Resultados de búsqueda: ' . $termino)

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-3">
            Resultados para <span class="text-primary">"{{ $termino }}"</span>
            <small class="text-muted fs-5">({{ $productos->count() }} resultados)</small>
        </h2>
    </div>
</div>

<div class="row">
    @if($productos->count() > 0)
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
    @else
        <div class="col-12">
            <div class="alert alert-warning text-center p-5">
                <i class="fas fa-search fa-3x mb-3 text-warning"></i>
                <h3>No encontramos resultados para "{{ $termino }}"</h3>
                <p class="lead">Intenta verificar la ortografía o usar términos más generales.</p>
                
                <h5 class="mt-4">Sugerencias:</h5>
                <ul class="list-unstyled">
                    <li>Revisa errores tipográficos.</li>
                    <li>Prueba con sinónimos (ej. "celular" en vez de "smartphone").</li>
                    <li>Navega por nuestras <a href="{{ route('categorias.index') }}">categorías</a>.</li>
                </ul>
                
                <div class="mt-4">
                    <a href="{{ route('productos.index') }}" class="btn btn-primary">Ver todos los productos</a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
