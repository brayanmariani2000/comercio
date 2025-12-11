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

// Rutas públicas que requieren POST
Route::post('/pedidos', [PedidoController::class, 'store']);
Route::post('/api/validar-compra', [ValidacionController::class, 'validarCompra']);
Route::get('/buscar', [Sistema\BusquedaController::class, 'search'])->name('busqueda');
// Rutas protegidas - Comprador
Route::middleware(['auth:sanctum', 'comprador'])->prefix('comprador')->name('comprador.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/perfil', [AuthController::class, 'profile'])->name('perfil');
    
    // Carrito
    Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');
    Route::post('/carrito', [CarritoController::class, 'store'])->name('carrito.store');
    Route::post('/carrito/add', [CarritoController::class, 'addItem'])->name('carrito.add');
    Route::put('/carrito/{id}', [CarritoController::class, 'update'])->name('carrito.update');
    Route::get('/carrito/summary', [CarritoController::class, 'summary'])->name('carrito.summary');
    Route::delete('/carrito/{id}', [CarritoController::class, 'destroy'])->name('carrito.destroy');
});

// Rutas protegidas - Vendedor (si las necesitas)
Route::middleware(['auth:sanctum', 'vendedor'])->prefix('vendedor')->name('vendedor.')->group(function () {
    Route::post('/productos', [ProductoVendedorController::class, 'store'])->name('productos.store');
});

// Rutas protegidas - Admin (si las necesitas)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/productos/{id}/aprobar', [ProductoAdminController::class, 'aprobar'])->name('productos.aprobar');
});