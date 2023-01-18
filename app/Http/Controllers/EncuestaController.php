<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Encuesta;
use App\Models\Proyecto;

class EncuestaController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $encuesta hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
    public function crearEncuesta(Request $request){
        $validateData=$request->validate([
            'nombre'=>'required|string|unique:encuesta',
            'descripcion'=>'required|string'
        ]);
        $encuesta=Encuesta::create([
            "nombre"=>$validateData['nombre'],
            "descripcion"=>$validateData['descripcion']
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'Encuesta creada correctamente'
        ],200);
    }

    /**
     * @param $request recibe la peticion del frontend
     * A traves de ELOQUENT podemos usar el metodo select y seleccionar el id y nombre del role con la condicion de que el estado
     * sea 1, es decir este activo      
     */
    public function obtenerEncuestas(){
        $encuestas=Encuesta::select("id","nombre","descripcion")
            ->where('estado',1)
            ->get();
        return response()->json($encuestas);    
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir requiee que la peticion contenga tres campos uno sea entero y el otro un string unico
     * y el ultimo string para descripcion
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar la encuesta que corresponde el id
     * 
     * Al obtener la encuesta podemos hacer uso de sus variables y asignarle el valor obtenido en el validateData
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL      
     */

    public function modificarEncuesta(Request $request){
        $validateData=$request->validate([
            'id'=>'required|int',
            'nombre'=>'required|string',
            'descripcion'=>'required|string'
        ]);
        $encuesta=Encuesta::find($validateData['id']);
        if(isset($encuesta)){
            $encuesta->nombre=$validateData['nombre'];
            $encuesta->descripcion=$validateData['descripcion'];
            $encuesta->save();
            return response()->json([
                'status'=>true,
                'message'=>'Encuesta modificada correctamente'
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
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar la encuesta que corresponde el id
     * 
     * Al obtener la encuesta podemos hacer uso de ELOQUENT con el metodo delete que hace referencia al DELETE de SQL
     * 
     * antes de eliminar la encuesta se verifica si la encuesta es usada en algun proyecto, si no es usada podra ser eliminada
     * de lo contraria no se podra eliminar      
     */

    public function desactivarEncuesta(int $id){
        try{
            $encuesta=Encuesta::find($id);
            if(isset($encuesta)){
                $encuesta->estado=0;
                $encuesta->save();
                return response()->json([
                    'status'=>true,
                    'message'=>'Encuesta desactivada correctamente'
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
