<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionRol;
use App\Models\Role;
use App\Models\Grupo;

class AsignacionRolController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga dos campos y ambos sean enteros
     * $asignacion hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
    public function asignarRolGrupo(Request $request)
    {
        $validateData = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'int',
            'grupo_id' => 'required|int'
        ]);
        $grupo = Grupo::find($validateData['grupo_id']);
        $arrayRoles = $validateData['roles'];
        if (isset($grupo)) {
            if ($grupo->estado == 1) {
                foreach ($arrayRoles as $rol) {
                    $asignacion = AsignacionRol::create([
                        "grupo_id" => $grupo->id,
                        "rol_id" => $rol
                    ]);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Rol asignado a grupo correctamente'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Datos no disponibles'
                ], 401);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Datos no encontrados'
            ], 404);
        }
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga dos campos y ambos sean enteros
     * $asignacion hace uso de ELOQUENT de laravel con el metodo where y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de eliminar en la DB con el metodo delete
     */
    public function eliminarAsignacion(Request $request)
    {
        $validateData = $request->validate([
            'rol_id' => 'required|int',
            'grupo_id' => 'required|int'
        ]);
        $matchThese = ['rol_id' => $validateData['rol_id'], 'grupo_id' => $validateData['grupo_id']];
        $asignacion = AsignacionRol::where($matchThese)
            ->first();
        if (isset($asignacion)) {
            AsignacionRol::where($matchThese)
                ->delete();
            return response()->json([
                'status' => true,
                'message' => 'Asignacion de grupo y Rol eliminada'
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Datos no encontrados'
            ], 404);
        }
    }

    public function modificarGruposRoles(Request $request)
    {
        $validateData = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'int',
            'grupo_id' => 'required|int'
        ]);
        $grupo = Grupo::find($validateData['grupo_id']);
        if (isset($grupo)) {
            AsignacionRol::where('grupo_id', $validateData['grupo_id'])->delete();
            $this->asignarRolGrupo($request);
            return response()->json([
                'status' => true,
                'message' => 'Grupo modificado correctamente'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Dato no encontrado'
            ], 404);
        }
    }

    public function obtenerGruposRoles()
    {
        try {
            $asginaciones = AsignacionRol::selectRaw('grupo.id,grupo.nombre, GROUP_CONCAT(rol.nombre) AS roles')
                ->join('rol', 'asignacion_rol.rol_id', 'rol.id')
                ->join('grupo', 'asignacion_rol.grupo_id', 'grupo.id')
                ->where('rol.estado', 1)
                ->where('grupo.estado', 1)
                ->groupBy('asignacion_rol.grupo_id')
                ->get();
            foreach ($asginaciones as $asginacion) {
                $asginacion->roles = explode(",", $asginacion->roles);
            }
            return response()->json($asginaciones);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerGrupoSinRol()
    {
        try {
            $asginaciones = Grupo::select('grupo.id','grupo.nombre')
                ->leftJoin('asignacion_rol', 'asignacion_rol.grupo_id', 'grupo.id')
                ->whereNull('asignacion_rol.grupo_id')
                ->where('grupo.estado', 1)
                ->get();
            return response()->json($asginaciones,200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}