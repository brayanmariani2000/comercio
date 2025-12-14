@extends('layouts.app')

@section('title', $producto->nombre)

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
        @if($producto->categoria)
            <li class="breadcrumb-item"><a href="{{ route('categorias.show', $producto->categoria->slug) }}">{{ $producto->categoria->nombre }}</a></li>
        @endif
        <li class="breadcrumb-item active" aria-current="page">{{ $producto->nombre }}</li>
    </ol>
</nav>

<div class="row">
    <!-- Galería de Imágenes -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @if($producto->imagenes->count() > 0)
                    <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach($producto->imagenes as $key => $imagen)
                                <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                    <img src="{{ $imagen->url }}" class="d-block w-100 rounded" alt="{{ $producto->nombre }}" style="height: 400px; object-fit: contain; background-color: #f8f9fa;">
                                </div>
                            @endforeach
                        </div>
                        @if($producto->imagenes->count() > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        @endif
                    </div>
                    <!-- Miniaturas -->
                    <div class="row mt-2 gx-2">
                        @foreach($producto->imagenes as $key => $imagen)
                        <div class="col-3">
                            <img src="{{ $imagen->url }}" class="img-fluid rounded border cursor-pointer" onclick="$('#productCarousel').carousel({{ $key }})" style="cursor: pointer;">
                        </div>
                        @endforeach
                    </div>
                @else
                    <img src="{{ asset('images/default-product.jpg') }}" class="img-fluid rounded" alt="Sin imagen">
                @endif
            </div>
        </div>
    </div>

    <!-- Detalles del Producto -->
    <div class="col-md-6 mb-4">
        <h1 class="h2 fw-bold">{{ $producto->nombre }}</h1>
        <div class="d-flex align-items-center mb-2">
            <div class="text-warning me-2">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star {{ $i <= $producto->rating_promedio ? '' : 'text-muted' }}"></i>
                @endfor
            </div>
            <span class="text-muted small">({{ $producto->resenas->count() }} reseñas)</span>
        </div>
        
        <p class="h3 text-primary fw-bold my-3">
            Bs. {{ number_format($producto->precio_actual, 2, ',', '.') }}
            @if($producto->oferta)
                <span class="text-muted text-decoration-line-through fs-6 ms-2">Bs. {{ number_format($producto->precio, 2, ',', '.') }}</span>
                <span class="badge bg-success ms-2">-{{ $producto->descuento_porcentaje }}%</span>
            @endif
        </p>

        <p class="lead">{{ Str::limit($producto->descripcion, 150) }}</p>

        <div class="card bg-light border-0 mb-4">
            <div class="card-body">
                <p class="mb-1"><strong>Vendedor:</strong> <a href="#">{{ $producto->vendedor->nombre_comercial ?? 'Vendedor' }}</a></p>
                <p class="mb-1"><strong>Estado:</strong> {{ $producto->condicion ?? 'Nuevo' }}</p>
                <p class="mb-0"><strong>Stock:</strong> 
                    @if($producto->stock > 0)
                        <span class="text-success"><i class="fas fa-check-circle"></i> Disponible ({{ $producto->stock }})</span>
                    @else
                        <span class="text-danger"><i class="fas fa-times-circle"></i> Agotado</span>
                    @endif
                </p>
            </div>
        </div>

        @if($producto->stock > 0)
        <form action="{{ route('carrito.add') }}" method="POST" class="mb-4">
            @csrf
            <input type="hidden" name="producto_id" value="{{ $producto->id }}">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <label for="cantidad" class="col-form-label fw-bold">Cantidad:</label>
                </div>
                <div class="col-auto">
                    <select name="cantidad" id="cantidad" class="form-select">
                        @for($i = 1; $i <= min($producto->stock, 10); $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-shopping-cart me-2"></i> Agregar al Carrito
                    </button>
                </div>
            </div>
        </form>
        @endif

        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary flex-grow-1"><i class="far fa-heart me-1"></i> Deseos</button>
            <button class="btn btn-outline-secondary flex-grow-1"><i class="fas fa-exchange-alt me-1"></i> Comparar</button>
        </div>
    </div>
</div>

<!-- Pestañas de Información -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs" id="productTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="desc-tab" data-bs-toggle="tab" href="#descripcion" role="tab">Descripción</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="specs-tab" data-bs-toggle="tab" href="#especificaciones" role="tab">Especificaciones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="questions-tab" data-bs-toggle="tab" href="#preguntas" role="tab">Preguntas</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="productTabsContent">
                    <div class="tab-pane fade show active" id="descripcion" role="tabpanel">
                        {!! nl2br(e($producto->descripcion)) !!}
                    </div>
                    <div class="tab-pane fade" id="especificaciones" role="tabpanel">
                        <table class="table table-striped">
                            <tbody>
                                @if($producto->especificaciones)
                                    @foreach($producto->especificaciones as $nombre => $valor)
                                    <tr>
                                        <th style="width: 30%">{{ $nombre }}</th>
                                        <td>{{ $valor }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="2">No hay especificaciones disponibles.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="preguntas" role="tabpanel">
                        <h5>Preguntas sobre el producto</h5>
                        @if($preguntas->count() > 0)
                            @foreach($preguntas as $pregunta)
                                <div class="mb-3 border-bottom pb-2">
                                    <p class="mb-1 fw-bold"><i class="far fa-comment-dots text-muted"></i> {{ $pregunta->pregunta }}</p>
                                    @if($pregunta->respuesta)
                                        <p class="ms-3 mb-0 text-muted"><i class="fas fa-reply text-success"></i> {{ $pregunta->respuesta }}</p>
                                    @else
                                        <p class="ms-3 mb-0 text-muted fst-italic">Pendiente de respuesta...</p>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <p>Aún no hay preguntas. ¡Sé el primero!</p>
                        @endif
                        
                        @auth
                            <form action="#" class="mt-4"> <!-- Actualizar ruta de preguntas -->
                                <div class="mb-3">
                                    <label for="pregunta" class="form-label">Escribe tu pregunta</label>
                                    <textarea class="form-control" id="pregunta" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Enviar Pregunta</button>
                            </form>
                        @else
                            <p class="mt-3"><a href="{{ route('login') }}">Inicia sesión</a> para hacer una pregunta.</p>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
