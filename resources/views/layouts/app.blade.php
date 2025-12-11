<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mercado Electrónico Venezuela')</title>
    <meta name="description" content="@yield('description', 'Compra electrónicos al mejor precio en Venezuela')">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand { font-weight: bold; color: #e53935 !important; }
        .btn-primary { background-color: #e53935; border-color: #e53935; }
        .btn-primary:hover { background-color: #c62828; }
    </style>
    @stack('styles')
</head>
<body>
    @include('partials.header')

    <main class="container my-4">
        @include('partials.flash-messages')
        @yield('content')
    </main>

    @include('partials.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>