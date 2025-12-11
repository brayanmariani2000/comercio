<?php
namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::activas()
            ->principales()
            ->with('subcategorias')
            ->get();

        return response()->json([
            'success' => true,
            'categorias' => $categorias
        ]);
    }

    public function show($slug)
    {
        $categoria = Categoria::where('slug', $slug)->firstOrFail();

        // Filtros desde request
        $filtros = request()->all();

        // Productos con filtros
        $productos = $categoria->obtenerProductosPorFiltros($filtros, 24);

        // Atributos para sidebar/filtros
        $atributos = $categoria->obtenerAtributosFiltrables();

        return response()->json([
            'success' => true,
            'categoria' => $categoria,
            'productos' => $productos,
            'filtros_disponibles' => $atributos,
            'ruta_completa' => $categoria->ruta_completa
        ]);
    }
}