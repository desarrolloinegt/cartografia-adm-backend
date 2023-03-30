<?php

namespace App\Http\Controllers\UPM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UPM;

class UPMController extends Controller
{

    /**
     * @param $id recibe el id en la peticion GET
     * Function para desactivar upm      
     * @return \Illuminate\Http\JsonResponse
     */

    public function desactiveUpm(int $id)
    {
        try {
            $upm = UPM::find($id); //Busca el upm por su id
            if (isset($upm)) { //verifica que el upm exista
                $upm->estado = 0; //Cambiar el estado a 0
                $upm->save(); //Metodo save equivalente a UPDATE de sql
                return response()->json([
                    'status' => true,
                    'message' => 'UPM desactivado correctamente'
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

    /**
     * @param $request recibe los datos enviados por el frontend en formato JSON
     * Function para cargar  upms   
     * Function comentada, usada unicamente para desarrollo   
     * @return \Illuminate\Http\JsonResponse
     */
    /*
    public function chargueUpms(Request $request)
    {
        try {
            $errores = [];
            $arrayUpms = $request->all(); //Conviernte el JSON en arreglo
            foreach ($arrayUpms as $key => $value) {
                try {
                    UPM::create([
                        'municipio_id' => $value['municipio_id'],
                        'nombre' => $value['nombre'],
                        'estado' => 1,
                        'departamento_id' => $value['departamento_id']
                    ]);

                } catch (\Throwable $th) {
                    array_push($errores, $th->getMessage());
                }
            }
            return response()->json([
                "status" => true,
                "message" => "Upms cargados",
                "errores" => $errores
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }*/
}