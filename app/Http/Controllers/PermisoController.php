<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permiso;

class PermisoController extends Controller
{
    /**
     * Funcion que obtiene la lista de permisos siempre que esten activos
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerPermisos()
    {
        $permisos = Permiso::select('id', 'alias')
            ->where('estado', 1)
            ->get();
        return response()->json($permisos);
    }
}