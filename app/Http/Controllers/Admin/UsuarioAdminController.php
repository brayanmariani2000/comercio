<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsuarioAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::orderBy('created_at', 'desc');
        if ($request->filled('tipo_usuario')) {
            $query->where('tipo_usuario', $request->tipo_usuario);
        }
        if ($request->filled('verificado')) {
            $query->where('verificado', $request->verificado);
        }
        if ($request->filled('suspendido')) {
            $query->where('suspendido', $request->suspendido);
        }
        if ($request->filled('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%")
                 ->orWhere('email', 'LIKE', "%{$request->search}%")
                 ->orWhere('cedula', 'LIKE', "%{$request->search}%");
        }

        $usuarios = $query->paginate(25);

        return response()->json([
            'success' => true,
            'usuarios' => $usuarios,
            'estadisticas' => [
                'total' => User::count(),
                'compradores' => User::where('tipo_usuario', 'comprador')->count(),
                'vendedores' => User::where('tipo_usuario', 'vendedor')->count(),
                'suspendidos' => User::where('suspendido', true)->count(),
                'no_verificados' => User::where('verificado', false)->count(),
            ]
        ]);
    }

    public function show($id)
    {
        $usuario = User::with([
            'estado',
            'ciudad',
            'direccionesEnvio',
            'vendedor',
            'pedidos',
            'reclamos'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'usuario' => $usuario
        ]);
    }

    public function actualizar(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        $usuario->update($request->only([
            'name',
            'email',
            'cedula',
            'telefono',
            'tipo_usuario',
            'verificado',
            'suspendido',
            'estado_id',
            'ciudad_id'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado',
            'usuario' => $usuario->fresh()
        ]);
    }

    public function cambiarPassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario = User::findOrFail($id);
        $usuario->password = Hash::make($request->password);
        $usuario->save();

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada'
        ]);
    }

    public function suspender($id, Request $request)
    {
        $usuario = User::findOrFail($id);
        $usuario->suspendido = true;
        $usuario->save();

        // Revocar sesiones
        $usuario->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario suspendido'
        ]);
    }

    public function activar($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->suspendido = false;
        $usuario->save();

        return response()->json([
            'success' => true,
            'message' => 'Usuario activado'
        ]);
    }

    public function eliminar($id)
    {
        $usuario = User::findOrFail($id);

        // No eliminar si tiene pedidos o es vendedor activo
        if ($usuario->pedidos()->count() > 0 || ($usuario->vendedor && $usuario->vendedor->activo)) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: el usuario tiene actividad en la plataforma'
            ], 400);
        }

        $usuario->delete();
        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado permanentemente'
        ]);
    }
}