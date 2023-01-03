<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionGrupo;
class AsignacionGrupoController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga dos campos y ambos sean enteros
     * $asignacion hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
    public function asignarGrupoUsuario(Request $request){
        $validateData=$request->validate([
            'usuario_id'=>'required|int',
            'grupo_id'=>'required|int'
        ]);
        $asignacion=AsignacionGrupo::create([
            "usuario_id"=>$validateData['usuario_id'],
            "grupo_id"=>$validateData['grupo_id']
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'Grupo asignado correctamente'
        ],200);
    }

    public function asignacionMasiva(Request $request){

    }
}
