<?php
namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\Cupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CuponController extends Controller
{
public function validar(Request $request)
{
    $validator = Validator::make($request->all(), [
        'codigo' => ['required', 'string'],
        'subtotal' => ['required', 'numeric', 'min:0'],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $userId = auth()->check() ? auth()->id() : null;
    $cupon = Cupon::validar($request->codigo, $userId, $request->subtotal);

    if (!$cupon) {
        return response()->json([
            'success' => false,
            'message' => 'Cupón inválido, expirado o no aplicable'
        ], 400);
    }

    return response()->json([
        'success' => true,
        'cupon' => [
            'codigo' => $cupon->codigo,
            'nombre' => $cupon->nombre,
            'tipo' => $cupon->tipo,
            'valor' => $cupon->valor,
            'descuento' => $cupon->calcularDescuento($request->subtotal),
            'minimo_compra' => $cupon->minimo_compra,
            'descripcion' => $cupon->descripcion,
            'fecha_fin' => $cupon->fecha_fin->format('d/m/Y'),
            'aplicable_a' => $this->getAplicabilidadCupon($cupon),
        ]
    ]);
}

/**
 * Obtener aplicabilidad del cupón
 */
private function getAplicabilidadCupon($cupon)
{
    $aplicabilidad = [];
    if ($cupon->categorias_aplicables) {
        $categorias = Categoria::whereIn('id', $cupon->categorias_aplicables)->pluck('nombre');
        $aplicabilidad[] = 'Categorías: ' . implode(', ', $categorias->toArray());
    }
    if ($cupon->productos_aplicables) {
        $aplicabilidad[] = 'Productos específicos (' . count($cupon->productos_aplicables) . ')';
    }
    if ($cupon->usuarios_aplicables) {
        $aplicabilidad[] = 'Usuarios seleccionados';
    }
    if ($cupon->solo_primer_compra) {
        $aplicabilidad[] = 'Solo primer compra';
    }
    if ($cupon->solo_usuarios_nuevos) {
        $aplicabilidad[] = 'Solo usuarios nuevos (menos de 30 días)';
    }
    if (empty($aplicabilidad)) {
        $aplicabilidad[] = 'Todos los productos';
    }
    return $aplicabilidad;
}

/**
 * Aplicar cupón en el carrito (API para frontend)
 */
public function aplicarEnCarrito(Request $request)
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

    if (!$carrito || $carrito->items()->count() === 0) {
        return response()->json([
            'success' => false,
            'message' => 'El carrito está vacío'
        ], 400);
    }

    try {
        $resultado = $carrito->aplicarCupon($request->codigo);
        return response()->json([
            'success' => true,
            'message' => 'Cupón aplicado exitosamente',
            'descuento_aplicado' => $resultado['descuento'],
            'subtotal_con_descuento' => $resultado['subtotal_con_descuento'],
            'cupon' => $resultado['cupon']->only(['codigo', 'nombre', 'tipo', 'valor'])
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}

/**
 * Generar cupón promocional (solo admin)
 */
public function generarPromocional(Request $request)
{
    $validator = Validator::make($request->all(), [
        'nombre' => ['required', 'string', 'max:255'],
        'descripcion' => ['nullable', 'string'],
        'tipo' => ['required', 'in:porcentaje,monto_fijo,envio_gratis'],
        'valor' => ['required', 'numeric'],
        'fecha_inicio' => ['required', 'date'],
        'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        'usos_maximos' => ['nullable', 'integer', 'min:1'],
        'minimo_compra' => ['nullable', 'numeric', 'min:0'],
        'solo_primer_compra' => ['boolean'],
        'excluir_productos_oferta' => ['boolean'],
        'publico' => ['boolean', 'required'],
        'usuarios_especificos' => ['nullable', 'array'],
        'usuarios_especificos.*' => ['exists:users,id'],
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    DB::beginTransaction();
    try {
        // Generar código único
        do {
            $codigo = strtoupper(Str::random(8));
        } while (Cupon::where('codigo', $codigo)->exists());

        $cuponData = $request->only([
            'nombre', 'descripcion', 'tipo', 'valor', 'fecha_inicio', 'fecha_fin',
            'usos_maximos', 'minimo_compra', 'solo_primer_compra', 'excluir_productos_oferta'
        ]);
        $cuponData['codigo'] = $codigo;
        $cuponData['activo'] = true;
        $cuponData['usuarios_aplicables'] = $request->publico ? null : $request->usuarios_especificos;

        $cupon = Cupon::create($cuponData);

        // Notificar a usuarios si es privado
        if (!$request->publico && $request->usuarios_especificos) {
            $usuarios = \App\Models\User::whereIn('id', $request->usuarios_especificos)->get();
            foreach ($usuarios as $usuario) {
                $usuario->generarNotificacion(
                    '¡Tienes un cupón exclusivo!',
                    "¡Hola {$usuario->name}! Tienes un cupón especial: {$codigo}. ¡Úsalo antes que expire!",
                    'promocion',
                    ['cupon_codigo' => $codigo]
                );
            }
        }

        DB::commit();
        return response()->json([
            'success' => true,
            'message' => 'Cupón generado exitosamente',
            'cupon' => $cupon,
            'codigo' => $codigo,
            'enlace_compartir' => route('cupon.compartir', $codigo),
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Error al generar cupón: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Obtener cupones activos para el usuario (dashboard)
 */
public function getMisCupones(Request $request)
{
    $user = $request->user();
    $cupones = Cupon::where(function($q) use ($user) {
        $q->where('usuarios_aplicables', 'LIKE', "%{$user->id}%")
          ->orWhereNull('usuarios_aplicables');
    })
    ->activos()
    ->with('usos')
    ->get();

    $usados = $user->cuponesUsados()->with('cupon')->get();
    $activos = $cupones->filter(function($cupon) use ($usados) {
        return !$usados->contains('cupon_id', $cupon->id) && $cupon->esValidoParaUsuario($user);
    });

    return response()->json([
        'success' => true,
        'activos' => $activos,
        'usados' => $usados,
        'total_activos' => $activos->count(),
        'total_usados' => $usados->count(),
    ]);
}

/**
 * Compartir cupón (página pública)
 */
public function compartir($codigo)
{
    $cupon = Cupon::activos()
        ->where('codigo', $codigo)
        ->first();

    if (!$cupon) {
        return response()->json([
            'success' => false,
            'message' => 'Cupón no encontrado o expirado'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'cupon' => [
            'codigo' => $cupon->codigo,
            'nombre' => $cupon->nombre,
            'descripcion' => $cupon->descripcion,
            'tipo' => $cupon->tipo,
            'valor' => $cupon->valor,
            'fecha_fin' => $cupon->fecha_fin->format('d/m/Y'),
            'qr' => 'data:image/png;base64,' . base64_encode(\QrCode::format('png')->size(200)->generate($codigo)),
        ]
    ]);
}
}