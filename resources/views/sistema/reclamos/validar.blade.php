<div class="text-center">
    <h2>Validar Compra</h2>
    <p>Escanea el QR o ingresa el serial de tu ticket</p>
    
    <input type="text" id="codigoInput" class="form-control mb-2" placeholder="Código QR o Serial">
    <button onclick="validarCompra()" class="btn btn-primary">Validar</button>
    
    <div id="resultado" class="mt-3"></div>
</div>

<script>
async function validarCompra() {
    const codigo = document.getElementById('codigoInput').value;
    if (!codigo) return alert('Ingresa un código');
    
    const res = await fetch('/api/validar-compra', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            codigo: codigo.includes('QR-') ? codigo : null,
            serial: codigo.includes('VEN-') ? codigo : null
        })
    });
    
    const data = await res.json();
    const cont = document.getElementById('resultado');
    
    if (data.valido) {
        cont.innerHTML = `
            <div class="alert alert-success">
                <h4>✅ COMPRA VÁLIDA</h4>
                <p>Cliente: ${data.pedido.cliente.nombre}</p>
                <p>Producto: ${data.pedido.vendedor.nombre}</p>
                <p>Total: Bs. ${data.pedido.total}</p>
                <img src="${data.qr_image}" width="100" class="mt-2">
            </div>
        `;
    } else {
        cont.innerHTML = `<div class="alert alert-danger">❌ Código inválido</div>`;
    }
}
</script>