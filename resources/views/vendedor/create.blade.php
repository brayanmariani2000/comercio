<form id="productoForm" enctype="multipart/form-data">
    @csrf
    <input name="nombre" placeholder="Nombre del producto" required>
    <textarea name="descripcion" placeholder="Descripción" required></textarea>
    <input type="number" name="precio" step="0.01" placeholder="Precio (Bs.)" required>
    <input type="number" name="stock" placeholder="Stock" required>
    <select name="categoria_id" required>
        @foreach($categorias as $c)
            <option value="{{ $c->id }}">{{ $c->nombre }}</option>
        @endforeach
    </select>
    <input type="file" name="imagenes[]" multiple accept="image/*" required>
    <button type="submit" class="btn btn-primary">Publicar producto</button>
</form>

<script>
document.getElementById('productoForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    const res = await fetch('/vendedor/productos', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    
    const data = await res.json();
    if (data.success) {
        alert('Producto enviado para aprobación');
        window.location.href = '/vendedor/productos';
    } else {
        alert('Error: ' + JSON.stringify(data.errors || data.message));
    }
});
</script>