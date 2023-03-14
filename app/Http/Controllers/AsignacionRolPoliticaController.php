<?php

namespace App\Http\Controllers;

use App\Models\AsignacionRolPolitica;
use App\Models\Politica;
use App\Models\Rol;
use Illuminate\Http\Request;
use App\Models\AsignacionRol;
use App\Models\Role;
use App\Models\Grupo;

class AsignacionRolPoliticaController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campo entero y un array de enteros
     * Foreach para recorrer el array que es un array de ids de roles
     * $asignacion hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     * @return \Illuminate\Http\JsonResponse
     */
    public function asignarRolPolitica(Request $request)
    {
        $validateData = $request->validate([
            'politicas' => 'required|array',
            'politicas.*' => 'int',
            'rol_id' => 'required|int'
        ]);
        $rol = Rol::find($validateData['rol_id']);
        $arrayPoliticas = $validateData['politicas'];
        if (isset($rol)) {
            if ($rol->estado == 1) {
                foreach ($arrayPoliticas as $politica) {
                    $policy=Politica::find($politica);
                    if($policy->politica_sistema==0){ 
                        $asignacion = AsignacionRolPolitica::create([
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
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campos  entero y un arreglo de enteros
     * $grupo obtenemos la verificacion si el grupo existe o no
     * Para asignar nuevos roles al grupo eliminamos los anteriores y se crean de nuevo
     * y se llama a la funcion asignarRolGrupo(), esta se encarga de crear las nuevas asignaciones
     * @return \Illuminate\Http\JsonResponse
     */
    public function modificarRolesPoliticas(Request $request)
    {
        $validateData = $request->validate([
            'politicas' => 'required|array',
            'politicas.*' => 'int',
            'rol_id' => 'required|int'
        ]);
        $rol = Rol::find($validateData['rol_id']);
        if (isset($rol)) {
            AsignacionRolPolitica::where('rol_id', $validateData['rol_id'])->delete();
            $this->asignarRolPolitica($request);
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
    }

    /**
     * Con esta funcion se obtine una tabla con el grupo y los roles que tiene ese grupo
     * siempre que el grupo y rol esten activos y se agrupa los roles por el grupo
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerRolesPoliticas($id)
    {
        try {
            $asginaciones = AsignacionRolPolitica::select('politica.nombre')
                ->join('politica', 'politica.id','asignacion_rol_politica.politica_id')
                ->join('rol', 'asignacion_rol_politica.rol_id', 'rol.id')
                ->where('politica.estado', 1)
                ->where('rol.id',$id)
                ->where('rol.estado', 1)
                ->get();
            return response()->json($asginaciones);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}