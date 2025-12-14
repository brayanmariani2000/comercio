<?php
namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;

class BusquedaController extends Controller
{
    public function search(Request $request)
    {
        $query = Producto::activos()->with(['vendedor', 'imagenes']);

        if ($request->filled('q')) {
            $query->buscar($request->q);
        }

        // Filtros adicionales
        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }

        if ($request->filled('precio_min') && $request->filled('precio_max')) {
            $query->whereBetween('precio', [$request->precio_min, $request->precio_max]);
        }

        if ($request->filled('envio_gratis')) {
            $query->where('envio_gratis', true);
        }

        if ($request->filled('oferta')) {
            $query->where('oferta', true);
        }

        // Orden
        $query->ordenarPor($request->get('orden', 'mas_relevantes'));

        $productos = $query->paginate(24);

        return view('sistema.busqueda.resultados', compact('productos'));
    }

    public function sugerencias(Request $request)
    {
        if (!$request->filled('q')) {
            return response()->json(['sugerencias' => []]);
        }

        $productos = Producto::activos()
            ->where('nombre', 'LIKE', "%{$request->q}%")
            ->limit(8)
            ->get(['nombre', 'slug', 'precio']);

        return response()->json(['sugerencias' => $productos]);
    }
}