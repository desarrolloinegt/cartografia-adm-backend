<?php

namespace App\Http\Controllers;

use App\Models\Politica;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Http\Controllers\AsignacionPermisoController;
use App\Models\AsignacionPermiso;

class PoliticaController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $role hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPolicy(Request $request)
    {
        $validateData = $request->validate([
            'nombre' => 'required|string|unique:politica',
            'politica_sistema' => 'required|min:1|max:1'
        ]);
        $politica = Politica::create([
            "nombre" => $validateData['nombre'],
            "politica_sistema" => $validateData['politica_sistema'],
            "estado" => 1
        ]);
        return response()->json([
            'status' => true,
            'id_rol' => $politica->id,
            'message' => 'Politica creada correctamente'
        ], 200);
    }

    /**
     * @param $request recibe la peticion del frontend
     * A traves de ELOQUENT podemos usar el metodo select y seleccionar el id y nombre del role con la condicion de que el estado
     * sea 1, es decir este activo   
     * @return \Illuminate\Http\JsonResponse   
     */
    public function obtenerPoliticas()
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

    public function obtenerPoliticasSistema()
    {
        try {
            $politicas = Politica::select("id", "nombre", "politica_sistema")
                ->where("estado", 1)
                ->where("politica_sistema",1)
                ->get();
            return response()->json($politicas, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerPoliticasProyecto()
    {
        try {
            $politicas = Politica::select("id", "nombre", "politica_sistema")
                ->where("estado", 1)
                ->where("politica_sistema",0)
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
     * $validateData valida los campos, es decir requiee que la peticion contenga un campo entero un campo string y un array de numeros
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar el rol que corresponde el id
     * Se elimina las asignaciones anteriores para evitar errores y se llama a la funcion asignacionMasiva()
     * que se encarga de crear las nuevas asignaciones
     * 
     * 
     * Al obtener el rol podemos hacer uso de sus variables y asignarle el valor obtenido en el validateData
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL 
     * @return \Illuminate\Http\JsonResponse     
     */

    public function modificarPolitica(Request $request)
    {
        try {
            $validateData = $request->validate([
                'id' => 'required|int',
                'nombre' => 'required|string',
            ]);
            $politica = Politica::find($validateData['id']);
            if (isset($politica)) {
                $politica->nombre = $validateData['nombre'];
                $politica->save();
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
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar el rol que corresponde el id
     * 
     * Al obtener el rol podemos hacer uso de sus variables y asignarle el valor 0 al rol
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL      
     * @return \Illuminate\Http\JsonResponse
     */

    public function desactivarPolitica(int $id)
    {
        try {
            $politica = Politica::find($id);
            if (isset($politica)) {
                if ($politica->nombre != 'Administrador' && $politica->id != 1) {
                    $politica->estado = 0;
                    $politica->save();
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