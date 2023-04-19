<?php

namespace App\Http\Controllers\Permiso;

use App\Http\Controllers\Controller;
use App\Models\Permiso;

class PermisoController extends Controller
{
    /**
     * Funcion que obtiene la lista de permisos siempre que esten activos
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissions()
    {
        try {
            $permisos = Permiso::select('id', 'alias')
                ->where('estado', 1)
                ->get();
            return response()->json($permisos, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Function para obtener los permisos de sistema
     *  @return \Illuminate\Http\JsonResponse
     */
    public function getSytemPermissions()
    {
        try {
            $permisos = Permiso::select('id', 'alias')
                ->where('estado', 1)
                ->where('permiso_sistema', 1)
                ->get();
            return response()->json($permisos, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Function para obtener los permisos de proyectos
     *  @return \Illuminate\Http\JsonResponse
     */
    public function getProjectPermissions()
    {
        try {
            $permisos = Permiso::select('id', 'alias')
                ->where('estado', 1)
                ->where('permiso_sistema', 0)
                ->get();
            return response()->json($permisos, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}