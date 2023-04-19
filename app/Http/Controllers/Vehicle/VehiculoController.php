<?php

namespace App\Http\Controllers\Vehicle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehiculo;

class VehiculoController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga tres campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * @return \Illuminate\Http\JsonResponse
     */
    public function createVehicle(Request $request)
    {
        try {
            $validateData = $request->validate([
                'placa' => 'required|string|max:7',
                'modelo' => 'required|string',
                'year' => 'required|max:4|min:4'
            ]);
            $exists = Vehiculo::where('placa', $validateData['placa'])->first(); //busca el vehiculo por su placa
            if (isset($exists)) { //Verifica que el vehiculo exista
                return response()->json([
                    'status' => true,
                    'message' => 'Este vehiculo ya existe'
                ], 404);
            } else {
                $vehicle = Vehiculo::create([
                    "placa" => $validateData['placa'],
                    "modelo" => $validateData['modelo'],
                    "year" => $validateData['year'],
                    "estado" => 1
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'Vehiculo creado correctamente'
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
     *funcion que devuelve una lista de los vehiculos siempre que esten activos 
     * @return \Illuminate\Http\JsonResponse  
     */
    public function getVehicles()
    {
        try {
            $encuestas = Vehiculo::select("id", "placa", "modelo", "year")
                ->where('estado', 1)
                ->get();
            return response()->json($encuestas);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @param $request recibe los datos enviados por el frontend en formato JSON
     * Function para editar un vehiculo
     * $validateData valida los campos, es decir requiee que la peticion contenga cuatro campos
     * @return \Illuminate\Http\JsonResponse    
     */
    public function editVehicle(Request $request)
    {
        try {
            $validateData = $request->validate([
                'id' => 'required|int',
                'placa' => 'required|string',
                'modelo' => 'required|string',
                'year' => 'required|max:4|min:4'
            ]);
            $vehiculo = Vehiculo::find($validateData['id']); //Busca el vehiculo por su id
            if (isset($vehiculo)) { //Verifica que el vehiculo exista
                $vehiculo->placa = $validateData['placa']; //Cambia la placa
                $vehiculo->modelo = $validateData['modelo']; //Cambia el modelo
                $vehiculo->year = $validateData['year']; //Cambia el año
                $vehiculo->save(); //Metodo save equivalente a UPDATE sql
                return response()->json([
                    'status' => true,
                    'message' => 'Vehiculo modificado correctamente'
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
     * Function para desactivar un vehiculo
     * @return \Illuminate\Http\JsonResponse    
     */
    public function desactiveVehicle(int $id)
    {
        try {
            $vehiculo = Vehiculo::find($id); // busca el vehiclo por su id
            if (isset($vehiculo)) { //Verifica que el vehiculo exista
                $vehiculo->estado = 0; //Cambia el estado 
                $vehiculo->save(); //Metodo save equivalente a UPDATE de sql
                return response()->json([
                    'status' => true,
                    'message' => 'Vehiculo desactivado correctamente'
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