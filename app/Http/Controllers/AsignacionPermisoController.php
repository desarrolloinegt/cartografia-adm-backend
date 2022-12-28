<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionPermiso;

class AsignacionPermisoController extends Controller
{

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga dos campos y ambos sean enteros
     * $asignacion hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
    public function asignarPermisoRol(Request $request){
        $validateData=$request->validate([
            'rol_id'=>'required|int',
            'permiso_id'=>'required|int'
        ]);
        $asignacion=AsignacionPermiso::create([
            "rol_id"=>$validateData['rol_id'],
            "permiso_id"=>$validateData['permiso_id']
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'Permiso asignado correctamente'
        ],200);
    }
}
