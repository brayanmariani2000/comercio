<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\CodigoVerificacion;

class AuthApiController extends ApiController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'cedula' => 'required|string|unique:users',
            'telefono' => 'required|string',
            'estado_id' => 'required|exists:estados_venezuela,id',
            'ciudad_id' => 'required|exists:ciudades_venezuela,id',
            'acepto_terminos' => 'accepted',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'cedula' => $request->cedula,
                'telefono' => $request->telefono,
                'estado_id' => $request->estado_id,
                'ciudad_id' => $request->ciudad_id,
                'tipo_usuario' => 'comprador',
                'verificado' => false,
            ]);

            // Crear carrito y wishlist
            $user->carrito()->create();
            $user->wishlists()->create(['nombre' => 'Mi lista de deseos', 'predeterminada' => true]);

            // Código de verificación
            $codigo = Str::random(6);
            CodigoVerificacion::create([
                'email' => $user->email,
                'codigo' => $codigo,
                'tipo' => 'registro',
                'expiracion' => now()->addHours(24),
            ]);

            return $this->success([
                'user' => $user->only('id', 'name', 'email'),
                'requires_verification' => true,
            ], 'Usuario registrado. Verifica tu email.', 201);

        } catch (\Exception $e) {
            return $this->error('Error al registrar usuario: ' . $e->getMessage(), 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->unauthorized('Credenciales incorrectas');
        }

        $user = Auth::user();
        if ($user->suspendido) {
            return $this->unauthorized('Tu cuenta ha sido suspendida');
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return $this->success([
            'user' => $user->only('id', 'name', 'email', 'tipo_usuario'),
            'token' => $token,
        ], 'Login exitoso');
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load(['estado', 'ciudad', 'direccionesEnvio']);

        return $this->success([
            'user' => $user,
            'estadisticas' => [
                'total_compras' => $user->total_compras,
                'notificaciones_no_leidas' => $user->notificacionesNoLeidas()->count(),
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Sesión cerrada');
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete(); // Opcional: revocar anteriores
        $newToken = $user->createToken('mobile-app-refresh')->plainTextToken;

        return $this->success([
            'token' => $newToken
        ], 'Token renovado');
    }
}