<?php
namespace App\Http\Controllers\Comprador;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request)
    {
        $notificaciones = $request->user()->notificaciones()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json(['success' => true, 'notificaciones' => $notificaciones]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notificacion = $request->user()->notificaciones()->findOrFail($id);
        $notificacion->marcarComoLeida();
        return response()->json(['success' => true, 'notificacion' => $notificacion]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->notificacionesNoLeidas()->update(['leida' => true, 'leida_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Todas las notificaciones marcadas como leídas']);
    }
}