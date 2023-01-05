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
     * $asignacion hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
    public function asignarPermisoRol(Request $request){
        $validateData=$request->validate([
            'rol_id'=>'required|int',
            'permiso_id'=>'required|int'
        ]);
        $asignacion=AsignacionPermiso::create([
            "rol_id"=>$validateData['rol_id'],
            "permiso_id"=>$validateData['permiso_id']
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'Permiso asignado correctamente'
        ],200);
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


    public function store($validateData){
        $rol=Role::find($validateData['rol_id']);
        $permiso=Permiso::find($validateData['permiso_id']);
        if(isset($rol) && isset($permiso)){
            if($rol->estado==1 && $permiso->estado==1){
                try{
                    $asignacion=AsignacionPermiso::create([
                        "rol_id"=>$validateData['rol_id'],
                        "permiso_id"=>$validateData['permiso_id']
                    ]);
                    return response()->json([
                        'status'=>true,
                        'message'=>'Permiso asignado a Rol correctamente'
                    ],200);
                }catch(\Throwable $th) {
                    return response()->json([
                        'status'=>false,
                        'message'=>$th->getMessage()
                    ],500);
                }
                
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
    public function asignacionMasiva(Request $request){
        $array = $request->all();
        $responses=[];
        foreach($array as $asignacion => $item){
            $validateData=Validator::make($item,[
                'rol_id'=>'required|int',
                'permiso_id'=>'required|int'
            ]);
            if($validateData->fails()){
            }else{
                //var_dump($item['grupo_id']);
                array_push($responses,$this->store($item));
                
            }
           
        }
        return response()->json($responses);
    }

}
