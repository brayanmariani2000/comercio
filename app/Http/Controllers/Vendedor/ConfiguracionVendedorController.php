<?php
namespace App\Http\Controllers\Vendedor;

use App\Http\Controllers\Controller;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ConfiguracionVendedorController extends Controller
{
    public function show(Request $request)
    {
        $vendedor = $request->user()->vendedor()->with('user')->first();

        return response()->json([
            'success' => true,
            'vendedor' => $vendedor,
            'logo_url' => $vendedor->logo_url,
            'banner_url' => $vendedor->banner_url,
            'estadisticas' => [
                'calificacion' => $vendedor->calificacion_promedio,
                'ventas' => $vendedor->total_ventas,
                'tiempo_respuesta' => $vendedor->tiempo_respuesta_promedio . ' min',
                'envios_completados' => round($vendedor->porcentaje_envios_completados, 2) . '%'
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $vendedor = $user->vendedor;

        $validator = Validator::make($request->all(), [
            'razon_social' => ['sometimes', 'string', 'max:255'],
            'nombre_comercial' => ['sometimes', 'string', 'max:255'],
            'direccion_fiscal' => ['sometimes', 'string'],
            'telefono' => ['sometimes', 'string'],
            'email' => ['sometimes', 'email'],
            'ciudad' => ['sometimes', 'string'],
            'estado' => ['sometimes', 'string'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'politica_devoluciones' => ['nullable', 'string'],
            'politica_garantias' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $datosAnteriores = $vendedor->toArray();

            // Subir logo
            if ($request->hasFile('logo')) {
                if ($vendedor->logo) {
                    Storage::disk('public')->delete($vendedor->logo);
                }
                $vendedor->logo = $request->file('logo')->store('vendedores/logos', 'public');
            }

            // Subir banner
            if ($request->hasFile('banner')) {
                if ($vendedor->banner) {
                    Storage::disk('public')->delete($vendedor->banner);
                }
                $vendedor->banner = $request->file('banner')->store('vendedores/banners', 'public');
            }

            // Actualizar resto de datos
            $vendedor->update($request->except(['logo', 'banner']));

            // Registrar en bitácora
            \App\Models\BitacoraSistema::registrarActualizacion(
                $user->id,
                'Vendedor',
                $vendedor->id,
                $datosAnteriores,
                $vendedor->toArray()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'vendedor' => $vendedor->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    public function metodosPago(Request $request)
    {
        $vendedor = $request->user()->vendedor;

        if ($request->isMethod('get')) {
            return response()->json([
                'success' => true,
                'metodos_actuales' => $vendedor->metodos_pago ?? [],
                'disponibles' => [
                    'transferencia_bancaria',
                    'pago_movil',
                    'efectivo',
                    'tarjeta_debito',
                    'tarjeta_credito',
                    'paypal',
                    'zelle',
                    'binance'
                ]
            ]);
        }

        // Actualizar
        $validator = Validator::make($request->all(), [
            'metodos' => ['required', 'array'],
            'metodos.*' => ['in:transferencia_bancaria,pago_movil,efectivo,tarjeta_debito,tarjeta_credito,paypal,zelle,binance']
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $vendedor->metodos_pago = $request->metodos;
        $vendedor->save();

        return response()->json([
            'success' => true,
            'message' => 'Métodos de pago actualizados',
            'metodos' => $vendedor->metodos_pago
        ]);
    }

    public function zonasEnvio(Request $request)
    {
        $vendedor = $request->user()->vendedor;

        if ($request->isMethod('get')) {
            return response()->json([
                'success' => true,
                'zonas_actuales' => $vendedor->zonas_envio ?? [],
                'estados_venezuela' => \App\Models\EstadoVenezuela::activos()->get(['id', 'nombre'])
            ]);
        }

        // Actualizar zonas de envío
        $validator = Validator::make($request->all(), [
            'zonas' => ['required', 'array'],
            'zonas.*.estado_id' => ['required', 'exists:estados_venezuela,id'],
            'zonas.*.costo' => ['nullable', 'numeric', 'min:0']
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $zonas = [];
        foreach ($request->zonas as $zona) {
            $estado = \App\Models\EstadoVenezuela::find($zona['estado_id']);
            $zonas[$estado->nombre] = [
                'estado' => $estado->nombre,
                'costo' => $zona['costo'] ?? 0,
                'activo' => true
            ];
        }

        $vendedor->zonas_envio = $zonas;
        $vendedor->save();

        return response()->json([
            'success' => true,
            'message' => 'Zonas de envío actualizadas',
            'zonas' => $vendedor->zonas_envio
        ]);
    }

    public function preferenciasNotificaciones(Request $request)
    {
        $user = $request->user();
        $preferencias = $user->preferencias ?? [];

        if ($request->isMethod('get')) {
            return response()->json([
                'success' => true,
                'preferencias' => array_merge([
                    'pedido_nuevo' => true,
                    'pago_confirmado' => true,
                    'reclamo' => true,
                    'stock_bajo' => true,
                    'producto_sin_aprobar' => true,
                    'email_mensual' => false
                ], $preferencias)
            ]);
        }

        $validator = Validator::make($request->all(), [
            'preferencias' => ['required', 'array'],
            'preferencias.pedido_nuevo' => ['boolean'],
            'preferencias.pago_confirmado' => ['boolean'],
            'preferencias.reclamo' => ['boolean'],
            'preferencias.stock_bajo' => ['boolean'],
            'preferencias.producto_sin_aprobar' => ['boolean'],
            'preferencias.email_mensual' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user->preferencias = array_merge($user->preferencias ?? [], $request->preferencias);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Preferencias actualizadas',
            'preferencias' => $user->preferencias
        ]);
    }
}