@extends('layouts.app')

@section('title', 'Acceso Exclusivo')
@push('styles')
<style>
    :root {
        --primary-gold: #D4AF37;
        --secondary-gold: #FFD700;
        --dark-charcoal: #121212;
        --light-charcoal: #1E1E1E;
        --crystal-white: #FFFFFF;
        --silver: #C0C0C0;
        --glass-bg: rgba(255, 255, 255, 0.05);
        --shadow-elegant: 0 20px 60px rgba(0, 0, 0, 0.3);
        --transition-smooth: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .login-page {
        min-height: 100vh;
        background: linear-gradient(135deg, var(--dark-charcoal) 0%, #0A1931 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        position: relative;
        overflow: hidden;
    }
    
    /* Patrón decorativo de fondo */
    .login-page::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            radial-gradient(circle at 20% 80%, rgba(212, 175, 55, 0.03) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.03) 0%, transparent 50%),
            radial-gradient(circle at 40% 40%, rgba(255, 215, 0, 0.02) 0%, transparent 50%);
        z-index: 0;
    }
    
    /* Líneas decorativas */
    .decor-line {
        position: absolute;
        background: rgba(212, 175, 55, 0.1);
        z-index: 0;
    }
    
    .line-1 {
        top: 20%;
        left: 10%;
        width: 2px;
        height: 100px;
    }
    
    .line-2 {
        bottom: 30%;
        right: 15%;
        width: 100px;
        height: 2px;
    }
    
    /* Contenedor principal */
    .login-container {
        max-width: 480px;
        width: 100%;
        position: relative;
        z-index: 1;
    }
    
    /* Header elegante */
    .login-header {
        text-align: center;
        margin-bottom: 50px;
        position: relative;
    }
    
    .login-logo {
        font-family: 'Playfair Display', serif;
        font-size: 3.5rem;
        font-weight: 700;
        color: var(--crystal-white);
        margin-bottom: 15px;
        position: relative;
        display: inline-block;
    }
    
    .login-logo::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--primary-gold), transparent);
    }
    
    .login-logo span {
        color: var(--primary-gold);
        font-style: italic;
        font-weight: 400;
    }
    
    .login-subtitle {
        font-family: 'Montserrat', sans-serif;
        font-size: 1.1rem;
        color: var(--silver);
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-top: 25px;
    }
    
    /* Tarjeta de login */
    .login-card {
        background: rgba(30, 30, 30, 0.8);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(212, 175, 55, 0.2);
        border-radius: 0;
        overflow: hidden;
        box-shadow: var(--shadow-elegant);
        transition: var(--transition-smooth);
    }
    
    .login-card:hover {
        border-color: rgba(212, 175, 55, 0.4);
        transform: translateY(-5px);
    }
    
    /* Encabezado de la tarjeta */
    .login-card-header {
        background: linear-gradient(90deg, 
            rgba(212, 175, 55, 0.1) 0%,
            rgba(255, 215, 0, 0.05) 100%);
        padding: 30px;
        border-bottom: 1px solid rgba(212, 175, 55, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .login-card-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--primary-gold), transparent);
    }
    
    .login-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        color: var(--crystal-white);
        margin: 0;
        position: relative;
        z-index: 1;
    }
    
    .login-icon {
        position: absolute;
        right: 30px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2.5rem;
        color: rgba(212, 175, 55, 0.2);
    }
    
    /* Cuerpo de la tarjeta */
    .login-card-body {
        padding: 40px;
    }
    
    /* Grupos de formulario */
    .form-group-elegant {
        margin-bottom: 30px;
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
        transition: color 0.3s ease;
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
    
    .form-input-elegant::placeholder {
        color: rgba(255, 255, 255, 0.3);
    }
    
    /* Íconos de los inputs */
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
    
    /* Checkbox elegante */
    .form-check-elegant {
        margin: 30px 0;
        position: relative;
    }
    
    .form-check-input-elegant {
        display: none;
    }
    
    .form-check-label-elegant {
        display: flex;
        align-items: center;
        cursor: pointer;
        font-family: 'Montserrat', sans-serif;
        color: var(--silver);
        font-size: 0.9rem;
        user-select: none;
    }
    
    .checkmark {
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
    
    .checkmark::after {
        content: '';
        position: absolute;
        width: 10px;
        height: 10px;
        background: var(--primary-gold);
        opacity: 0;
        transform: scale(0);
        transition: var(--transition-smooth);
    }
    
    .form-check-input-elegant:checked + .form-check-label-elegant .checkmark {
        border-color: var(--primary-gold);
    }
    
    .form-check-input-elegant:checked + .form-check-label-elegant .checkmark::after {
        opacity: 1;
        transform: scale(1);
    }
    
    /* Botón de submit */
    .btn-elegant-submit {
        width: 100%;
        background: transparent;
        color: var(--primary-gold);
        border: 1px solid var(--primary-gold);
        padding: 16px 30px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        font-size: 0.95rem;
        letter-spacing: 2px;
        text-transform: uppercase;
        cursor: pointer;
        transition: var(--transition-smooth);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .btn-elegant-submit::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(212, 175, 55, 0.2), transparent);
        transition: left 0.6s ease;
    }
    
    .btn-elegant-submit:hover::before {
        left: 100%;
    }
    
    .btn-elegant-submit:hover {
        background: var(--primary-gold);
        color: var(--dark-charcoal);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);
    }
    
    /* Enlaces y separador */
    .login-links {
        margin-top: 30px;
        text-align: center;
    }
    
    .login-separator {
        display: flex;
        align-items: center;
        margin: 30px 0;
        color: var(--silver);
        font-size: 0.9rem;
    }
    
    .login-separator::before,
    .login-separator::after {
        content: '';
        flex: 1;
        height: 1px;
        background: rgba(212, 175, 55, 0.2);
    }
    
    .login-separator span {
        padding: 0 15px;
        font-family: 'Montserrat', sans-serif;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    /* Enlace de registro */
    .register-link {
        text-align: center;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid rgba(212, 175, 55, 0.1);
    }
    
    .register-link p {
        color: var(--silver);
        font-family: 'Montserrat', sans-serif;
        font-size: 0.95rem;
        margin-bottom: 15px;
    }
    
    .btn-elegant-register {
        background: transparent;
        color: var(--crystal-white);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 12px 30px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 500;
        font-size: 0.9rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        cursor: pointer;
        transition: var(--transition-smooth);
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .btn-elegant-register:hover {
        border-color: var(--primary-gold);
        color: var(--primary-gold);
        transform: translateY(-2px);
    }
    
    /* Error messages */
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
    
    .form-input-elegant.error {
        border-color: #ff6b8b;
    }
    
    /* Efecto de carga */
    .loading-spinner {
        display: none;
        width: 20px;
        height: 20px;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .login-logo {
            font-size: 2.5rem;
        }
        
        .login-card-body {
            padding: 30px 20px;
        }
        
        .login-card-header {
            padding: 25px 20px;
        }
        
        .login-title {
            font-size: 1.5rem;
        }
    }
    
    @media (max-width: 480px) {
        .login-logo {
            font-size: 2rem;
        }
        
        .login-subtitle {
            font-size: 0.9rem;
            letter-spacing: 1px;
        }
        
        .btn-elegant-submit {
            padding: 14px 20px;
            font-size: 0.9rem;
        }
    }
</style>
@endpush

@section('content')
<div class="login-page">
    <!-- Líneas decorativas -->
    <div class="decor-line line-1"></div>
    <div class="decor-line line-2"></div>
    
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <h1 class="login-logo">
                MONAGAS<span>.TECH</span>
            </h1>
            <div class="login-subtitle">
                Acceso Exclusivo
            </div>
        </div>
        
        <!-- Tarjeta de login -->
        <div class="login-card">
            <div class="login-card-header">
                <h2 class="login-title">Ingresar a mi cuenta</h2>
                <div class="login-icon">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 25C25.5228 25 30 20.5228 30 15C30 9.47715 25.5228 5 20 5C14.4772 5 10 9.47715 10 15C10 20.5228 14.4772 25 20 25Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M5 35C5 28.9249 9.92487 24 16 24H24C30.0751 24 35 28.9249 35 35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
            
            <div class="login-card-body">
                <form method="POST" action="{{ route('auth.login') }}" id="loginForm">
                    @csrf
                    
                    <!-- Email -->
                    <div class="form-group-elegant">
                        <label class="form-label-elegant" for="email">
                            Correo Electrónico
                        </label>
                        <div class="form-input-container">
                            <input type="email" 
                                   class="form-input-elegant @error('email') error @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   placeholder="tu@email.com"
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
                    
                    <!-- Password -->
                    <div class="form-group-elegant">
                        <label class="form-label-elegant" for="password">
                            Contraseña
                        </label>
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
                                    <circle cx="10" cy="14.1667" r="1.66667" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </div>
                        </div>
                        @error('password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Remember me -->
                    <div class="form-check-elegant">
                        <input type="checkbox" 
                               class="form-check-input-elegant" 
                               id="remember" 
                               name="remember" 
                               value="1"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label-elegant" for="remember">
                            <span class="checkmark"></span>
                            Recordar mis datos
                        </label>
                    </div>
                    
                    <!-- Submit button -->
                    <button type="submit" class="btn-elegant-submit" id="submitBtn">
                        <span class="btn-text">Acceder</span>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4.16667 10H15.8333M15.8333 10L10.8333 5M15.8333 10L10.8333 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="loading-spinner"></div>
                    </button>
                </form>
                
                <!-- Separador -->
                <div class="login-separator">
                    <span>¿Nuevo aquí?</span>
                </div>
                
                <!-- Enlace de registro -->
                <div class="register-link">
                    <p>Únete a nuestra comunidad exclusiva</p>
                    <a href="{{ route('register') }}" class="btn-elegant-register">
                        Crear una cuenta
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 1V17M17 9H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const loadingSpinner = submitBtn.querySelector('.loading-spinner');
        
        // Efecto de focus en inputs
        const inputs = document.querySelectorAll('.form-input-elegant');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.input-icon').style.color = 'var(--secondary-gold)';
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.querySelector('.input-icon').style.color = 'var(--primary-gold)';
                }
            });
        });
        
        // Efecto de submit
        loginForm.addEventListener('submit', function(e) {
            // Solo mostrar loading si es válido
            if (this.checkValidity()) {
                btnText.style.opacity = '0';
                loadingSpinner.style.display = 'block';
                submitBtn.disabled = true;
                
                // Simular delay para demostración
                setTimeout(() => {
                    loadingSpinner.style.display = 'none';
                    btnText.style.opacity = '1';
                    submitBtn.disabled = false;
                }, 2000);
            }
        });
        
        // Validación en tiempo real
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.classList.contains('error')) {
                    this.classList.remove('error');
                }
            });
        });
        
        // Efecto de toggle para password
        const passwordInput = document.getElementById('password');
        const passwordIcon = passwordInput.parentElement.querySelector('.input-icon');
        
        // Podrías agregar un botón de "mostrar contraseña" aquí
        // passwordIcon.addEventListener('click', function() {
        //     const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        //     passwordInput.setAttribute('type', type);
        // });
    });
</script>
@endpush
@endsection