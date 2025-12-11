<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Categoria;

class ProductoApiController extends ApiController
{
    public function index(Request $request)
    {
        $query = Producto::activos()->with(['vendedor', 'imagenes', 'categoria']);

        if ($request->filled('q')) {
            $query->buscar($request->q);
        }
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }
        if ($request->filled('precio_min') && $request->filled('precio_max')) {
            $query->whereBetween('precio', [$request->precio_min, $request->precio_max]);
        }
        if ($request->filled('envio_gratis')) {
            $query->where('envio_gratis', true);
        }

        $query->ordenarPor($request->get('orden', 'mas_relevantes'));

        $productos = $query->paginate($request->get('per_page', 20));

        return $this->success($productos);
    }

    public function show($id)
    {
        $producto = Producto::activos()
            ->with([
                'vendedor',
                'categoria',
                'imagenes',
                'resenas.aprobadas',
                'preguntas.activas'
            ])
            ->findOrFail($id);

        $producto->aumentarVistas();

        // Verificar si el usuario autenticado lo ha comprado
        $compraVerificada = false;
        if ($user = request()->user()) {
            $compraVerificada = $user->pedidos()
                ->whereHas('items', fn($q) => $q->where('producto_id', $id))
                ->where('estado_pedido', 'entregado')
                ->exists();
        }

        return $this->success([
            'producto' => $producto,
            'compra_verificada' => $compraVerificada,
            'puede_resenar' => $compraVerificada && !$producto->resenas()->where('user_id', $user?->id)->exists(),
        ]);
    }

    public function buscar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:50'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $productos = Producto::activos()
            ->where('nombre', 'LIKE', "%{$request->q}%")
            ->limit(8)
            ->get(['id', 'nombre', 'precio', 'imagen_url']);

        return $this->success($productos, 'Sugerencias de búsqueda');
    }

    public function categorias()
    {
        $categorias = Categoria::activas()->principales()->with('subcategorias')->get();
        return $this->success($categorias);
    }
}