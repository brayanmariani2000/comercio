@extends('layouts.app')

@section('title', $categoria->nombre . ' | Monagas Vende')

@section('content')
<div class="container-fluid py-4 fade-in-up">
    <!-- Header Banner -->
    <div class="position-relative mb-4 rounded-3 overflow-hidden shadow-lg" style="height: 200px; background: linear-gradient(45deg, #0f172a, #334155);">
         <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center p-5">
             <div class="z-2">
                 <h1 class="display-4 fw-bold text-white mb-2">{{ $categoria->nombre }}</h1>
                 <p class="lead text-white-50 mb-0">{{ $categoria->descripcion }}</p>
                 <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mt-3 text-white-50">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50 text-decoration-none">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('categorias.index') }}" class="text-white-50 text-decoration-none">Categorías</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">{{ $categoria->nombre }}</li>
                    </ol>
                </nav>
             </div>
         </div>
         <div class="position-absolute top-0 end-0 h-100 w-50" style="background: radial-gradient(circle at center, rgba(0, 243, 255, 0.1) 0%, transparent 70%);"></div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="glass-card p-3 mb-4 sticky-top" style="top: 20px; z-index:1000">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-white"><i class="fas fa-filter me-2 text-primary"></i>Filtros</h5>
                    @if(request()->anyFilled(['marca', 'precio_min', 'precio_max', 'condicion']))
                        <a href="{{ route('categorias.show', $categoria->slug) }}" class="btn btn-sm btn-link text-white-50 text-decoration-none">Limpiar</a>
                    @endif
                </div>

                <form action="{{ route('categorias.show', $categoria->slug) }}" method="GET" id="filterForm">
                    <!-- Price Filter -->
                    <div class="mb-4">
                        <label class="form-label text-white-50 small text-uppercase fw-bold mb-2">Precio</label>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text bg-dark border-secondary text-white-50">$</span>
                            <input type="number" name="precio_min" class="form-control bg-dark text-white border-secondary" placeholder="Min" value="{{ request('precio_min') }}">
                            <span class="input-group-text bg-dark border-secondary text-white-50">-</span>
                            <input type="number" name="precio_max" class="form-control bg-dark text-white border-secondary" placeholder="Max" value="{{ request('precio_max') }}">
                        </div>
                    </div>

                    <!-- Brand Filter -->
                     @if(!empty($atributos['marcas']) && $atributos['marcas']->count() > 0)
                    <div class="mb-4">
                         <label class="form-label text-white-50 small text-uppercase fw-bold mb-2">Marcas</label>
                         <div class="d-flex flex-column gap-2 max-h-200 overflow-auto custom-scrollbar">
                            @foreach($atributos['marcas'] as $marca)
                                <div class="form-check">
                                    <input class="form-check-input bg-dark border-secondary" type="checkbox" name="marca[]" value="{{ $marca }}" id="brand_{{ Str::slug($marca) }}" {{ in_array($marca, (array)request('marca')) ? 'checked' : '' }}>
                                    <label class="form-check-label text-white-50" for="brand_{{ Str::slug($marca) }}">
                                        {{ $marca }}
                                    </label>
                                </div>
                            @endforeach
                         </div>
                    </div>
                    @endif
                    
                    <!-- Condition Filter -->
                    <div class="mb-4">
                        <label class="form-label text-white-50 small text-uppercase fw-bold mb-2">Condición</label>
                        <div class="form-check">
                             <input class="form-check-input bg-dark border-secondary" type="radio" name="condicion" value="nuevo" id="cond_nuevo" {{ request('condicion') == 'nuevo' ? 'checked' : '' }}>
                             <label class="form-check-label text-white-50" for="cond_nuevo">Nuevo</label>
                        </div>
                         <div class="form-check">
                             <input class="form-check-input bg-dark border-secondary" type="radio" name="condicion" value="usado" id="cond_usado" {{ request('condicion') == 'usado' ? 'checked' : '' }}>
                             <label class="form-check-label text-white-50" for="cond_usado">Usado</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Aplicar Filtros</button>
                </form>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <!-- Sorting & Info -->
            <div class="d-flex justify-content-between align-items-center mb-4 text-white-50">
                <span>Mostrando {{ $productos->firstItem() ?? 0 }} - {{ $productos->lastItem() ?? 0 }} de {{ $productos->total() }} resultados</span>
                <div class="d-flex align-items-center">
                    <label class="me-2 small">Ordenar por:</label>
                    <select class="form-select form-select-sm bg-dark text-white border-secondary" style="width: auto;" onchange="document.getElementById('hiddenOrden').value=this.value; document.getElementById('filterForm').submit();">
                        <option value="mas_relevantes" {{ request('orden') == 'mas_relevantes' ? 'selected' : '' }}>Relevancia</option>
                        <option value="precio_asc" {{ request('orden') == 'precio_asc' ? 'selected' : '' }}>Menor Precio</option>
                        <option value="precio_desc" {{ request('orden') == 'precio_desc' ? 'selected' : '' }}>Mayor Precio</option>
                        <option value="mas_recientes" {{ request('orden') == 'mas_recientes' ? 'selected' : '' }}>Más Recientes</option>
                    </select>
                     <!-- Hidden Sort Input for Main Form -->
                    <input type="hidden" name="orden" id="hiddenOrden" form="filterForm" value="{{ request('orden', 'mas_relevantes') }}">
                </div>
            </div>

            <div class="row g-4">
                @forelse($productos as $producto)
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 border-0 bg-dark text-white shadow-sm hover-elevate overflow-hidden">
                             <!-- Badge -->
                             @if($producto->oferta)
                                <div class="position-absolute top-0 start-0 m-2 badge bg-danger rounded-pill shadow-sm z-2">-{{ $producto->descuento_porcentaje }}%</div>
                             @elseif($producto->nuevo)
                                <div class="position-absolute top-0 start-0 m-2 badge bg-success rounded-pill shadow-sm z-2">NUEVO</div>
                             @endif

                             <!-- Image -->
                            <div class="position-relative item-img-container bg-white" style="height: 250px;">
                                <a href="{{ route('productos.show', $producto->id) }}">
                                    <img src="{{ $producto->imagen_url }}" class="card-img-top w-100 h-100" style="object-fit: contain;" alt="{{ $producto->nombre }}">
                                </a>
                                <!-- Actions -->
                                <div class="position-absolute bottom-0 end-0 m-2 d-flex gap-2">
                                     <button class="btn btn-light rounded-circle shadow-sm btn-icon-only text-danger" title="Añadir a Wishlist">
                                        <i class="far fa-heart"></i>
                                     </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <small class="text-primary text-uppercase fw-bold" style="font-size: 0.7rem;">{{ $producto->marca }}</small>
                                <h5 class="card-title text-truncate my-1">
                                    <a href="{{ route('productos.show', $producto->id) }}" class="text-white text-decoration-none hover-underline">
                                        {{ $producto->nombre }}
                                    </a>
                                </h5>
                                
                                <div class="d-flex align-items-baseline gap-2 mb-2">
                                    <h4 class="fw-bold mb-0 text-white">${{ number_format($producto->precio_actual, 2) }}</h4>
                                    @if($producto->oferta)
                                        <small class="text-muted text-decoration-line-through">${{ number_format($producto->precio, 2) }}</small>
                                    @endif
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="d-flex align-items-center text-warning small">
                                        <i class="fas fa-star me-1"></i>
                                        <span>{{ number_format($producto->calificacion_promedio, 1) }}</span>
                                        <span class="text-muted ms-1">({{ $producto->ventas }} vendidos)</span>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="addToCart({{ $producto->id }})">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                         <div class="mb-3"><i class="fas fa-search fa-3x text-muted opacity-25"></i></div>
                         <h4 class="text-white">No se encontraron productos</h4>
                         <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-5 d-flex justify-content-center">
                {{ $productos->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    .glass-card {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 12px;
    }
    .hover-elevate {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-elevate:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.3) !important;
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #475569;
        border-radius: 3px;
    }
    .btn-icon-only {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
</style>

<script>
    function addToCart(productId) {
        // Implementación simple de addToCart o llamada a API
        // Podría usar fetch a la ruta del carrito
        fetch("{{ route('comprador.carrito.add') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                producto_id: productId,
                cantidad: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Mostrar toast o notificación
                alert('Producto añadido al carrito');
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
@endsection
