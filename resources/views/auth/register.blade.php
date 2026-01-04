@extends('layouts.app')

@section('title', 'Unirse a la Comunidad Exclusiva')

@section('content')
<div class="register-page">
    <div class="register-container">
        <!-- Header -->
        <div class="register-header">
            <h1 class="register-logo">
                MONAGAS<span>.TECH</span>
            </h1>
        </div>
        
        <!-- Pasos del registro -->
        <div class="register-steps">
            <div class="steps-connector"></div>
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Información</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label">Dirección</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Seguridad</div>
            </div>
        </div>
        
        <!-- Formulario -->
        <div class="register-form-container">
            <div class="form-header">
                <h2 class="form-title">Crear Cuenta</h2>
                <div class="form-icon">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 35C28.2843 35 35 28.2843 35 20C35 11.7157 28.2843 5 20 5C11.7157 5 5 11.7157 5 20C5 28.2843 11.7157 35 20 35Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M15 20L18.5 23.5L25 16.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
            
            <div class="form-body">
                <form method="POST" action="{{ route('auth.register') }}" id="registerForm">
                    @csrf
                    
                    <!-- Sección 1: Información Personal -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <span class="section-icon">👤</span>
                                Información Personal
                            </h3>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group-elegant">
                                <label class="form-label-elegant required" for="name">Nombre Completo</label>
                                <div class="form-input-container">
                                    <input type="text" 
                                           class="form-input-elegant @error('name') error @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="Juan Pérez"
                                           required>
                                    <div class="input-icon">👤</div>
                                </div>
                                @error('name')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group-elegant">
                                <label class="form-label-elegant required" for="email">Correo Electrónico</label>
                                <div class="form-input-container">
                                    <input type="email" 
                                           class="form-input-elegant @error('email') error @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           placeholder="juan@email.com"
                                           required>
                                    <div class="input-icon">✉️</div>
                                </div>
                                @error('email')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group-elegant">
                                <label class="form-label-elegant required" for="cedula">Cédula de Identidad</label>
                                <div class="form-input-container">
                                    <input type="text" 
                                           class="form-input-elegant @error('cedula') error @enderror" 
                                           id="cedula" 
                                           name="cedula" 
                                           value="{{ old('cedula') }}" 
                                           placeholder="V-12345678"
                                           required>
                                    <div class="input-icon">🆔</div>
                                </div>
                                @error('cedula')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group-elegant">
                                <label class="form-label-elegant required" for="telefono">Teléfono</label>
                                <div class="form-input-container">
                                    <input type="text" 
                                           class="form-input-elegant @error('telefono') error @enderror" 
                                           id="telefono" 
                                           name="telefono" 
                                           value="{{ old('telefono') }}" 
                                           placeholder="0414-1234567"
                                           required>
                                    <div class="input-icon">📱</div>
                                </div>
                                @error('telefono')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group-elegant">
                                <label class="form-label-elegant" for="fecha_nacimiento">Fecha de Nacimiento</label>
                                <div class="form-input-container">
                                    <input type="date" 
                                           class="form-input-elegant @error('fecha_nacimiento') error @enderror" 
                                           id="fecha_nacimiento" 
                                           name="fecha_nacimiento" 
                                           value="{{ old('fecha_nacimiento') }}">
                                    <div class="input-icon">📅</div>
                                </div>
                                @error('fecha_nacimiento')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group-elegant">
                                <label class="form-label-elegant" for="genero">Género</label>
                                <div class="custom-select-container">
                                    <select class="form-input-elegant @error('genero') error @enderror" 
                                            id="genero" 
                                            name="genero">
                                        <option value="">Seleccionar...</option>
                                        <option value="masculino" {{ old('genero') == 'masculino' ? 'selected' : '' }}>Masculino</option>
                                        <option value="femenino" {{ old('genero') == 'femenino' ? 'selected' : '' }}>Femenino</option>
                                        <option value="otro" {{ old('genero') == 'otro' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                </div>
                                @error('genero')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección 2: Dirección y Tipo de Persona -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <span class="section-icon">📍</span>
                                Dirección y Tipo de Persona
                            </h3>
                        </div>
                        
                       <!-- ... código anterior ... -->

                        <div class="form-row">
                            @if(isset($estados) && $estados->count() > 0)
                            <div class="form-group-elegant">
                                <label class="form-label-elegant required" for="estado_id">Estado</label>
                                <div class="custom-select-container">
                                    <select class="form-input-elegant @error('estado_id') error @enderror" 
                                            id="estado_id" 
                                            name="estado_id" 
                                            required>
                                        <option value="">Seleccionar estado...</option>
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado->id }}" {{ old('estado_id') == $estado->id ? 'selected' : '' }}>
                                                {{ $estado->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('estado_id')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
    
                            <!-- Campo Municipio -->
                            <div class="form-group-elegant" id="municipioContainer">
                                <label class="form-label-elegant required" for="municipio_id">Municipio</label>
                                <div class="custom-select-container">
                                    <select class="form-input-elegant @error('municipio_id') error @enderror" 
                                            id="municipio_id" 
                                            name="municipio_id" 
                                            required 
                                            disabled>
                                        <option value="">Seleccionar estado primero...</option>
                                    </select>
                                </div>
                                @error('municipio_id')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif
                        </div>

<!-- ... resto del código ... -->
                        
                        <div class="form-row">
                            <div class="form-group-elegant">
                                <label class="form-label-elegant" for="codigo_postal">Código Postal</label>
                                <div class="form-input-container">
                                    <input type="text" 
                                           class="form-input-elegant @error('codigo_postal') error @enderror" 
                                           id="codigo_postal" 
                                           name="codigo_postal" 
                                           value="{{ old('codigo_postal') }}" 
                                           placeholder="6201">
                                    <div class="input-icon">📮</div>
                                </div>
                                @error('codigo_postal')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group-elegant">
                                <label class="form-label-elegant required">Tipo de Persona</label>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" 
                                               class="radio-input" 
                                               name="tipo_persona" 
                                               value="natural" 
                                               {{ old('tipo_persona', 'natural') == 'natural' ? 'checked' : '' }} 
                                               required>
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Natural</span>
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" 
                                               class="radio-input" 
                                               name="tipo_persona" 
                                               value="juridica" 
                                               {{ old('tipo_persona') == 'juridica' ? 'checked' : '' }}>
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Jurídica</span>
                                    </label>
                                </div>
                                @error('tipo_persona')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Campo RIF (condicional) -->
                        <div class="form-group-elegant" id="rifContainer" style="display: none;">
                            <label class="form-label-elegant required" for="rif">RIF</label>
                            <div class="form-input-container">
                                <input type="text" 
                                       class="form-input-elegant @error('rif') error @enderror" 
                                       id="rif" 
                                       name="rif" 
                                       value="{{ old('rif') }}" 
                                       placeholder="J-12345678-9">
                                <div class="input-icon">🏢</div>
                            </div>
                            <small style="color: var(--silver); font-size: 0.85rem;">Solo para persona jurídica</small>
                            @error('rif')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Sección 3: Contraseña y Términos -->
                    <div class="form-section">
                        <div class="section-header">
                            <h3 class="section-title">
                                <span class="section-icon">🔒</span>
                                Seguridad y Términos
                            </h3>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group-elegant">
                                <label class="form-label-elegant required" for="password">Contraseña</label>
                                <div class="form-input-container">
                                    <input type="password" 
                                           class="form-input-elegant @error('password') error @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="••••••••"
                                           required>
                                    <div class="input-icon">🔐</div>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        👁️
                                    </button>
                                </div>
                                <div class="password-strength">
                                    <div class="strength-meter" id="passwordStrength"></div>
                                </div>
                                <small style="color: var(--silver); font-size: 0.85rem;">Mínimo 8 caracteres, con letras y números</small>
                                @error('password')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group-elegant">
                                <label class="form-label-elegant required" for="password_confirmation">Confirmar Contraseña</label>
                                <div class="form-input-container">
                                    <input type="password" 
                                           class="form-input-elegant" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="••••••••"
                                           required>
                                    <div class="input-icon">🔒</div>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                        👁️
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="checkbox-group">
                            <label class="checkbox-option">
                                <input type="checkbox" 
                                       class="checkbox-input @error('acepto_terminos') error @enderror" 
                                       id="acepto_terminos" 
                                       name="acepto_terminos" 
                                       {{ old('acepto_terminos') ? 'checked' : '' }} 
                                       required>
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-label">
                                    Acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#terminosModal">términos y condiciones</a> *
                                </span>
                            </label>
                            @error('acepto_terminos')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                            
                            <label class="checkbox-option">
                                <input type="checkbox" 
                                       class="checkbox-input" 
                                       id="recibir_ofertas" 
                                       name="recibir_ofertas" 
                                       {{ old('recibir_ofertas') ? 'checked' : '' }}>
                                <span class="checkbox-custom"></span>
                                <span class="checkbox-label">
                                    Deseo recibir ofertas y promociones exclusivas por email
                                </span>
                            </label>
                        </div>
                        
                        <!-- Botón de envío -->
                        <div class="form-actions">
                            <button type="submit" class="btn-elegant-submit">
                                <span>Crear Cuenta</span>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.5 5.83333L8.33333 15L2.5 9.16667" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                            
                            <a href="{{ route('login') }}" class="btn-elegant-login">
                                <span>¿Ya tienes una cuenta?</span>
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6 15L12 9L6 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de términos -->
<div class="modal fade" id="terminosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Términos y Condiciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Al registrarte, aceptas nuestros términos y condiciones...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    :root {
        --primary-gold: #D4AF37;
        --secondary-gold: #FFD700;
        --dark-charcoal: #121212;
        --light-charcoal: #1E1E1E;
        --crystal-white: #FFFFFF;
        --silver: #C0C0C0;
        --success-green: #28a745;
        --shadow-elegant: 0 20px 60px rgba(0, 0, 0, 0.3);
        --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .register-page {
        min-height: 100vh;
        background: linear-gradient(135deg, var(--dark-charcoal) 0%, #0A1931 100%);
        padding: 60px 20px;
        position: relative;
        overflow: hidden;
    }
    
    .register-container {
        max-width: 1000px;
        width: 100%;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }
    
    .register-header {
        text-align: center;
        margin-bottom: 60px;
    }
    
    .register-logo {
        font-family: 'Playfair Display', serif;
        font-size: 3.5rem;
        font-weight: 700;
        color: var(--crystal-white);
        margin-bottom: 15px;
    }
    
    .register-logo span {
        color: var(--primary-gold);
        font-style: italic;
    }
    
    .register-steps {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-bottom: 50px;
        position: relative;
    }
    
    .steps-connector {
        position: absolute;
        top: 25px;
        left: 75px;
        right: 75px;
        height: 2px;
        background: linear-gradient(90deg, 
            rgba(212, 175, 55, 0.3) 0%,
            rgba(212, 175, 55, 0.1) 100%);
        z-index: 0;
    }
    
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
    }
    
    .step-number {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(212, 175, 55, 0.3);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        color: var(--silver);
        font-size: 1.2rem;
        margin-bottom: 15px;
        transition: var(--transition-smooth);
    }
    
    .step.active .step-number {
        background: var(--primary-gold);
        border-color: var(--primary-gold);
        color: var(--dark-charcoal);
        transform: scale(1.1);
    }
    
    .register-form-container {
        background: rgba(30, 30, 30, 0.8);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 0;
        overflow: hidden;
        box-shadow: var(--shadow-elegant);
    }
    
    .form-header {
        background: linear-gradient(90deg, 
            rgba(212, 175, 55, 0.1) 0%,
            rgba(40, 167, 69, 0.05) 100%);
        padding: 30px 40px;
        border-bottom: 1px solid rgba(212, 175, 55, 0.1);
    }
    
    .form-title {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: var(--crystal-white);
        margin: 0;
    }
    
    .form-body {
        padding: 40px;
    }
    
    .form-section {
        margin-bottom: 50px;
    }
    
    .section-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(212, 175, 55, 0.1);
    }
    
    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.4rem;
        color: var(--crystal-white);
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-bottom: 25px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
    
    .form-group-elegant {
        position: relative;
    }
    
    .form-label-elegant {
        display: block;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        font-size: 0.9rem;
        color: var(--silver);
        margin-bottom: 10px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    
    .required::after {
        content: ' *';
        color: var(--primary-gold);
    }
    
    .form-input-container {
        position: relative;
    }
    
    .form-input-elegant {
        width: 100%;
        padding: 15px 20px 15px 50px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 0;
        color: var(--crystal-white);
        font-family: 'Montserrat', sans-serif;
        font-size: 1rem;
        transition: var(--transition-smooth);
    }
    
    .form-input-elegant:focus {
        outline: none;
        border-color: var(--primary-gold);
        background: rgba(212, 175, 55, 0.05);
        box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.1);
    }
    
    .input-icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary-gold);
        font-size: 1.2rem;
    }
    
    .custom-select-container {
        position: relative;
    }
    
    .custom-select-container::after {
        content: '';
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        width: 10px;
        height: 10px;
        border-right: 2px solid var(--primary-gold);
        border-bottom: 2px solid var(--primary-gold);
        transform: translateY(-50%) rotate(45deg);
        pointer-events: none;
    }
    
    .password-toggle {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--silver);
        cursor: pointer;
        padding: 5px;
    }
    
    .password-strength {
        margin-top: 10px;
        height: 4px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        overflow: hidden;
    }
    
    .strength-meter {
        height: 100%;
        width: 0;
        transition: width 0.3s ease;
    }
    
    .error-message {
        color: #ff6b8b;
        font-size: 0.85rem;
        margin-top: 8px;
        font-family: 'Montserrat', sans-serif;
    }
    
    .radio-group {
        display: flex;
        gap: 30px;
        margin-top: 10px;
    }
    
    .radio-option {
        display: flex;
        align-items: center;
        cursor: pointer;
    }
    
    .radio-input {
        display: none;
    }
    
    .radio-custom {
        width: 22px;
        height: 22px;
        border: 2px solid rgba(212, 175, 55, 0.4);
        border-radius: 50%;
        margin-right: 12px;
        position: relative;
        transition: var(--transition-smooth);
    }
    
    .radio-input:checked + .radio-custom {
        border-color: var(--primary-gold);
        background: var(--primary-gold);
    }
    
    .checkbox-option {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        cursor: pointer;
    }
    
    .checkbox-input {
        display: none;
    }
    
    .checkbox-custom {
        width: 20px;
        height: 20px;
        border: 1px solid rgba(212, 175, 55, 0.4);
        margin-right: 12px;
        position: relative;
        transition: var(--transition-smooth);
    }
    
    .checkbox-input:checked + .checkbox-custom {
        border-color: var(--primary-gold);
        background: var(--primary-gold);
    }
    
    .form-actions {
        margin-top: 50px;
        display: grid;
        gap: 20px;
    }
    
    .btn-elegant-submit {
        background: transparent;
        color: var(--success-green);
        border: 1px solid var(--success-green);
        padding: 18px 30px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: 2px;
        text-transform: uppercase;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }
    
    .btn-elegant-submit:hover {
        background: var(--success-green);
        color: var(--crystal-white);
    }
    
    .btn-elegant-login {
        background: transparent;
        color: var(--primary-gold);
        border: 1px solid rgba(212, 175, 55, 0.4);
        padding: 15px 30px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        font-size: 0.95rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
    }
    
    .btn-elegant-login:hover {
        border-color: var(--primary-gold);
        background: rgba(212, 175, 55, 0.1);
    }
    
    option {
        color: black;
    }
    /* Agrega esto al final del CSS */
#municipioContainer {
    transition: var(--transition-smooth);
}

.form-input-elegant:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
@endpush

@push('scripts')
<script>
// 1. Mostrar/ocultar RIF según tipo de persona
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
            rifInput.value = '';
        }
    });
});

// 2. Agregar campo municipio automáticamente
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar RIF si ya está seleccionado
    const tipoJuridica = document.querySelector('input[name="tipo_persona"][value="juridica"]');
    if (tipoJuridica && tipoJuridica.checked) {
        document.getElementById('rifContainer').style.display = 'block';
        document.getElementById('rif').required = true;
    }
    
    // Crear campo municipio si no existe
    if (!document.getElementById('municipio_id')) {
        const estadoGroup = document.querySelector('.form-group-elegant:has(#estado_id)');
        if (estadoGroup) {
            const municipioContainer = document.createElement('div');
            municipioContainer.className = 'form-group-elegant';
            municipioContainer.innerHTML = `
                <label class="form-label-elegant required" for="municipio_id">Municipio</label>
                <div class="custom-select-container">
                    <select class="form-input-elegant" id="municipio_id" name="municipio_id" required>
                        <option value="">Seleccionar estado primero...</option>
                    </select>
                </div>
                <div id="municipio_error" class="error-message" style="display: none;"></div>
            `;
            
            // Insertar después del campo estado
            estadoGroup.parentNode.insertBefore(municipioContainer, estadoGroup.nextElementSibling);
        }
    }
});

// 3. Cargar municipios por estado
const estadoSelect = document.getElementById('estado_id');
const municipioSelect = document.getElementById('municipio_id');

if (estadoSelect && municipioSelect) {
    estadoSelect.addEventListener('change', async function() {
        const estadoId = this.value;
        
        // Limpiar select de municipio
        municipioSelect.innerHTML = '<option value="">Cargando municipios...</option>';
        municipioSelect.disabled = true;
        
        if (estadoId) {
            try {
                // Cargar municipios
                const response = await fetch(`/municipios/por-estado/${estadoId}`);
                if (!response.ok) throw new Error('Error en la respuesta');
                
                const municipios = await response.json();
                
                municipioSelect.innerHTML = '<option value="">Seleccionar municipio...</option>';
                municipios.forEach(municipio => {
                    const option = document.createElement('option');
                    option.value = municipio.id;
                    option.textContent = municipio.nombre;
                    municipioSelect.appendChild(option);
                });
                municipioSelect.disabled = false;
                
                // Restaurar valor antiguo
                const oldMunicipioId = '{{ old("municipio_id") }}';
                if (oldMunicipioId) {
                    municipioSelect.value = oldMunicipioId;
                }
            } catch (error) {
                console.error('Error:', error);
                municipioSelect.innerHTML = '<option value="">Error cargando</option>';
            }
        } else {
            municipioSelect.innerHTML = '<option value="">Seleccionar estado primero...</option>';
            municipioSelect.disabled = true;
        }
    });
    
    // 5. Inicializar con valores antiguos
    const oldEstadoId = '{{ old("estado_id") }}';
    if (oldEstadoId) {
        estadoSelect.value = oldEstadoId;
        estadoSelect.dispatchEvent(new Event('change'));
        
        // También inicializar municipio si hay valor antiguo
        const oldMunicipioId = '{{ old("municipio_id") }}';
        if (oldMunicipioId && municipioSelect) {
            // Esperar un momento para que se carguen los municipios
            setTimeout(() => {
                municipioSelect.value = oldMunicipioId;
            }, 500);
        }
    }
}

// 8. Validación del formulario (actualizada)
document.getElementById('registerForm').addEventListener('submit', function(e) {
    let isValid = true;
    
    // Validar contraseñas
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirmation').value;
    
    if (password !== confirmPassword) {
        alert('Las contraseñas no coinciden');
        isValid = false;
    }
    
    // Validar cédula
    const cedula = document.getElementById('cedula').value;
    if (cedula && !/^[VEJvej]-?\d{5,9}$/.test(cedula)) {
        alert('Formato de cédula inválido');
        isValid = false;
    }
    
    // Validar teléfono
    const telefono = document.getElementById('telefono').value;
    if (telefono && !/^[0-9+()\s-]{10,20}$/.test(telefono)) {
        alert('Formato de teléfono inválido');
        isValid = false;
    }
    
    // Validar RIF
    const tipoJuridica = document.querySelector('input[name="tipo_persona"][value="juridica"]');
    if (tipoJuridica && tipoJuridica.checked) {
        const rif = document.getElementById('rif').value;
        if (!rif || !/^[Jj]-?\d{7,9}-?\d$/.test(rif)) {
            alert('Formato de RIF inválido');
            isValid = false;
        }
    }
    
    // Validar municipio (ahora es obligatorio)
    if (municipioSelect && !municipioSelect.value) {
        alert('Por favor seleccione un municipio');
        isValid = false;
    }
    
    // Validar que se haya seleccionado un estado
    if (estadoSelect && !estadoSelect.value) {
        alert('Por favor seleccione un estado');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
    }
});
</script>
@endpush