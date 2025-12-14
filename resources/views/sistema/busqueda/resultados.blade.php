@extends('layouts.app')

@section('title', 'Resultados de búsqueda | Monagas Vende')

@section('content')
<div class="container-fluid py-4 fade-in-up">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h1 class="h3 text-white mb-0">
                @if(request('q'))
                    Resultados para: <span class="text-neon-cyan">"{{ request('q') }}"</span>
                @else
                    Explorar productos
                @endif
            </h1>
            <p class="text-muted small mb-0">{{ $productos->total() }} productos encontrados</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="glass-card p-3 sticky-top" style="top: 20px; z-index:1000">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-white"><i class="fas fa-filter me-2 text-primary"></i>Filtros</h5>
                    @if(request()->anyFilled(['categoria', 'precio_min', 'precio_max', 'envio_gratis', 'oferta']))
                        <a href="{{ route('busqueda', ['q' => request('q')]) }}" class="btn btn-sm btn-link text-white-50 text-decoration-none">Limpiar</a>
                    @endif
                </div>

                <form action="{{ route('busqueda') }}" method="GET" id="searchFilterForm">
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    
                    <!-- Category Filter -->
                    <div class="mb-4">
                         <label class="form-label text-white-50 small text-uppercase fw-bold mb-2">Categoría</label>
                         <select name="categoria" class="form-select form-select-sm bg-dark text-white border-secondary mb-2" onchange="this.form.submit()">
                            <option value="">Todas las categorías</option>
                            @foreach(\App\Models\Categoria::activas()->principales()->get() as $cat)
                                <option value="{{ $cat->id }}" {{ request('categoria') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                            @endforeach
                         </select>
                    </div>

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

                    <!-- Other Filters -->
                    <div class="mb-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" name="envio_gratis" value="1" id="envio_gratis" {{ request('envio_gratis') ? 'checked' : '' }}>
                            <label class="form-check-label text-white-50" for="envio_gratis">Envío Gratis</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" name="oferta" value="1" id="oferta" {{ request('oferta') ? 'checked' : '' }}>
                            <label class="form-check-label text-white-50" for="oferta">En Oferta</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Aplicar Filtros</button>
                </form>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            @if($productos->count() > 0)
                <div class="d-flex justify-content-end mb-3">
                     <select class="form-select form-select-sm bg-dark text-white border-secondary" style="width: auto;" onchange="document.getElementById('hiddenSort').value=this.value; document.getElementById('searchFilterForm').submit();">
                        <option value="mas_relevantes" {{ request('orden') == 'mas_relevantes' ? 'selected' : '' }}>Más relevantes</option>
                        <option value="precio_asc" {{ request('orden') == 'precio_asc' ? 'selected' : '' }}>Menor Precio</option>
                        <option value="precio_desc" {{ request('orden') == 'precio_desc' ? 'selected' : '' }}>Mayor Precio</option>
                    </select>
                    <input type="hidden" name="orden" id="hiddenSort" form="searchFilterForm" value="{{ request('orden') }}">
                </div>

                <div class="row g-4">
                    @foreach($productos as $producto)
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
                               </div>
                           </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-5 d-flex justify-content-center">
                    {{ $productos->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5 glass-card">
                    <div class="mb-3">
                         <i class="fas fa-search fa-4x text-muted opacity-25"></i>
                    </div>
                    <h3 class="text-white mb-2">No encontramos resultados</h3>
                    <p class="text-muted mb-4">No hay productos que coincidan con "{{ request('q') }}"</p>
                    <a href="{{ route('home') }}" class="btn btn-outline-primary rounded-pill px-4">Volver al inicio</a>
                </div>
            @endif
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
</style>
@endsection
