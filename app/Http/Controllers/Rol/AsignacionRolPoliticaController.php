<?php

namespace App\Http\Controllers\Rol;

use App\Http\Controllers\Controller;
use App\Models\AsignacionRolPolitica;
use App\Models\Politica;
use App\Models\Rol;
use Illuminate\Http\Request;

class AsignacionRolPoliticaController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * Function para asignar politicas a un rol
     * $validateData valida los campos, es decir require que la peticion contenga un campo entero y un array de enteros
     * @return \Illuminate\Http\JsonResponse
     */
    public function asignnRolPolicy(Request $request)
    {
        try {
            $validateData = $request->validate([
                'politicas' => 'required|array',
                'politicas.*' => 'int',
                'rol_id' => 'required|int'
            ]);
            $rol = Rol::find($validateData['rol_id']); //buscamos el rol por el id
            $arrayPoliticas = $validateData['politicas'];
            if (isset($rol)) { //Verifica que el rol exista
                if ($rol->estado == 1) { //Verifica que el rol este activo
                    foreach ($arrayPoliticas as $politica) {
                        $policy = Politica::find($politica);
                        if ($policy->politica_sistema == 0) { //Verifica que la politca no sea de sistema
                            $asignacion = AsignacionRolPolitica::create([
                                //Metodo de ELOQUENT que hace insert a la DB
                                "rol_id" => $rol->id,
                                "politica_id" => $politica
                            ]);
                        }
                    }
                    return response()->json([
                        'status' => true,
                        'message' => 'Politicas asignadas a rol correctamente'
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Datos no disponibles'
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Datos no encontrados'
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
     * @param $request recibe la peticion del frontend
     * Function para agregar o quitar politicas de un rol
     * $validateData valida los campos, es decir require que la peticion contenga un campo  entero y un arreglo de enteros
     * @return \Illuminate\Http\JsonResponse
     */
    public function modifyRolesPolicys(Request $request)
    {
        try {
            $validateData = $request->validate([
                'politicas' => 'required|array',
                'politicas.*' => 'int',
                'rol_id' => 'required|int'
            ]);
            $rol = Rol::find($validateData['rol_id']);
            if (isset($rol)) {
                AsignacionRolPolitica::where('rol_id', $validateData['rol_id'])->delete(); //Eliminamos todas las politicas de rol
                $this->asignnRolPolicy($request); //Llamamos de nuevo la function para asignar y se asignan de nuevo
                return response()->json([
                    'status' => true,
                    'message' => 'Politicas asignadas correctamente'
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
     * @param $id id del rol que se desea saber las politicas
     * Funcion para obtener una tabla con las politicas de un rol
     * siempre que el grupo y rol esten activos y se agrupa los roles por el grupo
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRolesPolicy($id)
    {
        try {
            $asginaciones = AsignacionRolPolitica::select('politica.nombre')
                ->join('politica', 'politica.id', 'asignacion_rol_politica.politica_id')
                ->join('rol', 'asignacion_rol_politica.rol_id', 'rol.id')
                ->where('politica.estado', 1)
                ->where('rol.id', $id)
                ->where('rol.estado', 1)
                ->get();
            return response()->json($asginaciones,200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}