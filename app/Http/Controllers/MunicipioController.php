<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Municipio;

class MunicipioController extends Controller
{
    /**
     * Funcion que devuleve la tabla municipios, mostrando el nombre del departamento
     * al que pertenece  cada municipio
     * Summary of obtenerMunicipios
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerMunicipios()
    {
        $municipios = Municipio::select('municipio.id', 'municipio.nombre', 'departamento.nombre AS departamento')
            ->join('departamento', 'municipio.departamento_id', 'departamento.id')
            ->get();
        return response()->json($municipios);
    }
}