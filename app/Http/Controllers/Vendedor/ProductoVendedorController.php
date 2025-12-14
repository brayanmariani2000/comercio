<?php

namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\ProductoImagen;
use App\Models\HistorialPrecio;
use App\Models\BitacoraSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductoVendedorController extends Controller
{
    /**
     * Listar productos del vendedor
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $query = $vendedor->productos()->with(['categoria', 'imagenes']);

        // Filtrar por estado
        if ($request->has('estado')) {
            if ($request->estado === 'activos') {
                $query->where('activo', true)->where('aprobado', true);
            } elseif ($request->estado === 'inactivos') {
                $query->where('activo', false);
            } elseif ($request->estado === 'pendientes') {
                $query->where('aprobado', false);
            } elseif ($request->estado === 'rechazados') {
                $query->where('aprobado', false)->whereNotNull('razon_rechazo');
            }
        }

        // Filtrar por stock
        if ($request->has('stock')) {
            if ($request->stock === 'bajo') {
                $query->whereRaw('stock <= stock_minimo');
            } elseif ($request->stock === 'agotado') {
                $query->where('stock', 0);
            } elseif ($request->stock === 'disponible') {
                $query->where('stock', '>', 0);
            }
        }

        // Buscar por término
        if ($request->has('search')) {
            $query->where('nombre', 'LIKE', "%{$request->search}%")
                  ->orWhere('codigo', 'LIKE', "%{$request->search}%")
                  ->orWhere('sku', 'LIKE', "%{$request->search}%");
        }

        // Ordenar
        $orden = $request->get('orden', 'recientes');
        switch ($orden) {
            case 'recientes':
                $query->orderBy('created_at', 'desc');
                break;
            case 'antiguos':
                $query->orderBy('created_at', 'asc');
                break;
            case 'nombre_asc':
                $query->orderBy('nombre');
                break;
            case 'nombre_desc':
                $query->orderBy('nombre', 'desc');
                break;
            case 'precio_asc':
                $query->orderBy('precio');
                break;
            case 'precio_desc':
                $query->orderBy('precio', 'desc');
                break;
            case 'mas_vendidos':
                $query->orderBy('ventas', 'desc');
                break;
            case 'mas_vistos':
                $query->orderBy('vistas', 'desc');
                break;
        }

        $productos = $query->paginate($request->get('per_page', 20));

        // Estadísticas
        $estadisticas = [
            'total' => $vendedor->productos()->count(),
            'activos' => $vendedor->productos()->where('activo', true)->where('aprobado', true)->count(),
            'pendientes' => $vendedor->productos()->where('aprobado', false)->count(),
            'inactivos' => $vendedor->productos()->where('activo', false)->count(),
            'sin_stock' => $vendedor->productos()->where('stock', 0)->count(),
            'stock_bajo' => $vendedor->productos()->whereRaw('stock <= stock_minimo')->where('stock', '>', 0)->count(),
            'total_ventas' => $vendedor->productos()->sum('ventas'),
            'total_vistas' => $vendedor->productos()->sum('vistas'),
        ];

        return response()->json([
            'success' => true,
            'productos' => $productos,
            'estadisticas' => $estadisticas,
            'limite_productos' => $vendedor->obtenerLimiteProductos(),
            'productos_publicados' => $vendedor->productos()->count(),
            'puede_publicar' => $vendedor->puedePublicarProducto(),
        ]);
    }

    /**
     * Mostrar detalle de producto del vendedor
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $producto = $vendedor->productos()
            ->with(['categoria', 'imagenes', 'resenas', 'preguntas', 'historialPrecios'])
            ->findOrFail($id);

        // Estadísticas del producto
        $estadisticas = $producto->obtenerEstadisticasVentas('30dias');

        return response()->json([
            'success' => true,
            'producto' => $producto,
            'estadisticas' => $estadisticas,
            'en_wishlists' => $producto->wishlistItems()->count(),
            'total_preguntas' => $producto->preguntas()->count(),
            'total_resenas' => $producto->resenas()->count(),
            'rating_promedio' => $producto->calificacion_promedio,
        ]);
    }

    /**
     * Mostrar formulario de crear producto
     */
    public function create()
    {
        $categorias = Categoria::whereNull('categoria_padre_id')->with('subcategorias')->get();
        return view('vendedor.productos.create', compact('categorias'));
    }

    /**
     * Crear nuevo producto
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        // Verificar si puede publicar
        if (!$vendedor->puedePublicarProducto()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes publicar más productos. Límite alcanzado o membresía expirada.'
                ], 400);
            }
            return back()->with('error', 'No puedes publicar más productos. Límite alcanzado o membresía expirada.');
        }

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string', 'min:50'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'garantia' => ['nullable', 'string', 'max:100'],
            'especificaciones' => ['nullable', 'array'],
            'imagenes' => ['required', 'array', 'min:1', 'max:10'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'precio_descuento' => ['nullable', 'numeric', 'lt:precio'],
            'envio_gratis' => ['boolean'],
            'costo_envio' => ['nullable', 'numeric', 'min:0'],
            'dias_entrega' => ['nullable', 'integer', 'min:1'],
            'peso' => ['nullable', 'numeric', 'min:0'],
            'dimensiones' => ['nullable', 'array'],
            'dimensiones.largo' => ['nullable', 'numeric', 'min:0'],
            'dimensiones.ancho' => ['nullable', 'numeric', 'min:0'],
            'dimensiones.alto' => ['nullable', 'numeric', 'min:0'],
            'color' => ['nullable', 'string'],
            'condicion' => ['required', 'in:nuevo,usado,reacondicionado'],
            'tags' => ['nullable', 'array'],
            'destacado' => ['boolean'],
            'oferta' => ['boolean'],
            'stock_minimo' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Crear producto
            $producto = Producto::create([
                'vendedor_id' => $vendedor->id,
                'categoria_id' => $request->categoria_id,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
                'precio_descuento' => $request->precio_descuento,
                'stock' => $request->stock,
                'stock_minimo' => $request->stock_minimo ?? 5,
                'marca' => $request->marca,
                'modelo' => $request->modelo,
                'garantia' => $request->garantia,
                'especificaciones' => $request->especificaciones,
                'envio_gratis' => $request->envio_gratis ?? false,
                'costo_envio' => $request->costo_envio,
                'dias_entrega' => $request->dias_entrega ?? 3,
                'peso' => $request->peso,
                'dimensiones' => $request->dimensiones,
                'color' => $request->color,
                'condicion' => $request->condicion,
                'nuevo' => $request->condicion === 'nuevo',
                'tags' => $request->tags,
                'destacado' => $request->destacado ?? false,
                'oferta' => $request->oferta ?? false,
                'activo' => true,
                'aprobado' => false, // Requiere aprobación de administrador
            ]);

            // Subir imágenes
            if ($request->hasFile('imagenes')) {
                foreach ($request->file('imagenes') as $index => $imagen) {
                    $path = $imagen->store('productos/' . $producto->id, 'public');
                    $producto->agregarImagen($path, $index === 0);
                }
            }

            // Registrar en bitácora
            BitacoraSistema::registrarCreacion(
                $user->id,
                'Producto',
                $producto->id,
                $producto->toArray()
            );

            // Notificar a administradores
            \App\Models\User::where('tipo_usuario', 'administrador')
                ->get()
                ->each(function($admin) use ($producto) {
                    $admin->generarNotificacion(
                        'Producto pendiente de aprobación',
                        "El producto '{$producto->nombre}' está pendiente de aprobación",
                        'producto',
                        ['producto_id' => $producto->id, 'vendedor_id' => $producto->vendedor_id]
                    );
                });

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Producto creado exitosamente. Esperando aprobación.',
                    'producto' => $producto->load(['categoria', 'imagenes']),
                ], 201);
            }

            return redirect()->route('vendedor.dashboard')
                ->with('success', 'Producto creado exitosamente. Estará visible cuando sea aprobado por un administrador.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear producto: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error al crear producto: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Actualizar producto
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $producto = $vendedor->productos()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => ['sometimes', 'string', 'max:255'],
            'descripcion' => ['sometimes', 'string', 'min:50'],
            'categoria_id' => ['sometimes', 'exists:categorias,id'],
            'precio' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'marca' => ['nullable', 'string', 'max:100'],
            'modelo' => ['nullable', 'string', 'max:100'],
            'garantia' => ['nullable', 'string', 'max:100'],
            'especificaciones' => ['nullable', 'array'],
            'precio_descuento' => ['nullable', 'numeric', 'lt:precio'],
            'envio_gratis' => ['boolean'],
            'costo_envio' => ['nullable', 'numeric', 'min:0'],
            'dias_entrega' => ['nullable', 'integer', 'min:1'],
            'destacado' => ['boolean'],
            'oferta' => ['boolean'],
            'activo' => ['boolean'],
            'stock_minimo' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $datosAnteriores = $producto->toArray();
            
            // Si cambia el precio, requiere aprobación nuevamente
            if ($request->has('precio') && $request->precio != $producto->precio) {
                $producto->aprobado = false;
            }

            $producto->update($request->all());
            $datosNuevos = $producto->toArray();

            // Registrar en bitácora
            BitacoraSistema::registrarActualizacion(
                $user->id,
                'Producto',
                $producto->id,
                $datosAnteriores,
                $datosNuevos
            );

            // Si se desactiva, notificar
            if ($request->has('activo') && !$request->activo && $producto->activo) {
                $user->generarNotificacion(
                    'Producto desactivado',
                    "El producto {$producto->nombre} ha sido desactivado",
                    'producto',
                    ['producto_id' => $producto->id]
                );
            }

            // Si requiere nueva aprobación
            if (!$producto->aprobado && $datosAnteriores['aprobado']) {
                \App\Models\User::where('tipo_usuario', 'administrador')
                    ->get()
                    ->each(function($admin) use ($producto) {
                        $admin->generarNotificacion(
                            'Producto modificado requiere aprobación',
                            "El producto '{$producto->nombre}' fue modificado y requiere nueva aprobación",
                            'producto',
                            ['producto_id' => $producto->id]
                        );
                    });
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'producto' => $producto->fresh(['categoria', 'imagenes']),
                'requiere_aprobacion' => !$producto->aprobado,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar producto
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $producto = $vendedor->productos()->findOrFail($id);

        // Verificar que no tenga pedidos activos
        $tienePedidosActivos = $producto->pedidoItems()
            ->whereHas('pedido', function($query) {
                $query->whereNotIn('estado_pedido', ['entregado', 'cancelado']);
            })
            ->exists();

        if ($tienePedidosActivos) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar un producto con pedidos activos'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $datosProducto = $producto->toArray();

            // Eliminar imágenes del storage
            foreach ($producto->imagenes as $imagen) {
                Storage::disk('public')->delete($imagen->imagen);
            }

            // Eliminar el producto
            $producto->delete();

            // Registrar en bitácora
            BitacoraSistema::registrarEliminacion(
                $user->id,
                'Producto',
                $id,
                $datosProducto
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar imágenes al producto
     */
    public function addImages(Request $request, $id)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $producto = $vendedor->productos()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'imagenes' => ['required', 'array', 'min:1', 'max:5'],
            'imagenes.*' => ['image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($request->file('imagenes') as $imagen) {
                $path = $imagen->store('productos/' . $producto->id, 'public');
                $producto->agregarImagen($path);
            }

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                'agregar_imagenes_producto',
                'Producto',
                $producto->id,
                'Imágenes agregadas al producto',
                null,
                ['cantidad_imagenes' => count($request->file('imagenes'))]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Imágenes agregadas exitosamente',
                'producto' => $producto->fresh('imagenes'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar imágenes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar imagen del producto
     */
    public function removeImage(Request $request, $id, $imageId)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $producto = $vendedor->productos()->findOrFail($id);
        $imagen = $producto->imagenes()->findOrFail($imageId);

        // No permitir eliminar la última imagen
        if ($producto->imagenes()->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar la última imagen del producto'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Eliminar del storage
            Storage::disk('public')->delete($imagen->imagen);
            
            // Eliminar de la base de datos
            $imagen->delete();

            // Si era la imagen principal, asignar nueva principal
            if ($imagen->principal) {
                $nuevaPrincipal = $producto->imagenes()->first();
                if ($nuevaPrincipal) {
                    $nuevaPrincipal->marcarComoPrincipal();
                }
            }

            // Registrar en bitácora
            BitacoraSistema::registrarEliminacion(
                $user->id,
                'ProductoImagen',
                $imageId,
                $imagen->toArray()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Imagen eliminada exitosamente',
                'producto' => $producto->fresh('imagenes'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Establecer imagen como principal
     */
    public function setMainImage(Request $request, $id, $imageId)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $producto = $vendedor->productos()->findOrFail($id);
        $imagen = $producto->imagenes()->findOrFail($imageId);

        DB::beginTransaction();
        try {
            $imagen->marcarComoPrincipal();

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                'cambiar_imagen_principal',
                'Producto',
                $producto->id,
                'Imagen establecida como principal',
                null,
                ['imagen_id' => $imageId]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Imagen establecida como principal',
                'producto' => $producto->fresh('imagenes'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al establecer imagen principal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar stock del producto
     */
    public function updateStock(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'stock' => ['required', 'integer', 'min:0'],
            'motivo' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $vendedor = $user->vendedor;

        $producto = $vendedor->productos()->findOrFail($id);

        DB::beginTransaction();
        try {
            $stockAnterior = $producto->stock;
            $producto->stock = $request->stock;
            $producto->save();

            // Registrar en bitácora
            BitacoraSistema::registrarActualizacion(
                $user->id,
                'Producto',
                $producto->id,
                ['stock' => $stockAnterior],
                ['stock' => $request->stock, 'motivo' => $request->motivo]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock actualizado exitosamente',
                'producto' => $producto->fresh(),
                'cambio' => $request->stock - $stockAnterior,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar precio del producto
     */
    public function updatePrice(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'precio' => ['required', 'numeric', 'min:0'],
            'precio_descuento' => ['nullable', 'numeric', 'lt:precio'],
            'motivo' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $vendedor = $user->vendedor;

        $producto = $vendedor->productos()->findOrFail($id);

        DB::beginTransaction();
        try {
            $precioAnterior = $producto->precio;
            $descuentoAnterior = $producto->precio_descuento;

            $producto->precio = $request->precio;
            $producto->precio_descuento = $request->precio_descuento;
            $producto->oferta = !is_null($request->precio_descuento);
            
            // Si cambia el precio, requiere nueva aprobación
            if ($request->precio != $precioAnterior) {
                $producto->aprobado = false;
            }
            
            $producto->save();

            // Registrar en bitácora
            BitacoraSistema::registrarActualizacion(
                $user->id,
                'Producto',
                $producto->id,
                ['precio' => $precioAnterior, 'precio_descuento' => $descuentoAnterior],
                ['precio' => $request->precio, 'precio_descuento' => $request->precio_descuento, 'motivo' => $request->motivo]
            );

            // Notificar a administradores si requiere nueva aprobación
            if (!$producto->aprobado) {
                \App\Models\User::where('tipo_usuario', 'administrador')
                    ->get()
                    ->each(function($admin) use ($producto) {
                        $admin->generarNotificacion(
                            'Precio modificado requiere aprobación',
                            "El precio del producto '{$producto->nombre}' fue modificado",
                            'producto',
                            ['producto_id' => $producto->id]
                        );
                    });
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Precio actualizado exitosamente',
                'producto' => $producto->fresh(),
                'requiere_aprobacion' => !$producto->aprobado,
                'cambio_precio' => $request->precio - $precioAnterior,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar precio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicar producto
     */
    public function duplicate(Request $request, $id)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $productoOriginal = $vendedor->productos()->findOrFail($id);

        // Verificar límite de productos
        if (!$vendedor->puedePublicarProducto()) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes publicar más productos. Límite alcanzado.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Duplicar producto
            $productoNuevo = $productoOriginal->replicate();
            $productoNuevo->nombre = $productoOriginal->nombre . ' (Copia)';
            $productoNuevo->codigo = 'PROD-' . strtoupper(uniqid());
            $productoNuevo->sku = 'SKU-' . time() . '-' . strtoupper(uniqid());
            $productoNuevo->slug = null; // Se generará automáticamente
            $productoNuevo->ventas = 0;
            $productoNuevo->vistas = 0;
            $productoNuevo->aprobado = false;
            $productoNuevo->save();

            // Duplicar imágenes
            foreach ($productoOriginal->imagenes as $imagen) {
                // Copiar archivo de imagen
                $nuevoPath = 'productos/' . $productoNuevo->id . '/' . basename($imagen->imagen);
                Storage::disk('public')->copy($imagen->imagen, $nuevoPath);
                
                $productoNuevo->imagenes()->create([
                    'imagen' => $nuevoPath,
                    'orden' => $imagen->orden,
                    'principal' => $imagen->principal,
                ]);
            }

            // Registrar en bitácora
            BitacoraSistema::registrarCreacion(
                $user->id,
                'Producto',
                $productoNuevo->id,
                ['duplicado_de' => $productoOriginal->id]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto duplicado exitosamente',
                'producto' => $productoNuevo->load(['categoria', 'imagenes']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de ventas del producto
     */
    public function salesStatistics(Request $request, $id)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $producto = $vendedor->productos()->findOrFail($id);

        $periodo = $request->get('periodo', '30dias');
        $estadisticas = $producto->obtenerEstadisticasVentas($periodo);

        // Ventas por mes
        $ventasPorMes = $producto->pedidoItems()
            ->whereHas('pedido', function($query) {
                $query->where('estado_pedido', 'entregado');
            })
            ->selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, SUM(cantidad) as cantidad, SUM(subtotal) as monto')
            ->groupBy('año', 'mes')
            ->orderBy('año', 'desc')
            ->orderBy('mes', 'desc')
            ->limit(12)
            ->get();

        // Clientes frecuentes
        $clientesFrecuentes = $producto->pedidoItems()
            ->whereHas('pedido', function($query) {
                $query->where('estado_pedido', 'entregado');
            })
            ->selectRaw('pedidos.user_id, COUNT(*) as compras, SUM(pedido_items.cantidad) as cantidad_total')
            ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
            ->groupBy('pedidos.user_id')
            ->orderBy('compras', 'desc')
            ->with('pedido.user')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'estadisticas' => $estadisticas,
            'ventas_por_mes' => $ventasPorMes,
            'clientes_frecuentes' => $clientesFrecuentes,
            'total_clientes' => $producto->pedidoItems()
                ->whereHas('pedido', function($query) {
                    $query->where('estado_pedido', 'entregado');
                })
                ->join('pedidos', 'pedido_items.pedido_id', '=', 'pedidos.id')
                ->distinct('pedidos.user_id')
                ->count('pedidos.user_id'),
        ]);
    }

    /**
     * Exportar productos a CSV/Excel
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $productos = $vendedor->productos()
            ->with(['categoria'])
            ->get();

        // Preparar datos para exportación
        $datosExportar = $productos->map(function($producto) {
            return [
                'ID' => $producto->id,
                'Código' => $producto->codigo,
                'SKU' => $producto->sku,
                'Nombre' => $producto->nombre,
                'Categoría' => $producto->categoria->nombre,
                'Precio' => $producto->precio,
                'Precio Descuento' => $producto->precio_descuento,
                'Stock' => $producto->stock,
                'Stock Mínimo' => $producto->stock_minimo,
                'Marca' => $producto->marca,
                'Modelo' => $producto->modelo,
                'Condición' => $producto->condicion,
                'Estado' => $producto->activo ? 'Activo' : 'Inactivo',
                'Aprobado' => $producto->aprobado ? 'Sí' : 'No',
                'Ventas' => $producto->ventas,
                'Vistas' => $producto->vistas,
                'Rating' => $producto->calificacion_promedio,
                'Envío Gratis' => $producto->envio_gratis ? 'Sí' : 'No',
                'Costo Envío' => $producto->costo_envio,
                'Fecha Creación' => $producto->created_at->format('d/m/Y'),
            ];
        });

        return response()->json([
            'success' => true,
            'productos' => $datosExportar,
            'total' => $productos->count(),
            'formato' => 'csv', // También se puede implementar Excel
            'download_url' => route('vendedor.productos.export.csv'), // Ruta para descargar
        ]);
    }
}