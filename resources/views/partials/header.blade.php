<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-danger" href="{{ route('home') }}">
            <i class="fas fa-store me-2"></i>Monagas Vende
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('categorias.index') }}">Categorías</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('productos.index') }}">Productos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('busqueda') }}">Buscar</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Iniciar sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-danger btn-sm" href="{{ route('register') }}">Registrarse</a>
                    </li>
                @else
                    @if(auth()->user()->esComprador())
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="{{ route('comprador.carrito') }}">
                                <i class="fas fa-shopping-cart"></i>
                                @if(auth()->user()->carrito && auth()->user()->carrito->items->count() > 0)
                                    <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                                        {{ auth()->user()->carrito->items->count() }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('comprador.dashboard') }}">Mi cuenta</a>
                        </li>
                    @elseif(auth()->user()->esVendedor())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('vendedor.dashboard') }}">Panel vendedor</a>
                        </li>
                    @elseif(auth()->user()->esAdministrador())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a>
                        </li>
                    @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>{{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('perfil') }}"><i class="fas fa-user-circle me-2"></i>Mi perfil</a></li>
                            @if(auth()->user()->hasRole('comprador'))
                                <li><a class="dropdown-item" href="{{ route('comprador.pedidos.index') }}"><i class="fas fa-box me-2"></i>Mis pedidos</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>