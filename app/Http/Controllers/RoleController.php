<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Http\Controllers\AsignacionPermisoController;
use App\Models\AsignacionPermiso;

class RoleController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $role hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRole(Request $request)
    {
        $validateData = $request->validate([
            'nombre' => 'required|string|unique:rol'
        ]);
        $role = Role::create([
            "nombre" => $validateData['nombre'],
            "estado" => 1
        ]);
        return response()->json([
            'status' => true,
            'id_rol' => $role->id,
            'message' => 'Politica creada correctamente'
        ], 200);
    }

    /**
     * @param $request recibe la peticion del frontend
     * A traves de ELOQUENT podemos usar el metodo select y seleccionar el id y nombre del role con la condicion de que el estado
     * sea 1, es decir este activo   
     * @return \Illuminate\Http\JsonResponse   
     */
    public function obtenerRoles()
    {
        $roles = Role::select("id", "nombre")
            ->where("estado", 1)
            ->get();
        return response()->json($roles);
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

    public function modificarRol(Request $request)
    {
        $validateData = $request->validate([
            'id' => 'required|int',
            'nombre' => 'required|string',
        ]);
        $rol = Role::find($validateData['id']);
        if (isset($rol)) {
            $rol->nombre = $validateData['nombre'];
            $rol->save();
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
    }

    /**
     * @param $id recibe el id en la peticion GET
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar el rol que corresponde el id
     * 
     * Al obtener el rol podemos hacer uso de sus variables y asignarle el valor 0 al rol
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL      
     * @return \Illuminate\Http\JsonResponse
     */

    public function desactivarRol(int $id)
    {
        try {
            $rol = Role::find($id);
            if (isset($rol)) {
                if ($rol->nombre != 'Administrador' && $rol->id != 1) {
                    $rol->estado = 0;
                    $rol->save();
                    return response()->json([
                        'status' => true,
                        'message' => 'Politica desactivada correctamente'
                    ], 200);
                }else{
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