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
    public function asignarRolGrupo(Request $request){
        $validateData=$request->validate([
            'rol_id'=>'required|int',
            'grupo_id'=>'required|int'
        ]);
        $grupo=Grupo::find($validateData['grupo_id']);
        $rol=Role::find($validateData['rol_id']);
        if(isset($grupo) && isset($rol)){
            if($grupo->estado==1 && $rol->estado==1){
                $asignacion=AsignacionRol::create([
                    "rol_id"=>$validateData['rol_id'],
                    "grupo_id"=>$validateData['grupo_id']
                ]);
                return response()->json([
                    'status'=>true,
                    'message'=>'Rol asignado a grupo correctamente'
                ],200);
            } else {
                return response()->json([
                    'status'=>false,
                    'message'=>'Datos no disponibles'
                ],401);
            }
        }else{
            return response()->json([
                'status'=>false,
                'message'=>'Datos no encontrados'
            ],404);
        }       
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga dos campos y ambos sean enteros
     * $asignacion hace uso de ELOQUENT de laravel con el metodo where y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de eliminar en la DB con el metodo delete
     */
    public function eliminarAsignacion(Request $request){
        $validateData=$request->validate([
            'rol_id'=>'required|int',
            'grupo_id'=>'required|int'
        ]);
        $matchThese = ['rol_id' =>$validateData['rol_id'], 'grupo_id' => $validateData['grupo_id']];
        $asignacion=AsignacionRol::where($matchThese)
            ->first();
        if(isset($asignacion)){
            AsignacionRol::where($matchThese)
            ->delete();
            return response()->json([
                'status'=>true,
                'message'=>'Asignacion de grupo y Rol eliminada'
            ],200);
        } else{
            return response()->json([
                'status'=>true,
                'message'=>'Datos no encontrados'
            ],404);
        }    
    }
}
