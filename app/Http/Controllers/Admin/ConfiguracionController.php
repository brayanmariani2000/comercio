<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionTienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    public function index()
    {
        $config = ConfiguracionTienda::first();
        return response()->json([
            'success' => true,
            'configuracion' => $config
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_tienda' => 'required|string',
            'rif' => 'required|string|unique:configuracion_tienda,rif,' . ($request->id ?? 0),
            'direccion' => 'required|string',
            'telefono' => 'required|string',
            'email' => 'required|email',
            'moneda' => 'required|string',
            'simbolo_moneda' => 'required|string',
            'iva' => 'required|numeric|min:0',
            'ciudad' => 'required|string',
            'estado' => 'required|string',
            'terminos_condiciones' => 'nullable|string',
            'politica_envios' => 'nullable|string',
            'politica_devoluciones' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $config = ConfiguracionTienda::firstOrCreate([]);
        $data = $request->except(['logo', 'banner']);

        if ($request->hasFile('logo')) {
            if ($config->logo) Storage::disk('public')->delete($config->logo);
            $data['logo'] = $request->file('logo')->store('configuracion', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($config->banner) Storage::disk('public')->delete($config->banner);
            $data['banner'] = $request->file('banner')->store('configuracion', 'public');
        }

        $config->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada',
            'configuracion' => $config
        ]);
    }
}