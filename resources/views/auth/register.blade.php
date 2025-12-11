@extends('layouts.app')

@section('title', 'Registro - Monagas Vende')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Crear cuenta nueva</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('auth.register') }}" id="registerForm">
                    @csrf
                    
                    <!-- Información personal -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Información Personal</h5>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nombre completo *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Correo electrónico *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="cedula" class="form-label">Cédula de identidad *</label>
                            <input type="text" class="form-control @error('cedula') is-invalid @enderror" 
                                   id="cedula" name="cedula" value="{{ old('cedula') }}" required
                                   placeholder="Ej: V-12345678">
                            @error('cedula')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono *</label>
                            <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                   id="telefono" name="telefono" value="{{ old('telefono') }}" required
                                   placeholder="Ej: 0414-1234567">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                            <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" 
                                   id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}">
                            @error('fecha_nacimiento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="genero" class="form-label">Género</label>
                            <select class="form-select @error('genero') is-invalid @enderror" id="genero" name="genero">
                                <option value="">Seleccionar...</option>
                                <option value="masculino" {{ old('genero') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                                <option value="femenino" {{ old('genero') == 'femenino' ? 'selected' : '' }}>Femenino</option>
                                <option value="otro" {{ old('genero') == 'otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('genero')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Dirección -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3"><i class="fas fa-map-marker-alt me-2"></i>Dirección</h5>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="direccion" class="form-label">Dirección completa</label>
                            <textarea class="form-control @error('direccion') is-invalid @enderror" 
                                      id="direccion" name="direccion" rows="2">{{ old('direccion') }}</textarea>
                            @error('direccion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        @if(isset($estados) && $estados->count() > 0)
                        <div class="col-md-6 mb-3">
                            <label for="estado_id" class="form-label">Estado *</label>
                            <select class="form-select @error('estado_id') is-invalid @enderror" 
                                    id="estado_id" name="estado_id" required>
                                <option value="">Seleccionar estado...</option>
                                @foreach($estados as $estado)
                                    <option value="{{ $estado->id }}" {{ old('estado_id') == $estado->id ? 'selected' : '' }}>
                                        {{ $estado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('estado_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="ciudad_id" class="form-label">Ciudad *</label>
                            <select class="form-select @error('ciudad_id') is-invalid @enderror" 
                                    id="ciudad_id" name="ciudad_id" required>
                                <option value="">Seleccionar estado primero...</option>
                                @if(isset($ciudades) && $ciudades->count() > 0)
                                    @foreach($ciudades as $ciudad)
                                        <option value="{{ $ciudad->id }}" 
                                                data-estado="{{ $ciudad->municipio->estado_id ?? '' }}"
                                                {{ old('ciudad_id') == $ciudad->id ? 'selected' : '' }}
                                                style="display: none;">
                                            {{ $ciudad->nombre }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('ciudad_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif
                        
                        <div class="col-md-6 mb-3">
                            <label for="codigo_postal" class="form-label">Código postal</label>
                            <input type="text" class="form-control @error('codigo_postal') is-invalid @enderror" 
                                   id="codigo_postal" name="codigo_postal" value="{{ old('codigo_postal') }}"
                                   placeholder="Ej: 6201">
                            @error('codigo_postal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="tipo_persona" class="form-label">Tipo de persona *</label>
                            <div class="d-flex">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="tipo_persona" 
                                           id="tipo_natural" value="natural" 
                                           {{ old('tipo_persona', 'natural') == 'natural' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="tipo_natural">
                                        Natural
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo_persona" 
                                           id="tipo_juridica" value="juridica" 
                                           {{ old('tipo_persona') == 'juridica' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tipo_juridica">
                                        Jurídica
                                    </label>
                                </div>
                            </div>
                            @error('tipo_persona')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Campo RIF (solo para persona jurídica) -->
                        <div class="col-md-6 mb-3" id="rifContainer" style="display: none;">
                            <label for="rif" class="form-label">RIF *</label>
                            <input type="text" class="form-control @error('rif') is-invalid @enderror" 
                                   id="rif" name="rif" value="{{ old('rif') }}"
                                   placeholder="Ej: J-12345678-9">
                            <small class="text-muted">Solo para persona jurídica</small>
                            @error('rif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Contraseñas -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3"><i class="fas fa-lock me-2"></i>Contraseña de acceso</h5>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Contraseña *</label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Mínimo 8 caracteres, con letras y números</small>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                                <button class="btn btn-outline-secondary" type="button" 
                                        onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Términos y condiciones -->
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input @error('acepto_terminos') is-invalid @enderror" 
                                   type="checkbox" id="acepto_terminos" name="acepto_terminos" 
                                   {{ old('acepto_terminos') ? 'checked' : '' }} required>
                            <label class="form-check-label" for="acepto_terminos">
                                Acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#terminosModal">términos y condiciones</a> *
                            </label>
                            @error('acepto_terminos')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="recibir_ofertas" 
                                   name="recibir_ofertas" {{ old('recibir_ofertas') ? 'checked' : '' }}>
                            <label class="form-check-label" for="recibir_ofertas">
                                Deseo recibir ofertas y promociones por email
                            </label>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Registrarse
                        </button>
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Ya tengo cuenta
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de términos y condiciones -->
<div class="modal fade" id="terminosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Términos y Condiciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Aceptación de términos</h6>
                <p>Al registrarte en Monagas Vende, aceptas cumplir con estos términos y condiciones...</p>
                
                <h6>2. Uso de la plataforma</h6>
                <p>Te comprometes a usar la plataforma de manera responsable y legal...</p>
                
                <h6>3. Privacidad</h6>
                <p>Tus datos personales serán protegidos según nuestra política de privacidad...</p>
                
                <h6>4. Responsabilidades</h6>
                <p>Eres responsable de la veracidad de la información proporcionada...</p>
                
                <h6>5. Modificaciones</h6>
                <p>Nos reservamos el derecho de modificar estos términos en cualquier momento...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Mostrar/ocultar RIF según tipo de persona
document.querySelectorAll('input[name="tipo_persona"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const rifContainer = document.getElementById('rifContainer');
        const rifInput = document.getElementById('rif');
        
        if (this.value === 'juridica') {
            rifContainer.style.display = 'block';
            rifInput.required = true;
        } else {
            rifContainer.style.display = 'none';
            rifInput.required = false;
        }
    });
});

// Inicializar visibilidad del campo RIF
document.addEventListener('DOMContentLoaded', function() {
    const tipoJuridica = document.getElementById('tipo_juridica');
    if (tipoJuridica.checked) {
        document.getElementById('rifContainer').style.display = 'block';
        document.getElementById('rif').required = true;
    }
});

// Filtrar ciudades por estado
const estadoSelect = document.getElementById('estado_id');
const ciudadSelect = document.getElementById('ciudad_id');

if (estadoSelect && ciudadSelect) {
    estadoSelect.addEventListener('change', function() {
        const estadoId = this.value;
        const ciudades = ciudadSelect.querySelectorAll('option');
        
        // Mostrar todas las ciudades primero
        ciudades.forEach(option => {
            option.style.display = 'none';
            option.disabled = false;
        });
        
        // Mostrar solo ciudades del estado seleccionado
        if (estadoId) {
            ciudades.forEach(option => {
                if (option.value === '' || option.dataset.estado === estadoId) {
                    option.style.display = 'block';
                }
            });
            ciudadSelect.value = '';
        } else {
            ciudades.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                }
            });
        }
    });
    
    // Inicializar filtro
    if (estadoSelect.value) {
        estadoSelect.dispatchEvent(new Event('change'));
    }
}

// Función para mostrar/ocultar contraseña
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        button.classList.remove('fa-eye');
        button.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        button.classList.remove('fa-eye-slash');
        button.classList.add('fa-eye');
    }
}

// Validación personalizada del formulario
document.getElementById('registerForm').addEventListener('submit', function(e) {
    // Validar que las contraseñas coincidan
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirmation').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Las contraseñas no coinciden');
        return false;
    }
    
    // Validar formato de cédula
    const cedula = document.getElementById('cedula').value;
    if (cedula && !/^[VEJvej]-?\d{5,9}$/.test(cedula)) {
        e.preventDefault();
        alert('Formato de cédula inválido. Use V/E/J seguido de números (ej: V12345678)');
        return false;
    }
    
    // Validar teléfono
    const telefono = document.getElementById('telefono').value;
    if (telefono && !/^[0-9+()\s-]{10,20}$/.test(telefono)) {
        e.preventDefault();
        alert('Formato de teléfono inválido');
        return false;
    }
});
</script>
@endpush

<style>
.form-label.required:after {
    content: " *";
    color: #dc3545;
}
.card {
    border: none;
}
.card-header {
    border-radius: 10px 10px 0 0 !important;
}
.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}
.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}
.btn-success:hover {
    background: linear-gradient(135deg, #218838 0%, #1ba67e 100%);
}
</style>
@endsection