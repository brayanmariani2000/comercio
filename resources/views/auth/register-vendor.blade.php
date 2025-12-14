@extends('layouts.app')

@section('title', 'Completar Perfil de Vendedor')

@push('styles')
<style>
    .vendor-register-container {
        min-height: calc(100vh - 100px);
        padding: 50px 0;
        background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.1), transparent 40%),
                    radial-gradient(circle at bottom left, rgba(40, 167, 69, 0.05), transparent 40%);
    }

    .form-card {
        background: rgba(30, 30, 30, 0.8);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        color: #fff;
        font-size: 2.2rem;
        margin-bottom: 10px;
        text-align: center;
    }

    .page-subtitle {
        color: var(--silver);
        text-align: center;
        margin-bottom: 40px;
        font-family: 'Montserrat', sans-serif;
    }

    .form-label {
        color: var(--primary-gold);
        font-weight: 500;
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
        padding: 12px;
        border-radius: 8px;
    }

    .form-control:focus, .form-select:focus {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--primary-gold);
        color: #fff;
        box-shadow: 0 0 0 0.2rem rgba(212, 175, 55, 0.25);
    }

    .btn-submit {
        background: linear-gradient(135deg, var(--primary-gold), var(--secondary-gold));
        color: #000;
        font-weight: 700;
        width: 100%;
        padding: 15px;
        border-radius: 30px;
        border: none;
        margin-top: 30px;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: transform 0.3s;
    }

    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
    }

    .section-divider {
        height: 1px;
        background: rgba(255, 255, 255, 0.1);
        margin: 30px 0;
    }
</style>
@endpush

@section('content')
<div class="vendor-register-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card fade-in">
                    <h1 class="page-title">Completa tu Perfil de Vendedor</h1>
                    <p class="page-subtitle">Necesitamos algunos datos adicionales para configurar tu tienda.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('vendedor.registro.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Datos de la Empresa/Vendedor -->
                        <h4 class="text-white mb-3">Datos del Comercio</h4>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre Comercial de la Tienda</label>
                                <input type="text" class="form-control" name="nombre_comercial" required value="{{ old('nombre_comercial') }}" placeholder="Ej: TechStore Monagas">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Razón Social </label>
                                <input type="text" class="form-control" name="razon_social" required value="{{ old('razon_social') }}" placeholder="Ej: Inversiones Tech C.A.">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">RIF</label>
                                <input type="text" class="form-control" name="rif" required value="{{ old('rif') }}" placeholder="J-12345678-9">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Vendedor</label>
                                <select class="form-select" name="tipo_vendedor" required>
                                    <option value="empresa">Empresa</option>
                                    <option value="individual">Particular / Emprendedor</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción de tu Tienda</label>
                            <textarea class="form-control" name="descripcion" rows="3" placeholder="¿Qué vendes? Cuéntanos sobre tu negocio.">{{ old('descripcion') }}</textarea>
                        </div>

                        <div class="section-divider"></div>

                        <!-- Ubicación y Contacto -->
                        <h4 class="text-white mb-3">Ubicación Fiscal</h4>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado" required>
                                    <option value="Monagas" selected>Monagas</option>
                                    <!-- Se pueden cargar más -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ciudad</label>
                                <input type="text" class="form-control" name="ciudad" required value="{{ old('ciudad') }}" placeholder="Maturín">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección Fiscal Completa</label>
                            <input type="text" class="form-control" name="direccion_fiscal" required value="{{ old('direccion_fiscal') }}">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Teléfono de Contacto</label>
                                <input type="text" class="form-control" name="telefono" required value="{{ old('telefono') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email de Contacto</label>
                                <input type="email" class="form-control" name="email" required value="{{ old('email', auth()->user()->email) }}">
                            </div>
                        </div>

                        <div class="section-divider"></div>

                        <!-- Configuración de Venta -->
                        <h4 class="text-white mb-3">Configuración de Venta</h4>
                        
                        <div class="mb-3">
                            <label class="form-label">Métodos de Pago Aceptados</label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="metodos_pago[]" value="pago_movil" checked id="mp">
                                        <label class="form-check-label text-white" for="mp">Pago Móvil</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="metodos_pago[]" value="transferencia_bancaria" checked id="transfer">
                                        <label class="form-check-label text-white" for="transfer">Transferencia</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="metodos_pago[]" value="efectivo" id="efectivo">
                                        <label class="form-check-label text-white" for="efectivo">Efectivo ($)</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="metodos_pago[]" value="binance" id="binance">
                                        <label class="form-check-label text-white" for="binance">Binance / USDT</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="metodos_pago[]" value="zelle" id="zelle">
                                        <label class="form-check-label text-white" for="zelle">Zelle</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Zonas de Envío</label>
                             <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="zonas_envio[]" value="maturin_centro" checked id="z1">
                                        <label class="form-check-label text-white" for="z1">Maturín (Centro)</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="zonas_envio[]" value="maturin_norte" id="z2">
                                        <label class="form-check-label text-white" for="z2">Maturín (Zona Norte)</label>
                                    </div>
                                </div>
                                 <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="zonas_envio[]" value="maturin_sur" id="z3">
                                        <label class="form-check-label text-white" for="z3">Maturín (Zona Sur)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-divider"></div>

                        <!-- Documentacion -->
                        <h4 class="text-white mb-3">Documentación</h4>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Comprobante RIF (PDF/IMG)</label>
                                <input type="file" class="form-control" name="comprobante_rif" required accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Comprobante Domicilio (PDF/IMG)</label>
                                <input type="file" class="form-control" name="comprobante_domicilio" required accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Enviar Solicitud</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
