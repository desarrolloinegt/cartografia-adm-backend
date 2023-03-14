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
        try{
            $permisos = Permiso::select('id', 'alias')
            ->where('estado', 1)
            ->get();
            return response()->json($permisos,200);
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }

    public function obtenerPermisosSistema(){
        try{
            $permisos = Permiso::select('id', 'alias')
            ->where('estado', 1)
            ->where('permiso_sistema',1)
            ->get();
            return response()->json($permisos,200);
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerPermisosProyecto(){
        try{
            $permisos = Permiso::select('id', 'alias')
            ->where('estado', 1)
            ->where('permiso_sistema',0)
            ->get();
            return response()->json($permisos,200);
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}