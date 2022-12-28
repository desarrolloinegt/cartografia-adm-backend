<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionRol;

class AsignacionRolController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga dos campos y ambos sean enteros
     * $asignacion hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
    public function asignarRolGrupo(Request $request){
        $validateData=$request->validate([
            'rol_id'=>'required|int',
            'grupo_id'=>'required|int'
        ]);
        $asignacion=AsignacionRol::create([
            "rol_id"=>$validateData['rol_id'],
            "grupo_id"=>$validateData['grupo_id']
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'Rol asignado correctamente'
        ],200);
    }
}
