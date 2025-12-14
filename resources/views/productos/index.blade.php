@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="row">
    <!-- Sidebar de Filtros -->
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Filtros</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('productos.index') }}" method="GET">
                    <!-- Categorías -->
                    <div class="mb-4">
                        <h6 class="fw-bold">Categorías</h6>
                        @foreach($categorias as $cat)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="categorias[]" value="{{ $cat->id }}" id="cat{{ $cat->id }}"
                                {{ in_array($cat->id, request('categorias', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cat{{ $cat->id }}">
                                {{ $cat->nombre }}
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <!-- Precio -->
                    <div class="mb-4">
                        <h6 class="fw-bold">Precio</h6>
                        <div class="d-flex align-items-center mb-2">
                            <input type="number" name="min_price" class="form-control form-control-sm me-2" placeholder="Mín" value="{{ request('min_price') }}">
                            <span>-</span>
                            <input type="number" name="max_price" class="form-control form-control-sm ms-2" placeholder="Máx" value="{{ request('max_price') }}">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">Aplicar Filtros</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Listado de Productos -->
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Catálogo de Productos</h2>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="ordenarPor" data-bs-toggle="dropdown">
                    Ordenar por
                </button>
                <ul class="dropdown-menu"> <!-- Ajustar hrefs según lógica de ordenamiento -->
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}">Más nuevos</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}">Precio: Menor a Mayor</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}">Precio: Mayor a Menor</a></li>
                </ul>
            </div>
        </div>

        @if($productos->count() > 0)
        <div class="row">
            @foreach($productos as $producto)
            <div class="col-6 col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <a href="{{ route('productos.show', $producto->id) }}">
                        @if($producto->imagenes->count() > 0)
                            <img src="{{ $producto->imagenes[0]->url }}" class="card-img-top" alt="{{ $producto->nombre }}" style="height: 200px; object-fit: cover;">
                        @else
                            <img src="{{ asset('images/default-product.jpg') }}" class="card-img-top" alt="Sin imagen" style="height: 200px; object-fit: cover;">
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
                        <a href="{{ route('productos.show', $producto->id) }}" class="btn btn-outline-primary">Ver Detalles</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $productos->links() }}
        </div>
        @else
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <p>No se encontraron productos con los filtros seleccionados.</p>
                <a href="{{ route('productos.index') }}" class="btn btn-link">Limpiar filtros</a>
            </div>
        @endif
    </div>
</div>
@endsection
