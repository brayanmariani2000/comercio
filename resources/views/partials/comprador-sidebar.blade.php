<div class="card border-0 shadow-sm" style="background-color: #1e293b; border-radius: 1rem;">
    <div class="card-body p-3">
        <div class="d-flex align-items-center mb-4 px-2">
            <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-3 text-primary">
                <i class="fas fa-user-circle fa-2x"></i>
            </div>
            <div>
                <h6 class="mb-0 text-white fw-bold">{{ auth()->user()->name }}</h6>
                <small class="text-muted">Comprador</small>
            </div>
        </div>

        <div class="list-group list-group-flush">
            <a href="{{ route('comprador.dashboard') }}" class="list-group-item list-group-item-action bg-transparent border-0 text-white px-3 py-2 rounded mb-1 {{ request()->routeIs('comprador.dashboard') ? 'active bg-primary' : '' }}">
                <i class="fas fa-tachometer-alt me-2 fa-fw"></i> Dashboard
            </a>
            
            <a href="{{ route('comprador.pedidos.index') }}" class="list-group-item list-group-item-action bg-transparent border-0 text-white px-3 py-2 rounded mb-1 {{ request()->routeIs('comprador.pedidos.*') ? 'active bg-primary' : '' }}">
                <i class="fas fa-shopping-bag me-2 fa-fw"></i> Mis Pedidos
            </a>

            <a href="{{ route('comprador.carrito') }}" class="list-group-item list-group-item-action bg-transparent border-0 text-white px-3 py-2 rounded mb-1 {{ request()->routeIs('comprador.carrito') ? 'active bg-primary' : '' }}">
                <i class="fas fa-shopping-cart me-2 fa-fw"></i> Mi Carrito
            </a>

            <a href="{{ route('comprador.perfil') }}" class="list-group-item list-group-item-action bg-transparent border-0 text-white px-3 py-2 rounded mb-1 {{ request()->routeIs('comprador.perfil') ? 'active bg-primary' : '' }}">
                <i class="fas fa-user-cog me-2 fa-fw"></i> Mi Perfil
            </a>
            
            <hr class="border-secondary border-opacity-25 my-2">

            <a href="{{ route('home') }}" class="list-group-item list-group-item-action bg-transparent border-0 text-muted px-3 py-2 rounded mb-1">
                <i class="fas fa-arrow-left me-2 fa-fw"></i> Volver a la Tienda
            </a>

            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="list-group-item list-group-item-action bg-transparent border-0 text-danger px-3 py-2 rounded w-100 text-start">
                    <i class="fas fa-sign-out-alt me-2 fa-fw"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</div>
