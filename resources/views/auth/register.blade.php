@extends('layouts.app')

@section('title', 'Unirse a la Comunidad Exclusiva')
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
    
    /* Patrón decorativo */
    .register-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            radial-gradient(circle at 10% 20%, rgba(212, 175, 55, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 90% 80%, rgba(40, 167, 69, 0.05) 0%, transparent 50%);
        z-index: 0;
    }
    
    /* Contenedor principal */
    .register-container {
        max-width: 1000px;
        width: 100%;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }
    
    /* Header elegante */
    .register-header {
        text-align: center;
        margin-bottom: 60px;
        position: relative;
    }
    
    .register-logo {
        font-family: 'Playfair Display', serif;
        font-size: 3.5rem;
        font-weight: 700;
        color: var(--crystal-white);
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
    }
    
    .register-logo::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--primary-gold), transparent);
    }
    
    .register-logo span {
        color: var(--primary-gold);
        font-style: italic;
        font-weight: 400;
    }
    
    .register-subtitle {
        font-family: 'Montserrat', sans-serif;
        font-size: 1.2rem;
        color: var(--silver);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-top: 25px;
    }
    
    /* Pasos del registro */
    .register-steps {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-bottom: 50px;
        position: relative;
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
    
    .step-label {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.9rem;
        color: var(--silver);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .step.active .step-label {
        color: var(--crystal-white);
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
    
    /* Contenedor del formulario */
    .register-form-container {
        background: rgba(30, 30, 30, 0.8);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 0;
        overflow: hidden;
        box-shadow: var(--shadow-elegant);
    }
    
    /* Header del formulario */
    .form-header {
        background: linear-gradient(90deg, 
            rgba(212, 175, 55, 0.1) 0%,
            rgba(40, 167, 69, 0.05) 100%);
        padding: 30px 40px;
        border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        position: relative;
    }
    
    .form-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--primary-gold), transparent);
    }
    
    .form-title {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: var(--crystal-white);
        margin: 0;
        position: relative;
    }
    
    .form-icon {
        position: absolute;
        right: 40px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2.5rem;
        color: rgba(212, 175, 55, 0.2);
    }
    
    /* Cuerpo del formulario */
    .form-body {
        padding: 40px;
    }
    
    /* Secciones del formulario */
    .form-section {
        margin-bottom: 50px;
        position: relative;
    }
    
    .section-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        position: relative;
    }
    
    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.4rem;
        color: var(--crystal-white);
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .section-icon {
        color: var(--primary-gold);
        font-size: 1.2rem;
    }
    
    /* Grupos de formulario */
    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-bottom: 25px;
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
            gap: 20px;
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
        transition: color 0.3s ease;
    }
    
    .form-input-elegant:focus + .input-icon {
        color: var(--secondary-gold);
    }
    
    /* Select personalizado */
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
    
    /* Textarea */
    .form-textarea-elegant {
        width: 100%;
        min-height: 100px;
        padding: 15px 20px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 0;
        color: var(--crystal-white);
        font-family: 'Montserrat', sans-serif;
        font-size: 1rem;
        resize: vertical;
        transition: var(--transition-smooth);
    }
    
    .form-textarea-elegant:focus {
        outline: none;
        border-color: var(--primary-gold);
        background: rgba(212, 175, 55, 0.05);
    }
    
    /* Radio buttons elegantes */
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
    
    .radio-custom::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 10px;
        height: 10px;
        background: var(--primary-gold);
        border-radius: 50%;
        opacity: 0;
        transition: var(--transition-smooth);
    }
    
    .radio-input:checked + .radio-custom {
        border-color: var(--primary-gold);
    }
    
    .radio-input:checked + .radio-custom::after {
        opacity: 1;
    }
    
    .radio-label {
        font-family: 'Montserrat', sans-serif;
        color: var(--silver);
        font-size: 0.95rem;
    }
    
    /* Contraseña con toggle */
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
        transition: color 0.3s ease;
    }
    
    .password-toggle:hover {
        color: var(--primary-gold);
    }
    
    /* Checkboxes elegantes */
    .checkbox-group {
        margin: 30px 0;
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
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .checkbox-custom::after {
        content: '✓';
        color: var(--primary-gold);
        font-size: 0.9rem;
        opacity: 0;
        transform: scale(0);
        transition: var(--transition-smooth);
    }
    
    .checkbox-input:checked + .checkbox-custom {
        border-color: var(--primary-gold);
    }
    
    .checkbox-input:checked + .checkbox-custom::after {
        opacity: 1;
        transform: scale(1);
    }
    
    .checkbox-label {
        font-family: 'Montserrat', sans-serif;
        color: var(--silver);
        font-size: 0.95rem;
        line-height: 1.5;
    }
    
    .checkbox-label a {
        color: var(--primary-gold);
        text-decoration: none;
        border-bottom: 1px solid transparent;
        transition: border-color 0.3s ease;
    }
    
    .checkbox-label a:hover {
        border-bottom-color: var(--primary-gold);
    }
    
    /* Botones */
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
        position: relative;
        overflow: hidden;
    }
    
    .btn-elegant-submit::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(40, 167, 69, 0.2), transparent);
        transition: left 0.6s ease;
    }
    
    .btn-elegant-submit:hover::before {
        left: 100%;
    }
    
    .btn-elegant-submit:hover {
        background: var(--success-green);
        color: var(--crystal-white);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(40, 167, 69, 0.3);
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
    }
    
    .btn-elegant-login:hover {
        border-color: var(--primary-gold);
        background: rgba(212, 175, 55, 0.1);
        transform: translateY(-2px);
    }
    
    /* Validación */
    .error-message {
        color: #ff6b8b;
        font-size: 0.85rem;
        margin-top: 8px;
        font-family: 'Montserrat', sans-serif;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .error-message::before {
        content: '⚠';
        font-size: 0.9rem;
    }
    
    .form-input-elegant.error,
    .form-textarea-elegant.error {
        border-color: #ff6b8b;
    }
    
    /* Fortaleza de contraseña */
    .password-strength {
        margin-top: 10px;
        height: 4px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 2px;
        overflow: hidden;
        position: relative;
    }
    
    .strength-meter {
        height: 100%;
        width: 0;
        transition: width 0.3s ease, background 0.3s ease;
    }
    
    .strength-weak { background: #ff6b6b; }
    .strength-medium { background: #ffd93d; }
    .strength-strong { background: #6bcf7f; }
    
    /* Responsive */
    @media (max-width: 768px) {
        .register-logo {
            font-size: 2.5rem;
        }
        
        .register-subtitle {
            font-size: 1rem;
        }
        
        .register-steps {
            gap: 15px;
        }
        
        .step-label {
            font-size: 0.8rem;
        }
        
        .form-body {
            padding: 30px 20px;
        }
        
        .form-header {
            padding: 25px 20px;
        }
        
        .form-title {
            font-size: 1.6rem;
        }
    }
</style>
@endpush

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
                                <span class="section-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="currentColor" stroke-width="2"/>
                                        <path d="M20 22C20 17.5817 16.4183 14 12 14C7.58172 14 4 17.5817 4 22" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                </span>
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
                                    <div class="input-icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15 6.66667C15 8.50762 13.5076 10 11.6667 10C9.82573 10 8.33334 8.50762 8.33334 6.66667C8.33334 4.82573 9.82573 3.33333 11.6667 3.33333C13.5076 3.33333 15 4.82573 15 6.66667Z" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M5 16.6667C5 13.9848 7.23858 11.6667 10 11.6667C12.7614 11.6667 15 13.9848 15 16.6667" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                    </div>
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
                                    <div class="input-icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2.5 5.83333L8.4705 9.8485C9.3153 10.4424 10.6847 10.4424 11.5295 9.8485L17.5 5.83333M4.16667 15.8333H15.8333C17.2141 15.8333 18.3333 14.714 18.3333 13.3333V6.66667C18.3333 5.28595 17.2141 4.16667 15.8333 4.16667H4.16667C2.78595 4.16667 1.66667 5.28595 1.66667 6.66667V13.3333C1.66667 14.714 2.78595 15.8333 4.16667 15.8333Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
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
                                    <div class="input-icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect x="3" y="4" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M7 8H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M7 11H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </div>
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
                                    <div class="input-icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M4.16667 4.16667H6.11111L7.5 8.33333L5.97222 9.58333C6.94444 11.6667 8.33333 13.0556 10.4167 14.0278L11.6667 12.5L15.8333 13.8889V15.8333C15.8333 16.75 15.0833 17.5 14.1667 17.5C9.86111 17.5 5.97222 15.8333 3.05556 12.9444C0.138889 10.0278 -0.694444 6.13889 2.5 2.5C2.5 1.58333 3.25 0.833333 4.16667 0.833333H4.16667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
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
                                    <div class="input-icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect x="2.5" y="4.16667" width="15" height="13.3333" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M2.5 8.33333H17.5" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M6.66667 2.5V4.16667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M13.3333 2.5V4.16667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </div>
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
                                <span class="section-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 21C16.1421 21 19.5 17.6421 19.5 13.5C19.5 9.35786 16.1421 6 12 6C7.85786 6 4.5 9.35786 4.5 13.5C4.5 17.6421 7.85786 21 12 21Z" stroke="currentColor" stroke-width="2"/>
                                        <path d="M12 13.5L14.25 15.75L17.25 11.25" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                Ubicación y Tipo de Persona
                            </h3>
                        </div>
                        
                        <div class="form-group-elegant">
                            <label class="form-label-elegant" for="direccion">Dirección Completa</label>
                            <textarea class="form-textarea-elegant @error('direccion') error @enderror" 
                                      id="direccion" 
                                      name="direccion" 
                                      rows="3">{{ old('direccion') }}</textarea>
                            @error('direccion')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        
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
                            
                            <div class="form-group-elegant">
                                <label class="form-label-elegant required" for="ciudad_id">Ciudad</label>
                                <div class="custom-select-container">
                                    <select class="form-input-elegant @error('ciudad_id') error @enderror" 
                                            id="ciudad_id" 
                                            name="ciudad_id" 
                                            required>
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
                                </div>
                                @error('ciudad_id')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </div>
                            @endif
                        </div>
                        
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
                                    <div class="input-icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect x="3" y="3" width="14" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M8 7H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M8 10H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M8 13H10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </div>
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
                                <div class="input-icon">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 15C12.7614 15 15 12.7614 15 10C15 7.23858 12.7614 5 10 5C7.23858 5 5 7.23858 5 10C5 12.7614 7.23858 15 10 15Z" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M10 7.5V10L11.5 11.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
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
                                <span class="section-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 15V17M6 21H18C19.1046 21 20 20.1046 20 19V13C20 11.8954 19.1046 11 18 11H6C4.89543 11 4 11.8954 4 13V19C4 20.1046 4.89543 21 6 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M16 11V7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </span>
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
                                    <div class="input-icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.8333 9.16667H4.16667C2.78595 9.16667 1.66667 10.286 1.66667 11.6667V16.6667C1.66667 18.0474 2.78595 19.1667 4.16667 19.1667H15.8333C17.2141 19.1667 18.3333 18.0474 18.3333 16.6667V11.6667C18.3333 10.286 17.2141 9.16667 15.8333 9.16667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M5.83333 9.16667V5.83333C5.83333 4.45262 6.95262 3.33333 8.33333 3.33333H11.6667C13.0474 3.33333 14.1667 4.45262 14.1667 5.83333V9.16667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 9C1 9 3.90909 3 9 3C14.0909 3 17 9 17 9C17 9 14.0909 15 9 15C3.90909 15 1 9 1 9Z" stroke="currentColor" stroke-width="1.5"/>
                                            <circle cx="9" cy="9" r="3" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
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
                                    <div class="input-icon">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.8333 9.16667H4.16667C2.78595 9.16667 1.66667 10.286 1.66667 11.6667V16.6667C1.66667 18.0474 2.78595 19.1667 4.16667 19.1667H15.8333C17.2141 19.1667 18.3333 18.0474 18.3333 16.6667V11.6667C18.3333 10.286 17.2141 9.16667 15.8333 9.16667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M5.83333 9.16667V5.83333C5.83333 4.45262 6.95262 3.33333 8.33333 3.33333H11.6667C13.0474 3.33333 14.1667 4.45262 14.1667 5.83333V9.16667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M10 13.3333V13.3417" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 9C1 9 3.90909 3 9 3C14.0909 3 17 9 17 9C17 9 14.0909 15 9 15C3.90909 15 1 9 1 9Z" stroke="currentColor" stroke-width="1.5"/>
                                            <circle cx="9" cy="9" r="3" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
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
                    </div>
                    
                    <!-- Acciones del formulario -->
                    <div class="form-actions">
                        <button type="submit" class="btn-elegant-submit">
                            <span>Crear Cuenta Premium</span>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.16667 10H15.8333M15.8333 10L10.8333 5M15.8333 10L10.8333 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <a href="{{ route('login') }}" class="btn-elegant-login">
                            <span>¿Ya tienes una cuenta?</span>
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 15L12 9L6 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de términos (se mantiene igual) -->
<div class="modal fade" id="terminosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Términos y Condiciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Aceptación de términos</h6>
                <p>Al registrarte en MONAGAS.TECH, aceptas cumplir con estos términos y condiciones...</p>
                
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
            rifInput.value = '';
        }
    });
});

// Inicializar visibilidad del campo RIF
document.addEventListener('DOMContentLoaded', function() {
    const tipoJuridica = document.querySelector('input[name="tipo_persona"][value="juridica"]');
    if (tipoJuridica && tipoJuridica.checked) {
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
        
        // Ocultar todas las ciudades excepto la opción vacía
        ciudades.forEach(option => {
            if (option.value !== '') {
                option.style.display = 'none';
                option.disabled = true;
            }
        });
        
        // Mostrar solo ciudades del estado seleccionado
        if (estadoId) {
            ciudades.forEach(option => {
                if (option.dataset.estado === estadoId) {
                    option.style.display = 'block';
                    option.disabled = false;
                }
            });
            ciudadSelect.value = '';
        }
    });
    
    // Inicializar filtro si hay estado seleccionado
    if (estadoSelect.value) {
        estadoSelect.dispatchEvent(new Event('change'));
    }
}

// Función para mostrar/ocultar contraseña
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.password-toggle');
    const icon = button.querySelector('svg');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `
            <path d="M1 9C1 9 3.90909 3 9 3C14.0909 3 17 9 17 9C17 9 14.0909 15 9 15C3.90909 15 1 9 1 9Z" stroke="currentColor" stroke-width="1.5"/>
            <path d="M9 11C10.1046 11 11 10.1046 11 9C11 7.89543 10.1046 7 9 7C7.89543 7 7 7.89543 7 9C7 10.1046 7.89543 11 9 11Z" stroke="currentColor" stroke-width="1.5"/>
            <path d="M3 3L15 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        `;
    } else {
        input.type = 'password';
        icon.innerHTML = `
            <path d="M1 9C1 9 3.90909 3 9 3C14.0909 3 17 9 17 9C17 9 14.0909 15 9 15C3.90909 15 1 9 1 9Z" stroke="currentColor" stroke-width="1.5"/>
            <circle cx="9" cy="9" r="3" stroke="currentColor" stroke-width="1.5"/>
        `;
    }
}

// Medidor de fortaleza de contraseña
const passwordInput = document.getElementById('password');
const strengthMeter = document.getElementById('passwordStrength');

if (passwordInput && strengthMeter) {
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        // Longitud mínima
        if (password.length >= 8) strength++;
        
        // Contiene números
        if (/\d/.test(password)) strength++;
        
        // Contiene letras minúsculas y mayúsculas
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        
        // Contiene caracteres especiales
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        // Actualizar medidor
        const width = (strength / 4) * 100;
        strengthMeter.style.width = width + '%';
        
        // Actualizar clase de color
        strengthMeter.className = 'strength-meter';
        if (strength <= 1) {
            strengthMeter.classList.add('strength-weak');
        } else if (strength <= 2) {
            strengthMeter.classList.add('strength-medium');
        } else {
            strengthMeter.classList.add('strength-strong');
        }
    });
}

// Validación del formulario
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
    
    // Validar RIF si es persona jurídica
    const tipoJuridica = document.querySelector('input[name="tipo_persona"][value="juridica"]');
    if (tipoJuridica && tipoJuridica.checked) {
        const rif = document.getElementById('rif').value;
        if (!rif || !/^[Jj]-?\d{7,9}-?\d$/.test(rif)) {
            e.preventDefault();
            alert('Formato de RIF inválido. Use J seguido de números (ej: J-12345678-9)');
            return false;
        }
    }
});
</script>
@endpush
@endsection