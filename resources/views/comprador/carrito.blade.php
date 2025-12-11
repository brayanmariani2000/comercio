@extends('layouts.app')

@section('title', 'Carrito de compras')
@section('content')
<h1><i class="fas fa-shopping-cart me-2"></i>Carrito de compras</h1>

@if($carrito->items->isEmpty())
    <div class="alert alert-info text-center">
        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
        <h4>Tu carrito está vacío</h4>
        <p>Agrega productos para continuar</p>
        <a href="{{ route('productos.index') }}" class="btn btn-primary">Ver productos</a>
    </div>
@else
    <div class="row">
        <div class="col-md-8">
            @foreach($carrito->items as $item)
            <div class="card mb-3">
                <div class="row g-0">
                    <div class="col-md-3">
                        <img src="{{ $item->producto->imagen_url }}" class="img-fluid rounded-start" alt="{{ $item->producto->nombre }}" style="height: 120px; object-fit: cover;">
                    </div>
                    <div class="col-md-9">
                        <div class="card-body">
                            <h6 class="card-title">{{ $item->producto->nombre }}</h6>
                            <p class="text-muted mb-1">Vendido por: {{ $item->producto->vendedor->nombre_comercial }}</p>
                            <p class="mb-2"><strong>Bs. {{ number_format($item->precio_unitario, 2, ',', '.') }}</strong></p>
                            <div class="d-flex align-items-center">
                                <form method="POST" action="{{ route('comprador.carrito.update', $item->id) }}" class="me-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" name="cantidad" value="{{ $item->cantidad }}" min="1" max="{{ $item->producto->stock }}" class="form-control form-control-sm" style="width: 80px;">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary mt-1">Actualizar</button>
                                </form>
                                <form method="POST" action="{{ route('comprador.carrito.destroy', $item->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                            @if($item->producto->stock < $item->cantidad)
                                <small class="text-danger">⚠️ Stock insuficiente ({{ $item->producto->stock }} disponibles)</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Resumen del pedido</h5>
                </div>
                <div class="card-body">
                    <p><strong>Subtotal:</strong> Bs. {{ number_format($subtotal, 2, ',', '.') }}</p>
                    <p><strong>Artículos:</strong> {{ $totalItems }}</p>
                    <button class="btn btn-success w-100 mt-3">Continuar al pago</button>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
<form id="checkoutForm">
    @csrf
    <select name="direccion_envio_id" required>
        @foreach($direcciones as $dir)
            <option value="{{ $dir->id }}">{{ $dir->alias }} - {{ $dir->direccion_completa }}</option>
        @endforeach
    </select>
    
    <select name="metodo_envio_id" required>
        @foreach($metodosEnvio as $m)
            <option value="{{ $m->id }}">{{ $m->nombre }} ({{ $m->costo_base }})</option>
        @endforeach
    </select>
    
    <select name="metodo_pago" required>
        <option value="transferencia_bancaria">Transferencia Bancaria</option>
        <option value="pago_movil">Pago Móvil</option>
        <option value="efectivo">Efectivo</option>
    </select>
    
    <input type="text" name="cupon_codigo" placeholder="Cupón (opcional)">
    
    <button type="submit" class="btn btn-danger">Finalizar compra</button>
</form>

<script>
document.getElementById('checkoutForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = new FormData(e.target);
    const obj = {};
    data.forEach((value, key) => obj[key] = value);
    
    const res = await fetch('/api/pedidos', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        body: JSON.stringify(obj)
    });
    
    const result = await res.json();
    if (result.success) {
        alert('¡Pedido creado! Guarda tu QR y serial.');
        window.location.href = '/comprador/pedidos/' + result.pedido.id;
    } else {
        alert('Error: ' + result.message);
    }
});
</script>