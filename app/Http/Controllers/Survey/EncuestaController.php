<?php

namespace App\Http\Controllers\Survey;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Encuesta;

class EncuestaController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * Function para crear una encuesta
     * $validateData valida los campos, es decir require que la peticion contenga un campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSurvey(Request $request)
    {
        try{
            $validateData = $request->validate([
                'nombre' => 'required|string',
                'descripcion' => ''
            ]);
            $exist=Encuesta::where('nombre',$validateData['nombre'])->first();
            if(isset($exist)){ //Verifica que la encunesta exista
                return response()->json([
                    'status' => true,
                    'message' => 'Esta encuesta ya existe'
                ], 400);
            }else{
                $encuesta = Encuesta::create([ //Metodo de eloquent create que hace referencia a insert de sql
                    "nombre" => $validateData['nombre'],
                    "descripcion" => $validateData['descripcion'],
                    "estado" => 1
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Encuesta creada correctamente'
                ], 200);
            }    
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
       
    }

    /**
     * Function para obtener las encuestas acticas   
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSurveys()
    {
        try{
            $encuestas = Encuesta::select("id", "nombre", "descripcion")
                ->where('estado', 1)
                ->get();
            return response()->json($encuestas);
        }  catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }

    /**
     * @param $request recibe la peticion del frontend en formato JSON
     * $validateData valida los campos, es decir requiee que la peticion contenga tres campos uno sea entero y el otro un string unico
     * y el ultimo string para descripcion
     * @return \Illuminate\Http\JsonResponse     
     */

    public function editSurvey(Request $request)
    {
        try{
            $validateData = $request->validate([
                'id' => 'required|int',
                'nombre' => 'required|string',
                'descripcion' => 'required|string'
            ]);
            $encuesta = Encuesta::find($validateData['id']); //Obtener datos de la encuensta
            if (isset($encuesta)) { //Verificar que la encuesta si exista
                $encuesta->nombre = $validateData['nombre']; // Nuevo nombre
                $encuesta->descripcion = $validateData['descripcion'];//Nueva descripcion
                $encuesta->save(); //Metodo save de eloquent que hace referencia al UPDATE de sql
                return response()->json([
                    'status' => true,
                    'message' => 'Encuesta modificada correctamente'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Dato no encontrado'
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
     * @param $id recibe el id en la peticion GET
     * Funcion para desactivar encuesta     
     * @return \Illuminate\Http\JsonResponse
     */

    public function desactiveSurvey(int $id)
    {
        try {
            $encuesta = Encuesta::find($id); //Buscar informacion de la encuesta
            if (isset($encuesta)) { //Verificar que exista la encuesta
                $encuesta->estado = 0; //Cambiar el estado a 0
                $encuesta->save(); //Metodo SAVE de eloquent que hace referncia a UPDATE de sql
                return response()->json([
                    'status' => true,
                    'message' => 'Encuesta desactivada correctamente'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'ERROR, dato no encontrado'
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}