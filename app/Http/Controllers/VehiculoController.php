<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehiculo;

class VehiculoController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga tres campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $vehiculo hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     * @return \Illuminate\Http\JsonResponse
     */
    public function crearVehiculo(Request $request)
    {
        try {
            $validateData = $request->validate([
                'placa' => 'required|string|max:7',
                'modelo' => 'required|string',
                'year' => 'required|max:4|min:4'
            ]);
            $exists=Vehiculo::where('placa',$validateData['placa'])->first();
            if(isset($exists)){
                return response()->json([
                    'status' => true,
                    'message' => 'Este vehiculo ya existe'
                ], 404);
            } else{
                $vehiculo = Vehiculo::create([
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
    public function obtenerVehiculos()
    {
        $encuestas = Vehiculo::select("id", "placa", "modelo", "year")
            ->where('estado', 1)
            ->get();
        return response()->json($encuestas);
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir requiee que la peticion contenga cuatro campos
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar la encuesta que corresponde el id
     * 
     * Al obtener la encuesta podemos hacer uso de sus variables y asignarle el valor obtenido en el validateData
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL  
     * @return \Illuminate\Http\JsonResponse    
     */

    public function modificarVehiculo(Request $request)
    {
        try {
            $validateData = $request->validate([
                'id' => 'required|int',
                'placa' => 'required|string',
                'modelo' => 'required|string',
                'year' => 'required|max:4|min:4'
            ]);
            $vehiculo = Vehiculo::find($validateData['id']);
            if (isset($vehiculo)) {
                $vehiculo->placa = $validateData['placa'];
                $vehiculo->modelo = $validateData['modelo'];
                $vehiculo->year = $validateData['year'];
                $vehiculo->save();
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
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar la encuesta que corresponde el id
     * 
     * Al obtener el vehiculo podemos hacer uso de sus variables y usar la variable estado y asignarle 0
     * con el metodo save se guardan los cambios en la DB 
     * @return \Illuminate\Http\JsonResponse    
     */

    public function desactivarVehiculo(int $id)
    {
        try {
            $vehiculo = Vehiculo::find($id);
            if (isset($vehiculo)) {
                $vehiculo->estado = 0;
                $vehiculo->save();
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