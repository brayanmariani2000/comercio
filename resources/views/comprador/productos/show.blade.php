<button onclick="addToCart({{ $producto->id }})" class="btn btn-success">
    <i class="fas fa-cart-plus"></i> Agregar al carrito
</button>

<script>
function addToCart(productId) {
    fetch('/api/carrito', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            producto_id: productId,
            cantidad: 1
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Producto agregado al carrito');
            // Actualizar badge del carrito
            updateCartCount();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function updateCartCount() {
    fetch('/api/carrito/summary')
        .then(r => r.json())
        .then(data => {
            document.getElementById('cart-count').textContent = data.total_items;
        });
}
</script>