@extends('layouts.app')

@section('title', 'Publicar Producto | MONAGAS.TECH')

@push('styles')
<style>
    :root {
        --primary-gold: #D4AF37;
        --secondary-gold: #FFD700;
        --card-bg: rgba(30, 30, 30, 0.7);
        --input-bg: rgba(255, 255, 255, 0.05);
        --text-white: #FFFFFF;
        --text-muted: #C0C0C0;
    }

    .form-container {
        min-height: calc(100vh - 100px);
        padding: 40px 0;
    }

    .form-card {
        background: var(--card-bg);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 15px;
        padding: 30px;
        backdrop-filter: blur(10px);
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        color: var(--text-white);
        margin-bottom: 30px;
        border-bottom: 2px solid var(--primary-gold);
        padding-bottom: 15px;
        display: inline-block;
    }

    .form-label {
        color: var(--primary-gold);
        font-weight: 500;
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        background: var(--input-bg);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--text-white);
        border-radius: 8px;
        padding: 12px;
    }

    .form-control:focus, .form-select:focus {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--primary-gold);
        color: var(--text-white);
        box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
    }

    .btn-submit {
        background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
        color: #000;
        font-weight: 700;
        border: none;
        padding: 12px 30px;
        border-radius: 25px;
        transition: transform 0.3s;
        width: 100%;
    }

    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
    }

    .image-preview-container {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .image-preview {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--primary-gold);
    }
</style>
@endpush

@section('content')
<div class="container form-container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card fade-in">
                <h1 class="page-title">Publicar Nuevo Producto</h1>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('vendedor.productos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="nombre" class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required value="{{ old('nombre') }}" placeholder="Ej: Smartphone Galaxy S24 Ultra">
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="categoria_id" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria_id" name="categoria_id" required>
                                <option value="">Seleccionar Categoría</option>
                                @foreach($categorias as $categoria)
                                    <optgroup label="{{ $categoria->nombre }}">
                                        @foreach($categoria->subcategorias as $sub)
                                            <option value="{{ $sub->id }}" {{ old('categoria_id') == $sub->id ? 'selected' : '' }}>
                                                {{ $sub->nombre }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="condicion" class="form-label">Condición</label>
                            <select class="form-select" id="condicion" name="condicion" required>
                                <option value="nuevo" {{ old('condicion') == 'nuevo' ? 'selected' : '' }}>Nuevo</option>
                                <option value="usado" {{ old('condicion') == 'usado' ? 'selected' : '' }}>Usado</option>
                                <option value="reacondicionado" {{ old('condicion') == 'reacondicionado' ? 'selected' : '' }}>Reacondicionado</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="form-label">Descripción Detallada</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="5" required minlength="50" placeholder="Describe las características principales...">{{ old('descripcion') }}</textarea>
                        <small class="text-muted">Mínimo 50 caracteres</small>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="precio" class="form-label">Precio ($)</label>
                            <input type="number" step="0.01" class="form-control" id="precio" name="precio" required min="0" value="{{ old('precio') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="stock" class="form-label">Stock Disponible</label>
                            <input type="number" class="form-control" id="stock" name="stock" required min="1" value="{{ old('stock') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="stock_minimo" class="form-label">Stock Mínimo (Alerta)</label>
                            <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" min="1" value="{{ old('stock_minimo', 5) }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="imagenes" class="form-label">Imágenes del Producto (Mínimo 1)</label>
                        <input type="file" class="form-control" id="imagenes" name="imagenes[]" accept="image/*" multiple required onchange="previewImages(event)">
                        <div id="imagePreview" class="image-preview-container"></div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn-submit">
                            PUBLICAR PRODUCTO
                        </button>
                        <a href="{{ route('vendedor.dashboard') }}" class="btn btn-outline-light text-center">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function previewImages(event) {
        var preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        if (event.target.files) {
            [].forEach.call(event.target.files, function(file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
    }
</script>
@endpush
@endsection
