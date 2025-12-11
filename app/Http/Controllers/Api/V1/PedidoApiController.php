<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Carrito;
use App\Models\Producto;

class CarritoApiController extends ApiController
{
    public function index(Request $request)
    {
        $carrito = $request->user()->carrito()->with(['items.producto.vendedor', 'items.producto.imagenes'])->first();
        if (!$carrito) {
            $carrito = $request->user()->carrito()->create();
        }
        $carrito->load(['items.producto.vendedor', 'items.producto.imagenes']);
        return $this->success($carrito);
    }

    public function addItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $user = $request->user();
        $producto = Producto::findOrFail($request->producto_id);

        if (!$producto->activo || !$producto->aprobado) {
            return $this->error('Producto no disponible');
        }
        if ($producto->stock < $request->cantidad) {
            return $this->error("Stock insuficiente. Disponible: {$producto->stock}");
        }

        $carrito = $user->carrito ?? $user->carrito()->create();
        $item = $carrito->agregarProducto($request->producto_id, $request->cantidad);

        return $this->success($item->load('producto'), 'Producto agregado al carrito', 201);
    }

    public function removeItem($itemId)
    {
        $carrito = $request->user()->carrito;
        if (!$carrito) {
            return $this->notFound('Carrito no encontrado');
        }
        $carrito->eliminarProducto($itemId);
        return $this->success(null, 'Producto eliminado del carrito');
    }

    public function clear()
    {
        $carrito = $request->user()->carrito;
        if ($carrito) {
            $carrito->vaciar();
        }
        return $this->success(null, 'Carrito vaciado');
    }

    public function summary()
    {
        $carrito = $request->user()->carrito;
        if (!$carrito) {
            return $this->success([
                'subtotal' => 0,
                'total_items' => 0,
                'items' => []
            ]);
        }

        return $this->success([
            'subtotal' => $carrito->calcularSubtotal(),
            'total_items' => $carrito->calcularTotalItems(),
            'items' => $carrito->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'producto_id' => $item->producto_id,
                    'nombre' => $item->producto->nombre,
                    'precio' => $item->precio_unitario,
                    'cantidad' => $item->cantidad,
                    'subtotal' => $item->subtotal,
                    'imagen' => $item->producto->imagen_url,
                ];
            })
        ]);
    }
}