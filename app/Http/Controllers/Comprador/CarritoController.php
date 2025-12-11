<?php

namespace App\Http\Controllers\Comprador;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Producto;
use App\Models\Cupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CarritoController extends Controller
{
    /**
     * Obtener el carrito del usuario
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $carrito = $user->carrito()->with(['items.producto.vendedor', 'items.producto.imagenes'])->first();

        if (!$carrito) {
            $carrito = $user->carrito()->create();
        }

        $items = $carrito->items()->with(['producto.vendedor', 'producto.imagenes'])->get();
        
        $subtotal = $carrito->calcularSubtotal();
        $totalItems = $carrito->calcularTotalItems();

        return response()->json([
            'success' => true,
            'carrito' => $carrito,
            'items' => $items,
            'subtotal' => $subtotal,
            'total_items' => $totalItems,
            'agrupado_por_vendedor' => $carrito->agruparPorVendedor(),
        ]);
    }

    /**
     * Agregar producto al carrito
     */
    public function addItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'producto_id' => ['required', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $producto = Producto::findOrFail($request->producto_id);

        // Verificar que el producto esté activo
        if (!$producto->activo || !$producto->aprobado) {
            return response()->json([
                'success' => false,
                'message' => 'El producto no está disponible'
            ], 400);
        }

        // Verificar stock
        if ($producto->stock < $request->cantidad) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuficiente. Disponible: ' . $producto->stock,
                'stock_disponible' => $producto->stock
            ], 400);
        }

        DB::beginTransaction();
        try {
            $carrito = $user->carrito ?? $user->carrito()->create();
            $item = $carrito->agregarProducto($request->producto_id, $request->cantidad);

            // Registrar en bitácora
            \App\Models\BitacoraSistema::registrarCreacion(
                $user->id,
                'CarritoItem',
                $item->id,
                $item->toArray()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'item' => $item->load('producto'),
                'carrito' => $carrito->fresh(['items.producto']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar al carrito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar cantidad de un item en el carrito
     */
    public function updateItem(Request $request, $itemId)
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $carrito = $user->carrito;

        if (!$carrito) {
            return response()->json([
                'success' => false,
                'message' => 'Carrito no encontrado'
            ], 404);
        }

        $item = $carrito->items()->findOrFail($itemId);
        $producto = $item->producto;

        // Verificar stock
        if ($producto->stock < $request->cantidad) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuficiente. Disponible: ' . $producto->stock,
                'stock_disponible' => $producto->stock
            ], 400);
        }

        DB::beginTransaction();
        try {
            $datosAnteriores = $item->toArray();
            $item = $carrito->actualizarCantidad($item->producto_id, $request->cantidad);
            $datosNuevos = $item->toArray();

            // Registrar en bitácora
            \App\Models\BitacoraSistema::registrarActualizacion(
                $user->id,
                'CarritoItem',
                $item->id,
                $datosAnteriores,
                $datosNuevos
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cantidad actualizada',
                'item' => $item->load('producto'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar cantidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar item del carrito
     */
    public function removeItem(Request $request, $itemId)
    {
        $user = $request->user();
        $carrito = $user->carrito;

        if (!$carrito) {
            return response()->json([
                'success' => false,
                'message' => 'Carrito no encontrado'
            ], 404);
        }

        $item = $carrito->items()->findOrFail($itemId);

        DB::beginTransaction();
        try {
            $datosItem = $item->toArray();
            $carrito->eliminarProducto($item->producto_id);

            // Registrar en bitácora
            \App\Models\BitacoraSistema::registrarEliminacion(
                $user->id,
                'CarritoItem',
                $itemId,
                $datosItem
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado del carrito',
                'carrito' => $carrito->fresh(['items.producto']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar del carrito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vaciar carrito
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        $carrito = $user->carrito;

        if (!$carrito) {
            return response()->json([
                'success' => false,
                'message' => 'Carrito no encontrado'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $items = $carrito->items()->get();
            $carrito->vaciar();

            // Registrar en bitácora
            \App\Models\BitacoraSistema::registrar(
                $user->id,
                'vaciar_carrito',
                'Carrito',
                $carrito->id,
                'Carrito vaciado',
                ['items_count' => $items->count()],
                null
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Carrito vaciado',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al vaciar carrito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aplicar cupón al carrito
     */
    public function applyCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codigo' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $carrito = $user->carrito;

        if (!$carrito) {
            return response()->json([
                'success' => false,
                'message' => 'Carrito no encontrado'
            ], 404);
        }

        try {
            $resultado = $carrito->aplicarCupon($request->codigo);

            return response()->json([
                'success' => true,
                'message' => 'Cupón aplicado exitosamente',
                'cupon' => $resultado['cupon'],
                'descuento' => $resultado['descuento'],
                'subtotal_con_descuento' => $resultado['subtotal_con_descuento'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remover cupón del carrito
     */
    public function removeCoupon(Request $request)
    {
        $user = $request->user();
        $carrito = $user->carrito;

        if (!$carrito) {
            return response()->json([
                'success' => false,
                'message' => 'Carrito no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cupón removido',
            'subtotal' => $carrito->calcularSubtotal(),
        ]);
    }

    /**
     * Calcular costo de envío
     */
    public function calculateShipping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'metodo_envio_id' => ['required', 'exists:metodos_envio,id'],
            'estado_envio' => ['required', 'string'],
            'ciudad_envio' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $carrito = $user->carrito;

        if (!$carrito) {
            return response()->json([
                'success' => false,
                'message' => 'Carrito no encontrado'
            ], 404);
        }

        try {
            $costoEnvio = $carrito->calcularEnvio($request->metodo_envio_id, [
                'estado' => $request->estado_envio,
                'ciudad' => $request->ciudad_envio,
            ]);

            return response()->json([
                'success' => true,
                'costo_envio' => $costoEnvio,
                'subtotal' => $carrito->calcularSubtotal(),
                'total' => $carrito->calcularSubtotal() + $costoEnvio,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al calcular envío: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verificar disponibilidad de productos en el carrito
     */
    public function checkAvailability(Request $request)
    {
        $user = $request->user();
        $carrito = $user->carrito;

        if (!$carrito) {
            return response()->json([
                'success' => false,
                'message' => 'Carrito no encontrado'
            ], 404);
        }

        $verificacion = $carrito->verificarDisponibilidad();

        return response()->json([
            'success' => true,
            'disponible' => $verificacion['disponible'],
            'productos_no_disponibles' => $verificacion['productos_no_disponibles'],
            'mensaje' => $verificacion['disponible'] 
                ? 'Todos los productos están disponibles' 
                : 'Algunos productos no están disponibles',
        ]);
    }

    /**
     * Mover producto del carrito a wishlist
     */
    public function moveToWishlist(Request $request, $itemId)
    {
        $user = $request->user();
        $carrito = $user->carrito;

        if (!$carrito) {
            return response()->json([
                'success' => false,
                'message' => 'Carrito no encontrado'
            ], 404);
        }

        $item = $carrito->items()->findOrFail($itemId);

        DB::beginTransaction();
        try {
            // Agregar a wishlist
            $user->agregarProductoAWishlist($item->producto_id);
            
            // Eliminar del carrito
            $carrito->eliminarProducto($item->producto_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto movido a la lista de deseos',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al mover a wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen del carrito
     */
    public function summary(Request $request)
    {
        $user = $request->user();
        $carrito = $user->carrito;

        if (!$carrito) {
            return response()->json([
                'success' => false,
                'message' => 'Carrito no encontrado'
            ], 404);
        }

        $subtotal = $carrito->calcularSubtotal();
        $totalItems = $carrito->calcularTotalItems();
        $items = $carrito->items()->with('producto')->get();

        return response()->json([
            'success' => true,
            'subtotal' => $subtotal,
            'total_items' => $totalItems,
            'items_count' => $items->count(),
            'agrupado_por_vendedor' => $carrito->agruparPorVendedor(),
            'productos' => $items->map(function($item) {
                return [
                    'id' => $item->producto_id,
                    'nombre' => $item->producto->nombre,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->subtotal,
                    'stock' => $item->producto->stock,
                ];
            }),
        ]);
    }
}