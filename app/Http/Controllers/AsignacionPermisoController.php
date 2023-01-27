<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionPermiso;
use App\Models\Role;
use App\Models\Permiso;

class AsignacionPermisoController extends Controller
{

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga dos campos y ambos sean enteros
     * $asignacion hace uso de ELOQUENT de laravel con el metodo where y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de eliminar en la DB con el metodo delete
     * @return \Illuminate\Http\JsonResponse
     */
    public function eliminarAsignacion(Request $request)
    {
        $validateData = $request->validate([
            'rol_id' => 'required|int',
            'permiso_id' => 'required|int'
        ]);
        $matchThese = ['rol_id' => $validateData['rol_id'], 'permiso_id' => $validateData['permiso_id']];
        $asignacion = AsignacionPermiso::where($matchThese)
            ->first();
        if (isset($asignacion)) {
            AsignacionPermiso::where($matchThese)
                ->delete();
            return response()->json([
                'status' => true,
                'message' => 'Asignacion de rol y permiso eliminada'
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Datos no encontrados'
            ], 404);
        }
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campo y un array de numeros\
     * $rol busca que el rol si exista
     * Foreach para recorrer el array de permisos y pasarlos al metodo create
     * $asignacion hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     * @return \Illuminate\Http\JsonResponse
     */

    public function asignacionMasiva(Request $request)
    {
        try {
            $validateData = $request->validate([
                'id' => 'required|int',
                'permisos' => 'array|required',
                'permisos.*' => 'int'
            ]);
            $rol = Role::find($validateData['id']);
            $arrayPermisos = $validateData['permisos'];
            if (isset($rol)) {
                AsignacionPermiso::where('rol_id',$rol->id)->delete();
                foreach ($arrayPermisos as $permiso) {
                    $asignacion = AsignacionPermiso::create([
                        "rol_id" => $rol->id,
                        "permiso_id" => $permiso
                    ]);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Permisos asignados correctamente'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Rol no Econtrado"
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     *Funcion para obtener los roles con los permisos asignados, siempre que el permiso este activo
     *y el rol este activo, agrupados por el rol 
     *@return \Illuminate\Http\JsonResponse 
     */
    public function obtenerRolPermiso($id)
    {
        try {
            $asginaciones = AsignacionPermiso::select('permiso.alias')
                ->join('rol', 'asignacion_permisos.rol_id', 'rol.id')
                ->join('permiso', 'asignacion_permisos.permiso_id', 'permiso.id')
                ->where('permiso.estado', 1)
                ->where('rol.estado', 1)
                ->where('rol.id',$id)
                ->get();
            return response()->json($asginaciones);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}