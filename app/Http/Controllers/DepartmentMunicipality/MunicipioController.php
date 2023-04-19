<?php

namespace App\Http\Controllers\DepartmentMunicipality;

use App\Http\Controllers\Controller;
use App\Models\Municipio;

class MunicipioController extends Controller
{
    /**
     * Funcion que devuleve la tabla municipios, mostrando el nombre del departamento
     * al que pertenece  cada municipio
     * Summary of obtenerMunicipios
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMunicipality()
    {
        try {
            $municipios = Municipio::select('municipio.id', 'municipio.nombre', 'departamento.nombre AS departamento')
                ->join('departamento', 'municipio.departamento_id', 'departamento.id')
                ->get();
            return response()->json($municipios,200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => true,
                "message" => $th->getMessage(),
            ], 500);
        }

    }

    /**
     * @param $request obtiene los datos enviados por el frontend en formato json
     * Function para cargar los municipio a la DB
     * Function comentada, usada solo para desarrollo
     * @return \Illuminate\Http\JsonResponse
     */

     /*
    public function cargarMunicipios(Request $request)
    {
        try {
            $errores = [];
            $array = $request->all(); //Convierte el formato JSON en un arreglo
            foreach ($array as $departamento => $value) { //Se recorre el arreglo
                try {
                    Municipio::create([ //Se inserta el municipio
                        "nombre" => $value['nombre'],
                        "departamento_id" => $value['departamento_id'],
                        "id" => $value['id']
                    ]);
                } catch (\Throwable $th) {
                    array_push($errores, $th->getMessage());
                }
            }
            return response()->json([
                "status" => true,
                "message" => "Municipios creados",
                "errores" => $errores
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => true,
                "message" => $th->getMessage(),
            ], 500);
        }
    }*/
}