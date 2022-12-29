<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $role hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
    public function createRole(Request $request){
        $validateData=$request->validate([
            'nombre'=>'required|string|unique:rol'
        ]);
        $role=Role::create([
            "nombre"=>$validateData['nombre'],
            "estado"=>1
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'rol creado correctamente'
        ],200);
    }

    /**
     * @param $request recibe la peticion del frontend
     * A traves de ELOQUENT podemos usar el metodo select y seleccionar el id y nombre del role con la condicion de que el estado
     * sea 1, es decir este activo      
     */
    public function obtenerRoles(Request $request){
        $roles=Role::select("id","nombre")
            ->where("estado",1)
            ->get();
        return response()->json($roles);    
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir requiee que la peticion contenga dos campos uno sea entero y el otro un string
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar el rol que corresponde el id
     * 
     * Al obtener el rol podemos hacer uso de sus variables y asignarle el valor obtenido en el validateData
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL      
     */

    public function modificarRol(Request $request){
        $validateData=$request->validate([
            'id'=>'required|int',
            'nombre'=>'required|string'
        ]);
        $rol=Role::find($validateData['id']);
        if(isset($rol)){
            $rol->nombre=$validateData['nombre'];
            $rol->save();
            return response()->json([
                'status'=>true,
                'message'=>'rol modificado correctamente'
            ],200);
        } else{
            return response()->json([
                'status'=>false,
                'message'=>'Dato no encontrado'
            ],404);
        }
    }

    /**
     * @param $id recibe el id en la peticion GET
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar el rol que corresponde el id
     * 
     * Al obtener el rol podemos hacer uso de sus variables y asignarle el valor 0 al rol
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL      
     */

    public function desactivarRol(int $id){
        try{
            $rol=Role::find($id);
            if(isset($rol)){
                $rol->estado=0;
                $rol->save();
                return response()->json([
                    'status'=>true,
                    'message'=>'rol desactivado correctamente'
                ],200);
            } else{
                return response()->json([
                    'status'=>false,
                    'message'=>'ERROR, dato no encontrado'
                ],404); 
            }
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }
}
