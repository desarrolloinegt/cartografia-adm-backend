<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;

class GrupoController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga tres campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $grupo hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
    public function createGroup(Request $request){
        $validateData=$request->validate([
            'nombre'=>'required|string|unique:grupo',
            'descripcion'=>'',
            'proyecto_id'=>'required|int',
        ]);
        $grupo=Grupo::create([
            "nombre"=>$validateData['nombre'],
            "descripcion"=>$validateData['descripcion'],
            "estado"=>1,
            "proyecto_id"=>$validateData['proyecto_id']
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'Grupo creado correctamente'
        ],200);
    }

    /**
     * @param $request recibe la peticion del frontend
     * A traves de ELOQUENT podemos usar el metodo select y seleccionar los campos con la condicion de que el estado
     * sea 1, es decir este activo      
     */
    public function obtenerGrupos(){
        $grupos=Grupo::select("grupo.id","grupo.nombre","grupo.descripcion","proyecto.nombre AS proyecto")
            ->join('proyecto','grupo.proyecto_id','proyecto.id')
            ->where("grupo.estado",1)
            ->get();
        return response()->json($grupos);    
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir requiee que la peticion contenga cuatro campos 
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar la encuesta que corresponde el id
     * 
     * Al obtener el proyecto podemos hacer uso de sus variables y asignarle el valor obtenido en el validateData
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL      
     */

    public function modificarGrupo(Request $request){
        $validateData=$request->validate([
            'id'=>'required|int',
            'nombre'=>'required|string',
            'descripcion'=>'',
            'proyecto_id'=>'required|int',
        ]);
        $grupo=Grupo::find($validateData['id']);
        if(isset($grupo)){
            $grupo->nombre=$validateData['nombre'];
            $grupo->descripcion=$validateData['descripcion'];
            $grupo->proyecto_id=$validateData['proyecto_id'];
            $grupo->save();
            return response()->json([
                'status'=>true,
                'message'=>'Grupo modificado correctamente'
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

    public function desactivarGrupo(int $id){
        try{
            $grupo=Grupo::find($id);
            if(isset($grupo)){
                $grupo->estado=0;
                $grupo->save();
                return response()->json([
                    'status'=>true,
                    'message'=>'Grupo desactivado correctamente'
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
