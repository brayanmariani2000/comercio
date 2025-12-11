<?php
namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Productos destacados
        $destacados = Producto::activos()
            ->destacados()
            ->with(['vendedor', 'imagenes'])
            ->limit(12)
            ->get();

        // Ofertas del día
        $ofertas = Producto::activos()
            ->enOferta()
            ->with(['vendedor', 'imagenes'])
            ->limit(12)
            ->get();

        // Nuevos productos
        $nuevos = Producto::activos()
            ->nuevos()
            ->with(['vendedor', 'imagenes'])
            ->limit(12)
            ->get();

        // Categorías principales
        $categorias = Categoria::activas()
            ->mostrarEnInicio()
            ->limit(8)
            ->get();

        // Pasar datos a la vista
        return view('sistema/home', [
            'destacados' => $destacados,
            'ofertas' => $ofertas,
            'nuevos' => $nuevos,
            'categorias' => $categorias
        ]);
    }
}