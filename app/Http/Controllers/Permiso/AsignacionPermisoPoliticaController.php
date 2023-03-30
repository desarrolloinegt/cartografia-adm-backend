<?php

namespace App\Http\Controllers\Permiso;

use App\Http\Controllers\Controller;
use App\Models\AsignacionPoliticaPermiso;
use App\Models\Politica;
use Illuminate\Http\Request;

class AsignacionPermisoPoliticaController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * Function para asignar permisos a una politica
     * $validateData valida los campos, es decir require que la peticion contenga un campo y un array de numeros
     * Foreach para recorrer el array de permisos y pasarlos al metodo create
     * $asignacion hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     * @return \Illuminate\Http\JsonResponse
     */

    public function asignnment(Request $request)
    {
        try {
            $validateData = $request->validate([
                'id' => 'required|int',
                'permisos' => 'array|required',
                'permisos.*' => 'int'
            ]);
            $politica = Politica::find($validateData['id']);  // busca que la politica  si exista
            $arrayPermisos = $validateData['permisos'];
            if (isset($politica)) {
                AsignacionPoliticaPermiso::where('politica_id',$politica->id)->delete();
                foreach ($arrayPermisos as $permiso) {
                    $asignacion = AsignacionPoliticaPermiso::create([
                        "politica_id" => $politica->id,
                        "permiso_id" => $permiso
                    ]);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Permisos asignados correctamente'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Rol no Econtrado"
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
     *Funcion para obtener las politicas con los permisos asignados, siempre que el permiso este activo
     *y la politica este activa, agrupados por la politica
     *@return \Illuminate\Http\JsonResponse 
     */
    public function getPolicyPermission($id)
    {
        try {
            $asginaciones = AsignacionPoliticaPermiso::select('permiso.alias')
                ->join('politica', 'asignacion_permiso_politica.politica_id', 'politica.id')
                ->join('permiso', 'asignacion_permiso_politica.permiso_id', 'permiso.id')
                ->where('permiso.estado', 1)
                ->where('politica.estado', 1)
                ->where('politica.id',$id)
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