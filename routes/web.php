<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sistema\HomeController;
use App\Http\Controllers\Sistema\ProductoController;
use App\Http\Controllers\Sistema\CategoriaController;
use App\Http\Controllers\Sistema\BusquedaController;
use App\Http\Controllers\Sistema\ValidacionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Comprador\PedidoController;
use App\Http\Controllers\Comprador\DashboardController;
use App\Http\Controllers\Comprador\CarritoController;
use App\Http\Controllers\Vendedor\ProductoVendedorController;
use App\Http\Controllers\Admin\ProductoAdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/ofertas', [ProductoController::class, 'ofertas'])->name('ofertas');
Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('productos.show');
Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
Route::get('/categorias/{slug}', [CategoriaController::class, 'show'])->name('categorias.show');
Route::get('/buscar', [BusquedaController::class, 'search'])->name('busqueda');

// **RUTAS DE AUTENTICACIÓN - AÑADE ESTAS RUTAS GET**
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');

// Rutas de autenticación API (POST)
Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth:sanctum');

// Registro de Vendedor (flujo post-registro)
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/vendedor/registro', [AuthController::class, 'showVendorRegisterForm'])->name('vendedor.registro');
    Route::post('/vendedor/registro', [AuthController::class, 'registerAsVendor'])->name('vendedor.registro.store');
});

// Rutas de Verificación de Email
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/email/verify', [App\Http\Controllers\Auth\EmailVerificationController::class, 'show'])
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [App\Http\Controllers\Auth\EmailVerificationController::class, 'resend'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
});

// Rutas públicas que requieren POST
Route::post('/pedidos', [PedidoController::class, 'store']);
Route::post('/api/validar-compra', [ValidacionController::class, 'validarCompra']);

// Rutas protegidas generales
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/perfil', [AuthController::class, 'profile'])->name('perfil');
    Route::get('/configuracion', [AuthController::class, 'config'])->name('configuracion');
});

// Rutas protegidas - Comprador
Route::middleware(['auth:sanctum', 'comprador'])->prefix('comprador')->name('comprador.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/perfil', [AuthController::class, 'profile'])->name('perfil');
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{id}', [PedidoController::class, 'show'])->name('pedidos.show');
    
    // Carrito
    Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');
    Route::post('/carrito', [CarritoController::class, 'store'])->name('carrito.store');
    Route::post('/carrito/add', [CarritoController::class, 'addItem'])->name('carrito.add');
    Route::put('/carrito/{id}', [CarritoController::class, 'update'])->name('carrito.update');
    Route::get('/carrito/summary', [CarritoController::class, 'summary'])->name('carrito.summary');
    Route::delete('/carrito/{id}', [CarritoController::class, 'destroy'])->name('carrito.destroy');

    // Mensajes
    Route::get('/mensajes', [\App\Http\Controllers\Comprador\ChatCompradorController::class, 'index'])->name('mensajes.index');
    Route::post('/mensajes', [\App\Http\Controllers\Comprador\ChatCompradorController::class, 'store'])->name('mensajes.store');
    Route::get('/mensajes/{id}', [\App\Http\Controllers\Comprador\ChatCompradorController::class, 'show'])->name('mensajes.show');
    Route::post('/mensajes/{id}', [\App\Http\Controllers\Comprador\ChatCompradorController::class, 'sendMessage'])->name('mensajes.reply');
});

// Rutas protegidas - Vendedor
Route::middleware(['auth:sanctum', 'vendedor'])->prefix('vendedor')->name('vendedor.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Vendedor\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/productos/crear', [\App\Http\Controllers\Vendedor\ProductoVendedorController::class, 'create'])->name('productos.create');
    Route::post('/productos', [\App\Http\Controllers\Vendedor\ProductoVendedorController::class, 'store'])->name('productos.store');
});

// Rutas protegidas - Admin (si las necesitas)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/productos/{id}/aprobar', [ProductoAdminController::class, 'aprobar'])->name('productos.aprobar');
    
    // Vendedores
    Route::get('/vendedores', [\App\Http\Controllers\Admin\VendedorAdminController::class, 'index'])->name('vendedores.index');
    Route::post('/vendedores/{id}/aprobar', [\App\Http\Controllers\Admin\VendedorAdminController::class, 'verificar'])->name('vendedores.aprobar');
    Route::post('/vendedores/{id}/rechazar', [\App\Http\Controllers\Admin\VendedorAdminController::class, 'rechazar'])->name('vendedores.rechazar');
});