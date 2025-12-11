<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendedor;
use App\Models\CodigoVerificacion;
use App\Models\BitacoraSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\EstadoVenezuela;  // <-- AÑADE ESTA LÍNEA
use App\Models\CiudadVenezuela;

class AuthController extends Controller
{
    /**
     * Registrar nuevo usuario
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'cedula' => ['required', 'string', 'unique:users'],
            'telefono' => ['required', 'string'],
            'genero' => ['nullable', 'in:masculino,femenino,otro'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'direccion' => ['nullable', 'string'],
            'estado_id' => ['required', 'exists:estados_venezuela,id'],
            'ciudad_id' => ['required', 'exists:ciudades_venezuela,id'],
            'codigo_postal' => ['nullable', 'string'],
            'tipo_persona' => ['required', 'in:natural,juridica'],
            'rif' => ['nullable', 'required_if:tipo_persona,juridica', 'unique:users'],
            'acepto_terminos' => ['required', 'accepted'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
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
            ]);

            // Crear carrito para el usuario
            $user->carrito()->create();

            // Crear wishlist por defecto
            $user->wishlists()->create([
                'nombre' => 'Mi lista de deseos',
                'predeterminada' => true,
            ]);

            // Generar código de verificación
            $codigo = Str::random(6);
            CodigoVerificacion::create([
                'email' => $user->email,
                'codigo' => $codigo,
                'tipo' => 'registro',
                'expiracion' => now()->addHours(24),
            ]);

            // Registrar en bitácora
            BitacoraSistema::registrarCreacion(
                $user->id,
                'User',
                $user->id,
                $user->toArray()
            );

            event(new Registered($user));

            // Autenticar al usuario
            Auth::login($user);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
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
     * Login de usuario
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->remember)) {
            $user = Auth::user();
            
            // Verificar si está suspendido
            if ($user->suspendido) {
                Auth::logout();
                BitacoraSistema::registrarLogin($user->id, false, 'Cuenta suspendida');
                
                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta ha sido suspendida'
                ], 403);
            }

            // Actualizar último acceso
            $user->update(['ultimo_acceso' => now()]);

            // Registrar login exitoso
            BitacoraSistema::registrarLogin($user->id, true);

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ]);
        }

        // Registrar login fallido
        BitacoraSistema::registrarLogin(null, false, 'Credenciales incorrectas');

        return response()->json([
            'success' => false,
            'message' => 'Credenciales incorrectas'
        ], 401);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        
        if ($user) {
            // Registrar logout
            BitacoraSistema::registrarLogout($user->id);
            
            // Revocar token actual
            $request->user()->currentAccessToken()->delete();
        }

        Auth::logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
    }

    /**
     * Obtener perfil del usuario autenticado
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load(['estado', 'ciudad', 'direccionesEnvio', 'vendedor']);

        return response()->json([
            'success' => true,
            'user' => $user,
            'estadisticas' => [
                'total_compras' => $user->total_compras,
                'monto_total_compras' => $user->monto_total_compras,
                'rating_promedio' => $user->rating_promedio,
                'direcciones' => $user->direccionesEnvio()->count(),
                'wishlists' => $user->wishlists()->count(),
                'notificaciones_no_leidas' => $user->notificacionesNoLeidas()->count(),
            ]
        ]);
    }

    /**
     * Actualizar perfil
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'telefono' => ['sometimes', 'string'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'genero' => ['nullable', 'in:masculino,femenino,otro'],
            'direccion' => ['nullable', 'string'],
            'estado_id' => ['sometimes', 'exists:estados_venezuela,id'],
            'ciudad_id' => ['sometimes', 'exists:ciudades_venezuela,id'],
            'codigo_postal' => ['nullable', 'string'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $datosAnteriores = $user->toArray();
            
            $data = $request->only([
                'name', 'telefono', 'fecha_nacimiento', 'genero',
                'direccion', 'estado_id', 'ciudad_id', 'codigo_postal'
            ]);

            // Subir avatar si existe
            if ($request->hasFile('avatar')) {
                $path = $request->file('avatar')->store('avatars', 'public');
                $data['avatar'] = $path;
            }

            $user->update($data);
            $datosNuevos = $user->toArray();

            // Registrar en bitácora
            BitacoraSistema::registrarActualizacion(
                $user->id,
                'User',
                $user->id,
                $datosAnteriores,
                $datosNuevos
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'user' => $user->fresh(['estado', 'ciudad']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña actual es incorrecta'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $user->password = Hash::make($request->new_password);
            $user->save();

            // Registrar en bitácora
            BitacoraSistema::registrarActualizacion(
                $user->id,
                'User',
                $user->id,
                ['password' => '***'],
                ['password' => '***']
            );

            // Enviar notificación
            $user->generarNotificacion(
                'Contraseña cambiada',
                'Tu contraseña ha sido cambiada exitosamente',
                'seguridad',
                ['ip' => $request->ip(), 'fecha' => now()]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña cambiada exitosamente',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar contraseña: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convertirse en vendedor
     */
    public function registerAsVendor(Request $request)
    {
        $user = $request->user();

        // Verificar si ya es vendedor
        if ($user->esVendedor()) {
            return response()->json([
                'success' => false,
                'message' => 'Ya eres vendedor'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'razon_social' => ['required', 'string', 'max:255'],
            'nombre_comercial' => ['required', 'string', 'max:255'],
            'rif' => ['required', 'string', 'unique:vendedores'],
            'direccion_fiscal' => ['required', 'string'],
            'telefono' => ['required', 'string'],
            'email' => ['required', 'email'],
            'ciudad' => ['required', 'string'],
            'estado' => ['required', 'string'],
            'tipo_vendedor' => ['required', 'in:individual,empresa'],
            'descripcion' => ['nullable', 'string'],
            'metodos_pago' => ['required', 'array'],
            'metodos_pago.*' => ['in:transferencia_bancaria,pago_movil,efectivo,tarjeta_debito,tarjeta_credito,paypal,zelle,binance'],
            'zonas_envio' => ['required', 'array'],
            'comprobante_rif' => ['required', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
            'comprobante_domicilio' => ['required', 'file', 'mimes:pdf,jpg,png', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Actualizar tipo de usuario
            $user->tipo_usuario = 'vendedor';
            $user->save();

            // Crear vendedor
            $vendedor = Vendedor::create([
                'user_id' => $user->id,
                'razon_social' => $request->razon_social,
                'nombre_comercial' => $request->nombre_comercial,
                'rif' => $request->rif,
                'direccion_fiscal' => $request->direccion_fiscal,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'ciudad' => $request->ciudad,
                'estado' => $request->estado,
                'tipo_vendedor' => $request->tipo_vendedor,
                'descripcion' => $request->descripcion,
                'metodos_pago' => $request->metodos_pago,
                'zonas_envio' => $request->zonas_envio,
                'verificado' => false,
                'activo' => true,
                'membresia' => 'basico',
                'fecha_vencimiento_membresia' => now()->addMonth(),
            ]);

            // Guardar documentos
            if ($request->hasFile('comprobante_rif')) {
                $rifPath = $request->file('comprobante_rif')->store('documentos/vendedores/' . $vendedor->id, 'public');
                // Guardar path en base de datos si es necesario
            }

            if ($request->hasFile('comprobante_domicilio')) {
                $domicilioPath = $request->file('comprobante_domicilio')->store('documentos/vendedores/' . $vendedor->id, 'public');
                // Guardar path en base de datos si es necesario
            }

            // Registrar en bitácora
            BitacoraSistema::registrarCreacion(
                $user->id,
                'Vendedor',
                $vendedor->id,
                $vendedor->toArray()
            );

            // Notificar a administradores
            User::where('tipo_usuario', 'administrador')
                ->get()
                ->each(function($admin) use ($vendedor) {
                    $admin->generarNotificacion(
                        'Nuevo vendedor registrado',
                        "El usuario {$vendedor->nombre_comercial} se ha registrado como vendedor",
                        'vendedor',
                        ['vendedor_id' => $vendedor->id]
                    );
                });

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Solicitud de registro como vendedor enviada. Espera la verificación.',
                'vendedor' => $vendedor,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar vendedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar código de verificación
     */
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'codigo' => ['required', 'string', 'size:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $codigo = CodigoVerificacion::where('email', $request->email)
            ->where('codigo', $request->codigo)
            ->where('verificado', false)
            ->where('expiracion', '>', now())
            ->first();

        if (!$codigo) {
            return response()->json([
                'success' => false,
                'message' => 'Código inválido o expirado'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $user = User::where('email', $request->email)->firstOrFail();
            $user->email_verified_at = now();
            $user->verificado = true;
            $user->save();

            $codigo->verificado = true;
            $codigo->fecha_verificacion = now();
            $codigo->save();

            // Registrar en bitácora
            BitacoraSistema::registrar(
                $user->id,
                'email_verificado',
                'User',
                $user->id,
                'Email verificado exitosamente'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Email verificado exitosamente',
                'user' => $user,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reenviar código de verificación
     */
    public function resendVerificationCode(Request $request)
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
                'message' => 'El email ya está verificado'
            ], 400);
        }

        // Invalidar códigos anteriores
        CodigoVerificacion::where('email', $user->email)
            ->where('tipo', 'registro')
            ->where('verificado', false)
            ->update(['expiracion' => now()->subDay()]);

        // Generar nuevo código
        $codigo = Str::random(6);
        CodigoVerificacion::create([
            'email' => $user->email,
            'codigo' => $codigo,
            'tipo' => 'registro',
            'expiracion' => now()->addHours(24),
        ]);

        // Aquí enviar email con el nuevo código (implementar con Mailgun, SendGrid, etc.)

        return response()->json([
            'success' => true,
            'message' => 'Código de verificación reenviado',
        ]);
    }

    /**
     * Cerrar sesión de todos los dispositivos
     */
    public function logoutAllDevices(Request $request)
    {
        $user = $request->user();
        
        try {
            // Revocar todos los tokens
            $user->tokens()->delete();

            // Registrar en bitácora
            BitacoraSistema::registrarLogout($user->id);

            Auth::logout();

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada en todos los dispositivos',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesiones: ' . $e->getMessage()
            ], 500);
        }
    }
       
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Mostrar formulario de registro
     */
public function showRegisterForm()
{
    // Temporalmente sin estados/ciudades
    $estados = collect([]);
    $ciudades = collect([]);
    
    return view('auth.register', compact('estados', 'ciudades'));
}
    /**
     * Mostrar formulario de verificación
     */
    public function showVerificationForm(Request $request)
    {
        $email = $request->query('email');
        
        if (!$email) {
            return redirect()->route('register')->with('error', 'Email requerido para verificación');
        }
        
        return view('auth.verify', compact('email'));
    }

    /**
     * Mostrar formulario de recuperación de contraseña
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Mostrar formulario de restablecimiento de contraseña
     */
    public function showResetPasswordForm(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->token,
            'email' => $request->email
        ]);
    }
}

