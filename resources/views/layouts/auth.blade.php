<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Monagas Vende')</title>
    
    <!-- Google Fonts: Orbitron (Futurista) & Rajdhani (Tecnológico) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    

    <!-- Vite Assets (Bootstrap + Custom) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --neon-primary: #00f3ff;
            --neon-secondary: #bc13fe;
            --bg-dark: #0a0a12;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-main: #e0e0e0;
        }

        body {
            font-family: 'Rajdhani', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Fondo Animado */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 15% 50%, rgba(188, 19, 254, 0.15), transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(0, 243, 255, 0.15), transparent 25%);
            z-index: -1;
            animation: gradientMove 15s ease-in-out infinite alternate;
        }

        @keyframes gradientMove {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        /* Glassmorphism Card */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.03),
                transparent
            );
            transform: rotate(45deg);
            animation: shine 6s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        /* Tipografía Futurista */
        h1, h2, h3, h4, h5, h6, .display-font {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 2px;
            text-transform: uppercase;
            background: linear-gradient(45deg, #fff, var(--neon-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(0, 243, 255, 0.3);
        }

        /* Inputs Futuristas */
        .form-control, .form-select {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--glass-border);
            color: #fff;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: rgba(0, 0, 0, 0.4);
            border-color: var(--neon-primary);
            box-shadow: 0 0 15px rgba(0, 243, 255, 0.2);
            color: #fff;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        /* Botones Neón */
        .btn-neon-primary {
            background: transparent;
            color: var(--neon-primary);
            border: 1px solid var(--neon-primary);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Orbitron', sans-serif;
            padding: 12px 30px;
            position: relative;
            transition: all 0.3s ease;
            overflow: hidden;
            border-radius: 5px;
            font-weight: 700;
        }

        .btn-neon-primary:hover {
            background: var(--neon-primary);
            color: #000;
            box-shadow: 0 0 20px var(--neon-primary), 0 0 40px var(--neon-primary);
        }

        .btn-neon-secondary {
            background: transparent;
            color: var(--neon-secondary);
            border: 1px solid var(--neon-secondary);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Orbitron', sans-serif;
            padding: 12px 30px;
            transition: all 0.3s ease;
            border-radius: 5px;
            font-weight: 700;
        }

        .btn-neon-secondary:hover {
            background: var(--neon-secondary);
            color: #fff;
            box-shadow: 0 0 20px var(--neon-secondary);
        }

        /* Links */
        a {
            color: var(--neon-primary);
            text-decoration: none;
            transition: 0.3s;
        }

        a:hover {
            color: #fff;
            text-shadow: 0 0 10px var(--neon-primary);
        }

        /* Utilidades */
        .text-neon { color: var(--neon-primary); }
        .border-neon { border-color: var(--neon-primary) !important; }
        
        .input-group-text {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            border-right: none;
            color: var(--neon-primary);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0a0a12;
        }
        ::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--neon-primary);
        }
        
        .logo-glow {
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.5));
            animation: logoPulse 3s infinite alternate;
        }
        
        @keyframes logoPulse {
            from { filter: drop-shadow(0 0 5px rgba(0, 243, 255, 0.3)); }
            to { filter: drop-shadow(0 0 15px rgba(0, 243, 255, 0.6)); }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 text-center mb-4">
                <a href="{{ url('/') }}" class="text-decoration-none">
                    <h1 class="mb-0 display-4 logo-glow">MONAGAS<span class="text-neon">VENDE</span></h1>
                    <p class="text-muted letter-spacing-2">MERCADO DIGITAL FUTURISTA</p>
                </a>
            </div>
            
            @yield('content')
            
            <div class="col-12 text-center mt-5">
                <p class="text-muted small">&copy; {{ date('Y') }} Monagas Vende. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
