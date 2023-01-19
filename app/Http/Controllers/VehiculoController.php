<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehiculo;
class VehiculoController extends Controller
{
        /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $encuesta hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
    public function crearVehiculo(Request $request){
        try{
            $validateData=$request->validate([
                'placa'=>'required|string|max:7|unique:vehiculo',
                'modelo'=>'required|string',
                'year'=>'required|max:4|min:4'
            ]);
            $encuesta=Vehiculo::create([
                "placa"=>$validateData['placa'],
                "modelo"=>$validateData['modelo'],
                "year"=>$validateData['year'],
                "estado"=>1
            ]);
            return response()->json([
                'status'=>true,
                'message'=>'Vehiculo creado correctamente'
            ],200);
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }

    /**
     * @param $request recibe la peticion del frontend
     * A traves de ELOQUENT podemos usar el metodo select y seleccionar el id y nombre del role con la condicion de que el estado
     * sea 1, es decir este activo      
     */
    public function obtenerVehiculos(){
        $encuestas=Vehiculo::select("id","placa","modelo","year")
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

    public function modificarVehiculo(Request $request){
        try{
            $validateData=$request->validate([
                'id'=>'required|int',
                'placa'=>'required|string',
                'modelo'=>'required|string',
                'year'=>'required|max:4|min:4'
            ]);
            $vehiculo=Vehiculo::find($validateData['id']);
            if(isset($vehiculo)){
                $vehiculo->placa=$validateData['placa'];
                $vehiculo->modelo=$validateData['modelo'];
                $vehiculo->year=$validateData['year'];
                $vehiculo->save();
                return response()->json([
                    'status'=>true,
                    'message'=>'Vehiculo modificado correctamente'
                ],200);
            } else{
                return response()->json([
                    'status'=>false,
                    'message'=>'Dato no encontrado'
                ],404);
            }
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
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

    public function desactivarVehiculo(int $id){
        try{
            $vehiculo=Vehiculo::find($id);
            if(isset($vehiculo)){
                $vehiculo->estado=0;
                $vehiculo->save();
                return response()->json([
                    'status'=>true,
                    'message'=>'Vehiculo desactivado correctamente'
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
