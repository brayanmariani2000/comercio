<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CodigoVerificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    /**
     * Mostrar formulario de registro (opcional si usas API)
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Registrar un nuevo comprador en el sistema
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
            'cedula' => ['required', 'string', 'unique:users', 'regex:/^[VEJPGvejpg][0-9]{5,9}$/'],
            'telefono' => ['required', 'string', 'regex:/^0[0-9]{10}$/'],
            'fecha_nacimiento' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'genero' => ['nullable', 'in:masculino,femenino,otro'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'estado_id' => ['required', 'exists:estados_venezuela,id'],
            'ciudad_id' => ['required', 'exists:ciudades_venezuela,id'],
            'codigo_postal' => ['nullable', 'string', 'max:10'],
            'tipo_persona' => ['required', 'in:natural,juridica'],
            'rif' => ['nullable', 'required_if:tipo_persona,juridica', 'unique:users', 'regex:/^[JGVEjgve][0-9]{7,9}[0-9]$/'],
            'acepto_terminos' => ['accepted'],
            'recibir_boletines' => ['boolean'],
        ], [
            'cedula.regex' => 'La cédula debe comenzar con V, E, J, P o G seguido de 5 a 9 dígitos.',
            'rif.regex' => 'El RIF debe tener formato válido venezolano (Ej: J123456789).',
            'telefono.regex' => 'El teléfono debe comenzar con 0 y tener 11 dígitos (Ej: 04121234567).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'cedula' => $request->cedula,
                'rif' => $request->rif,
                'tipo_persona' => $request->tipo_persona,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'direccion' => $request->direccion,
                'estado_id' => $request->estado_id,
                'ciudad_id' => $request->ciudad_id,
                'codigo_postal' => $request->codigo_postal,
                'tipo_usuario' => 'comprador',
                'verificado' => false,
                'suspendido' => false,
                'preferencias' => [
                    'email_boletines' => (bool) ($request->recibir_boletines ?? false),
                    'email_promociones' => true,
                    'email_pedidos' => true,
                    'email_reclamos' => true,
                    'notificaciones_push' => true,
                    'notificaciones_email' => true,
                    'moneda_preferida' => 'Bs.',
                ],
            ]);

            // Crear carrito vacío
            $user->carrito()->create();

            // Crear wishlist predeterminada
            $user->wishlists()->create([
                'nombre' => 'Mi lista de deseos',
                'predeterminada' => true,
            ]);

            // Generar código de verificación de 6 dígitos
            $codigo = Str::random(6);
            CodigoVerificacion::create([
                'email' => $user->email,
                'codigo' => $codigo,
                'tipo' => 'registro',
                'expiracion' => now()->addHours(24),
            ]);

            // Aquí deberías enviar el código por correo (usar Mail::send, Mailgun, etc.)
            // Ejemplo: Mail::to($user->email)->send(new VerificationCodeMail($codigo));

            // Disparar evento de registro (para enviar email automático si usas Laravel Breeze/UI)
            event(new Registered($user));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registro exitoso. Verifica tu correo electrónico.',
                'user' => $user->only(['id', 'name', 'email', 'tipo_usuario']),
                'requires_verification' => true,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reenviar código de verificación
     */
    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'El correo ya está verificado.'
            ], 400);
        }

        // Invalidar códigos anteriores
        CodigoVerificacion::where('email', $user->email)
            ->where('tipo', 'registro')
            ->where('verificado', false)
            ->update(['expiracion' => now()->subMinute()]);

        // Generar nuevo código
        $codigo = Str::random(6);
        CodigoVerificacion::create([
            'email' => $user->email,
            'codigo' => $codigo,
            'tipo' => 'registro',
            'expiracion' => now()->addHours(24),
        ]);

        // Aquí enviarías el nuevo código por email

        return response()->json([
            'success' => true,
            'message' => 'Código de verificación reenviado a tu correo.',
        ]);
    }
}