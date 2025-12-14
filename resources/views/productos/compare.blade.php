@extends('layouts.app')

@section('title', 'Comparar Productos')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <h2 class="mb-3">Comparación de Productos</h2>
        <a href="{{ route('productos.index') }}" class="btn btn-outline-secondary mb-3"><i class="fas fa-arrow-left me-2"></i> Volver al catálogo</a>
    </div>

    @if(isset($productos) && count($productos) > 0)
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th style="width: 20%;" class="bg-light">Característica</th>
                        @foreach($productos as $producto)
                        <th style="width: {{ 80 / count($productos) }}%;">
                            <div class="position-relative">
                                <a href="#" class="position-absolute top-0 end-0 text-danger" title="Quitar"><i class="fas fa-times"></i></a>
                                <a href="{{ route('productos.show', $producto->id) }}">
                                    @if($producto->imagenes->count() > 0)
                                        <img src="{{ $producto->imagenes[0]->url }}" alt="{{ $producto->nombre }}" class="img-fluid mb-2" style="height: 100px; object-fit: contain;">
                                    @else
                                        <img src="{{ asset('images/default-product.jpg') }}" alt="Sin imagen" class="img-fluid mb-2" style="height: 100px; object-fit: contain;">
                                    @endif
                                </a>
                                <h6 class="text-truncate px-2"><a href="{{ route('productos.show', $producto->id) }}" class="text-decoration-none text-dark">{{ $producto->nombre }}</a></h6>
                            </div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <!-- Precio -->
                    <tr>
                        <th class="bg-light text-start">Precio</th>
                        @foreach($productos as $producto)
                        <td class="fw-bold text-primary">Bs. {{ number_format($producto->precio_actual, 2, ',', '.') }}</td>
                        @endforeach
                    </tr>
                    
                    <!-- Vendedor -->
                    <tr>
                        <th class="bg-light text-start">Vendedor</th>
                        @foreach($productos as $producto)
                        <td>{{ $producto->vendedor->nombre_comercial ?? 'N/A' }}</td>
                        @endforeach
                    </tr>

                    <!-- Estado -->
                    <tr>
                        <th class="bg-light text-start">Condición</th>
                        @foreach($productos as $producto)
                        <td>{{ $producto->condicion ?? 'Nuevo' }}</td>
                        @endforeach
                    </tr>

                    <!-- Rating -->
                    <tr>
                        <th class="bg-light text-start">Calificación</th>
                        @foreach($productos as $producto)
                        <td>
                            <div class="text-warning">
                                {{ $producto->rating_promedio }} <i class="fas fa-star"></i>
                            </div>
                            <small class="text-muted">({{ $producto->resenas->count() }} reseñas)</small>
                        </td>
                        @endforeach
                    </tr>

                    <!-- Especificaciones Dinámicas (Ejemplo simplificado) -->
                    <tr>
                        <th class="bg-light text-start">Descripción</th>
                        @foreach($productos as $producto)
                        <td class="small text-start">{{ Str::limit($producto->descripcion, 100) }}</td>
                        @endforeach
                    </tr>
                    
                    <!-- Acciones -->
                    <tr>
                        <th class="bg-light"></th>
                        @foreach($productos as $producto)
                        <td>
                            <form action="{{ route('carrito.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                                <input type="hidden" name="cantidad" value="1">
                                <button type="submit" class="btn btn-primary btn-sm w-100" {{ $producto->stock <= 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-cart-plus"></i> Agregar
                                </button>
                            </form>
                        </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="col-12">
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-balance-scale fa-3x mb-3 text-info"></i>
            <h4>No hay productos para comparar</h4>
            <p>Agrega productos a la lista de comparación desde la página de detalles del producto.</p>
            <a href="{{ route('productos.index') }}" class="btn btn-primary mt-3">Ir al catálogo</a>
        </div>
    </div>
    @endif
</div>
@endsection
