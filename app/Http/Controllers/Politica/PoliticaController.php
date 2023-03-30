<?php

namespace App\Http\Controllers\Politica;

use App\Http\Controllers\Controller;
use App\Models\Politica;
use Illuminate\Http\Request;

class PoliticaController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campos, la inidicacion unique valida que el nombre sea unico
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPolicy(Request $request)
    {
        try {
            $validateData = $request->validate([
                'nombre' => 'required|string|unique:politica',
                'politica_sistema' => 'required|min:1|max:1'
            ]);
            $politica = Politica::create([
                //se crea politica 
                "nombre" => $validateData['nombre'],
                "politica_sistema" => $validateData['politica_sistema'],
                "estado" => 1
            ]);
            return response()->json([
                'status' => true,
                'id_rol' => $politica->id,
                'message' => 'Politica creada correctamente'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     *Function para obtener las politicas  
     * @return \Illuminate\Http\JsonResponse   
     */
    public function getPolicys()
    {
        try {
            $politicas = Politica::select("id", "nombre", "politica_sistema")
                ->where("estado", 1)
                ->get();
            return response()->json($politicas, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Function para obtener las politicas de sistemaa  
     * @return \Illuminate\Http\JsonResponse   
     */
    public function getSystemPolicys()
    {
        try {
            $politicas = Politica::select("id", "nombre", "politica_sistema")
                ->where("estado", 1)
                ->where("politica_sistema", 1)
                ->get();
            return response()->json($politicas, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Function para obtener las politicas de proyectos  
     * @return \Illuminate\Http\JsonResponse   
     */
    public function getProjectPolicys()
    {
        try {
            $politicas = Politica::select("id", "nombre", "politica_sistema")
                ->where("estado", 1)
                ->where("politica_sistema", 0)
                ->get();
            return response()->json($politicas, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @param $request recibe la peticion del frontend
     * Function para modificar una politica
     * $validateData valida los campos, es decir requiee que la peticion contenga un campo entero un campo string y un array de numeros
     * @return \Illuminate\Http\JsonResponse     
     */

    public function editPolicy(Request $request)
    {
        try {
            $validateData = $request->validate([
                'id' => 'required|int',
                'nombre' => 'required|string',
            ]);
            $politica = Politica::find($validateData['id']); //buscar la politica por su id
            if (isset($politica)) { //Verificar que la politica exista
                $politica->nombre = $validateData['nombre']; //Cambiar el nombre
                $politica->save(); //Save es el metodo equivalente a UPDATE de sql
                return response()->json([
                    'status' => true,
                    'message' => 'Politica modificada correctamente'
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
     * Function pra desactivar una poltica    
     * @return \Illuminate\Http\JsonResponse
     */

    public function desactivePolicy(int $id)
    {
        try {
            $politica = Politica::find($id); //Busca la politica por su id
            if (isset($politica)) { //Verificar que existe la politica
                if ($politica->nombre != 'Administrador' && $politica->id != 1) {
                    $politica->estado = 0; //Cambiar de estado
                    $politica->save(); //Metodo save equivalente a UPDATE de sql
                    return response()->json([
                        'status' => true,
                        'message' => 'Politica desactivada correctamente'
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'La politica de administrador no se puede desactivar'
                    ], 401);
                }
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