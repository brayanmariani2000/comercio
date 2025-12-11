<button onclick="aprobarProducto({{ $producto->id }})" class="btn btn-success">Aprobar</button>
<button onclick="rechazarProducto({{ $producto->id }})" class="btn btn-danger">Rechazar</button>

<script>
function aprobarProducto(id) {
    if (!confirm('¿Aprobar este producto?')) return;
    
    fetch(`/admin/productos/${id}/aprobar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ accion: 'aprobar' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function rechazarProducto(id) {
    const razon = prompt('Razón del rechazo:');
    if (!razon) return;
    
    fetch(`/admin/productos/${id}/aprobar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ accion: 'rechazar', razon: razon })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>