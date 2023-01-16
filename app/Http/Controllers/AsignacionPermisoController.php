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
     */
    public function eliminarAsignacion(Request $request){
        $validateData=$request->validate([
            'rol_id'=>'required|int',
            'permiso_id'=>'required|int'
        ]);
        $matchThese = ['rol_id' =>$validateData['rol_id'], 'permiso_id' => $validateData['permiso_id']];
        $asignacion=AsignacionPermiso::where($matchThese)
            ->first();
        if(isset($asignacion)){
            AsignacionPermiso::where($matchThese)
            ->delete();
            return response()->json([
                'status'=>true,
                'message'=>'Asignacion de rol y permiso eliminada'
            ],200);
        } else{
            return response()->json([
                'status'=>true,
                'message'=>'Datos no encontrados'
            ],404);
        }    
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga dos campos y ambos sean enteros
     * $asignacion hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
    */

    public function asignacionMasiva(Request $request){
        try{
            $erros=[];
            $validateData=$request->validate([
                'rol_id'=>'required|int',
                'permisos'=>'array|required',
                'permisos.*'=>'int'
            ]);
            $rol=Role::find($validateData['rol_id']);
            $arrayPermisos=$validateData['permisos'];
            if(isset($rol)){
                foreach($arrayPermisos as $permiso){
                    $asignacion=AsignacionPermiso::create([
                        "rol_id"=>$rol->id,
                        "permiso_id"=>$permiso
                    ]);             
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Rol no Econtrado"
                ], 404);
            }   
            return response()->json([
                'status'=>true,
                'message'=>'Permiso asignado correctamente'
            ],200);  
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function obtenerRolPermiso(){
        try{
            $asignacionArray=[];
            $permisos=[];
            $asginaciones= AsignacionPermiso::selectRaw('rol.id AS rol_id,rol.nombre, GROUP_CONCAT(permiso.alias) AS permisos')
                ->join('rol','asignacion_permisos.rol_id','rol.id')
                ->join('permiso','asignacion_permisos.permiso_id','permiso.id')
                ->where('permiso.estado',1)
                ->where('rol.estado',1)
                ->groupBy('asignacion_permisos.rol_id')
                ->get();
            foreach ($asginaciones as $asginacion) {
                $asginacion->permisos =explode(",", $asginacion->permisos);
            }
            return response()->json($asginaciones);
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
