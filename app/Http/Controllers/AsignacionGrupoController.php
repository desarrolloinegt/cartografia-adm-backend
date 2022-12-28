<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionGrupo;
class AsignacionGrupoController extends Controller
{
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
}
