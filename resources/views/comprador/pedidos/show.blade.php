@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: #0f172a; min-height: 100vh;">
    <div class="row">
        <div class="col-md-3 col-lg-2 d-none d-md-block">
            @include('partials.comprador-sidebar')
        </div>

        <div class="col-md-9 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4 text-white">
                <div>
                    <h2 class="fw-bold mb-1">Pedido #{{ $pedido->numero_pedido }}</h2>
                    <p class="text-muted mb-0">Realizado el {{ $pedido->created_at->format('d M, Y h:i A') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('comprador.pedidos.index') }}" class="btn btn-outline-light rounded-pill">
                        <i class="fas fa-arrow-left me-2"></i> Volver
                    </a>
                    @if($pedido->estado_pago == 'confirmado')
                        <button class="btn btn-info rounded-pill text-white">
                            <i class="fas fa-file-invoice me-2"></i> Factura
                        </button>
                    @endif
                </div>
            </div>

            <div class="row g-4">
                <!-- Order Items -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4" style="background-color: #1e293b;">
                        <div class="card-header border-0 bg-transparent py-3">
                            <h5 class="mb-0 text-white fw-bold">Productos</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover mb-0 align-middle">
                                    <thead class="bg-dark text-uppercase small text-muted">
                                        <tr>
                                            <th class="ps-4">Producto</th>
                                            <th>Precio</th>
                                            <th>Cant.</th>
                                            <th class="text-end pe-4">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pedido->items as $item)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded bg-white p-1 me-3" style="width: 50px; height: 50px;">
                                                            <img src="{{ $item->producto->imagen_url }}" class="w-100 h-100" style="object-fit: contain;">
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-white">{{ $item->producto->nombre }}</h6>
                                                            <small class="text-muted">{{ Str::limit($item->producto->descripcion, 30) }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>${{ number_format($item->precio_unitario, 2) }}</td>
                                                <td>{{ $item->cantidad }}</td>
                                                <td class="text-end pe-4 fw-bold">${{ number_format($item->subtotal, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="border-top border-secondary border-opacity-25">
                                        <tr>
                                            <td colspan="3" class="text-end text-muted pt-3">Subtotal:</td>
                                            <td class="text-end pe-4 pt-3 text-white">${{ number_format($pedido->items->sum('subtotal'), 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end text-muted">Envío:</td>
                                            <td class="text-end pe-4 text-white">${{ number_format($pedido->costo_envio, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end text-white fw-bold pt-3 pb-3">Total:</td>
                                            <td class="text-end pe-4 text-primary fw-bold pt-3 pb-3 fs-5">${{ number_format($pedido->total, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline / Status -->
                    <div class="card border-0 shadow-sm" style="background-color: #1e293b;">
                        <div class="card-header border-0 bg-transparent py-3">
                            <h5 class="mb-0 text-white fw-bold">Estado del Pedido</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between text-center position-relative mb-3">
                                <div class="position-absolute top-50 start-0 translate-middle-y bg-secondary w-100" style="height: 2px; z-index: 0;"></div>
                                
                                @php
                                    $states = ['pendiente', 'confirmado', 'enviado', 'entregado'];
                                    $currentStateIndex = array_search($pedido->estado_pedido, $states);
                                    if ($currentStateIndex === false) $currentStateIndex = -1;
                                @endphp

                                @foreach($states as $index => $state)
                                    <div class="position-relative bg-dark px-2" style="z-index: 1;">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 {{ $index <= $currentStateIndex ? 'bg-primary text-white' : 'bg-secondary text-muted' }}" style="width: 40px; height: 40px;">
                                            @if($state == 'pendiente') <i class="fas fa-clock"></i>
                                            @elseif($state == 'confirmado') <i class="fas fa-check"></i>
                                            @elseif($state == 'enviado') <i class="fas fa-truck"></i>
                                            @elseif($state == 'entregado') <i class="fas fa-box-open"></i>
                                            @endif
                                        </div>
                                        <small class="text-uppercase fw-bold {{ $index <= $currentStateIndex ? 'text-primary' : 'text-muted' }}">{{ ucfirst($state) }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="col-lg-4">
                    <!-- QR Code -->
                    <div class="card border-0 shadow-sm mb-4" style="background-color: #1e293b;">
                        <div class="card-body text-center">
                            @if(isset($qr_base64))
                                <img src="data:image/png;base64,{{ $qr_base64 }}" class="img-fluid rounded border border-white p-2 mb-3" style="max-width: 150px; background: white;">
                                <p class="text-muted small mb-0">Escanea para ver detalles rápidos</p>
                            @else
                                <p class="text-muted">QR no disponible</p>
                            @endif
                        </div>
                    </div>

                    <!-- Shipping Info -->
                    <div class="card border-0 shadow-sm mb-4" style="background-color: #1e293b;">
                        <div class="card-header border-0 bg-transparent py-3">
                            <h5 class="mb-0 text-white fw-bold">Envío</h5>
                        </div>
                        <div class="card-body text-white">
                            <p class="mb-1 text-muted small">Dirección:</p>
                            <p class="mb-3">{{ $pedido->direccion_envio }}<br>{{ $pedido->ciudad_envio }}, {{ $pedido->estado_envio }}</p>
                            
                            <p class="mb-1 text-muted small">Método:</p>
                            <p class="mb-3">{{ $pedido->metodo_envio_id ? 'Envío Estándar' : 'Por acordar' }}</p> <!-- Simplified, ideal to use relationship name -->

                            @if($pedido->codigo_seguimiento)
                                <div class="alert alert-info bg-opacity-10 border-info text-info mb-0">
                                    <small class="d-block fw-bold display-block">Tracking ID:</small>
                                    {{ $pedido->codigo_seguimiento }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="card border-0 shadow-sm" style="background-color: #1e293b;">
                        <div class="card-header border-0 bg-transparent py-3">
                            <h5 class="mb-0 text-white fw-bold">Pago</h5>
                        </div>
                        <div class="card-body text-white">
                             <p class="mb-1 text-muted small">Método:</p>
                            <p class="mb-3 text-capitalize">{{ str_replace('_', ' ', $pedido->metodo_pago) }}</p>

                            <p class="mb-1 text-muted small">Estado:</p>
                            <span class="badge {{ $pedido->estado_pago == 'confirmado' ? 'bg-success' : ($pedido->estado_pago == 'pendiente' ? 'bg-warning' : 'bg-primary') }}">
                                {{ ucfirst($pedido->estado_pago) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
