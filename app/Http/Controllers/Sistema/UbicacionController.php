<?php
namespace App\Http\Controllers\Sistema;

use App\Http\Controllers\Controller;
use App\Models\EstadoVenezuela;
use App\Models\MunicipioVenezuela;
use App\Models\CiudadVenezuela;

class UbicacionController extends Controller
{
    public function estados()
    {
        $estados = EstadoVenezuela::activos()->get(['id', 'nombre']);
        return response()->json(['estados' => $estados]);
    }

    public function municipios($estadoId)
    {
        $municipios = MunicipioVenezuela::where('estado_id', $estadoId)->get(['id', 'nombre']);
        return response()->json(['municipios' => $municipios]);
    }

    public function ciudades($municipioId)
    {
        $ciudades = CiudadVenezuela::where('municipio_id', $municipioId)->get(['id', 'nombre', 'codigo_postal']);
        return response()->json(['ciudades' => $ciudades]);
    }
}