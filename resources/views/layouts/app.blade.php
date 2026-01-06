<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MONAGAS.TECH - Comunidad Tecnológica Exclusiva')</title>
    <meta name="description" content="@yield('description', 'Descubre tecnología de vanguardia en la comunidad más exclusiva de Venezuela. Productos premium, servicios personalizados y experiencia...')">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        /* Variables CSS para diseño elegante */
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
        
        /* Reset y estilos base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--dark-charcoal) 0%, #0A1931 100%);
            color: var(--crystal-white);
            line-height: 1.6;
            overflow-x: hidden;
            min-height: 100vh;
            position: relative;
        }
        
        /* Contenedor de imagen de fondo */
        .fullscreen-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            overflow: hidden;
        }
        
        .background-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            filter: brightness(0.4);
            transition: transform 30s ease-in-out;
            animation: slowZoom 30s infinite alternate;
        }
        
        @keyframes slowZoom {
            0% {
                transform: scale(1);
            }
            100% {
                transform: scale(1.05);
            }
        }
        
        /* Overlay elegante sobre la imagen */
        .background-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg,
                rgba(18, 18, 18, 0.85) 0%,
                rgba(10, 25, 49, 0.8) 50%,
                rgba(18, 18, 18, 0.85) 100%
            );
            z-index: -1;
        }
        
        /* Patrón decorativo */
        .background-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(212, 175, 55, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(212, 175, 55, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(255, 215, 0, 0.02) 0%, transparent 50%);
            z-index: -1;
        }
        
        /* Grid decorativo */
        .grid-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(rgba(255,255,255,0.01) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.01) 1px, transparent 1px);
            background-size: 80px 80px;
            pointer-events: none;
            z-index: -1;
        }
        
        /* Contenido principal */
        main {
            position: relative;
            z-index: 1;
            min-height: calc(100vh - 120px); /* Ajustar según navbar y footer */
            display: flex;
            flex-direction: column;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
            position: relative;
            flex: 1;
        }
        
        /* Efectos de scroll suave */
        html {
            scroll-behavior: smooth;
        }
        
        /* Estilos para scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(30, 30, 30, 0.5);
            backdrop-filter: blur(10px);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-gold);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-gold);
        }
        
        /* Estilos para selección de texto */
        ::selection {
            background-color: var(--primary-gold);
            color: var(--dark-charcoal);
        }
        
        /* Animaciones de entrada */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        /* Clases utilitarias */
        .glass-effect {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .text-gold {
            color: var(--primary-gold);
        }
        
        .text-gradient {
            background: linear-gradient(45deg, var(--primary-gold), var(--secondary-gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }
            
            .grid-overlay {
                background-size: 40px 40px;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 15px 10px;
            }
        }
        
        /* Efecto de partículas (opcional) */
        .particles-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: var(--primary-gold);
            border-radius: 50%;
            animation: float 8s infinite ease-in-out;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            10%, 90% {
                opacity: 0.5;
            }
            50% {
                transform: translateY(-100px) translateX(50px);
                opacity: 1;
            }
        }
        
        /* Preloader elegante (opcional) */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--dark-charcoal);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }
        
        .preloader-content {
            text-align: center;
        }
        
        .preloader-logo {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--crystal-white);
            margin-bottom: 20px;
        }
        
        .preloader-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid transparent;
            border-top-color: var(--primary-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Preloader (opcional) -->
    <div id="preloader">
        <div class="preloader-content">
            <div class="preloader-logo">
                MONAGAS<span class="text-gold">.TECH</span>
            </div>
            <div class="preloader-spinner"></div>
        </div>
    </div>
    
    <!-- Grid decorativo -->
    <div class="grid-overlay"></div>
    
    <!-- Efecto de partículas (opcional) -->
    <div class="particles-container" id="particlesContainer"></div>
    
    <!-- Header/Navbar -->
    @include('partials.header')
    
    <!-- Contenido principal -->
    <main>
        <div class="container fade-in-up">
            <!-- Mensajes flash -->
            @include('partials.flash-messages')
            
            <!-- Contenido específico de cada página -->
            @yield('content')
        </div>
    </main>
    
    <!-- Footer -->
    @include('partials.footer')
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Preloader
        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('preloader');
            
            // Ocultar preloader después de 1.5 segundos
            setTimeout(() => {
                preloader.style.opacity = '0';
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 500);
            }, 1500);
            
            // Generar partículas decorativas
            generateParticles();
            
            // Efecto de scroll suave para enlaces internos
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        e.preventDefault();
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Observador de intersección para animaciones
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in-up');
                    }
                });
            }, observerOptions);
            
            // Observar elementos que queremos animar
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        });
        
        // Función para generar partículas decorativas
        function generateParticles() {
            const container = document.getElementById('particlesContainer');
            if (!container) return;
            
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Posición aleatoria
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;
                
                // Tamaño aleatorio
                const size = Math.random() * 2 + 1;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Opacidad aleatoria
                particle.style.opacity = Math.random() * 0.5;
                
                // Retardo de animación aleatorio
                particle.style.animationDelay = `${Math.random() * 8}s`;
                
                container.appendChild(particle);
            }
        }
        
        // Actualizar partículas al redimensionar la ventana
        window.addEventListener('resize', function() {
            const container = document.getElementById('particlesContainer');
            if (container) {
                container.innerHTML = '';
                generateParticles();
            }
        });
    </script>

    <!-- CSRF initialization for axios/fetch -->
    <script>
        (function () {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : null;

            if (typeof axios !== 'undefined') {
                axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
                if (csrfToken) {
                    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
                }
            }

            if (csrfToken) {
                window.__CSRF_TOKEN__ = csrfToken;
                window.fetchWithCsrf = function(url, options = {}) {
                    options.headers = Object.assign({}, options.headers || {}, {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    });
                    return fetch(url, options);
                };
            }
        })();
    </script>
    
    @stack('scripts')
</body>
</html>
