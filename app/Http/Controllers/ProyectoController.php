<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proyecto;

class ProyectoController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $encuesta hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
    public function crearProyecto(Request $request){
        $validateData=$request->validate([
            'nombre'=>'required|string|unique:proyecto',
            'fecha'=>'required|date',
            'encuesta_id'=>'required|int'
        ]);
        $date = $validateData['fecha'];
        $newDate = new \DateTime($date); 
        $newDate->format('YYYY-mm-dd');
        $proyecto=Proyecto::create([
            "nombre"=>$validateData['nombre'],
            "fecha"=>$newDate,
            "encuesta_id"=>$validateData['encuesta_id'],
            "estado_proyecto"=>1
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'Proyecto creado correctamente'
        ],200);
    }

    /**
     * @param $request recibe la peticion del frontend
     * A traves de ELOQUENT podemos usar el metodo select y seleccionar los campos con la condicion de que el estado
     * sea 1, es decir este activo      
     */
    public function obtenerProyectos(){
        $proyectos=Proyecto::select("proyecto.id","proyecto.nombre","proyecto.fecha","encuesta.nombre AS encuesta")
            ->join('encuesta','proyecto.encuesta_id','encuesta.id')
            ->where("proyecto.estado_proyecto",1)
            ->get();
        return response()->json($proyectos);    
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir requiee que la peticion contenga cuatro campos 
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar la encuesta que corresponde el id
     * 
     * Al obtener el proyecto podemos hacer uso de sus variables y asignarle el valor obtenido en el validateData
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL      
     */

    public function modificarProyecto(Request $request){
        $validateData=$request->validate([
            'id'=>'required|int',
            'nombre'=>'required|string',
            'fecha'=>'required|string',
            'encuesta_id'=>'required|int'
        ]);
        $proyecto=Proyecto::find($validateData['id']);
        if(isset($proyecto)){
            $proyecto->nombre=$validateData['nombre'];
            $proyecto->fecha=$validateData['fecha'];
            $proyecto->encuesta_id=$validateData['encuesta_id'];
            $proyecto->save();
            return response()->json([
                'status'=>true,
                'message'=>'Proyecto modificado correctamente'
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
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar el proyecto que corresponde el id
     * 
     * Al obtener el proyecto podemos hacer uso de ELOQUENT y obtener su variable estado_proyecto y asignarle 0
     * El metodo save() de ELOQUENT es equivalente al UPDATE de SQL
     *      
     */

    public function desactivarProyecto(int $id){
        try{
            $proyecto=Proyecto::find($id);
            if(isset($proyecto)){
                $proyecto->estado_proyecto=0;
                $proyecto->save();
                return response()->json([
                    'status'=>true,
                    'message'=>'Proyecto desactivado correctamente'
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
