<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionGrupo;
class AsignacionGrupoController extends Controller
{
    public function asignarGrupoUsuario(Request $request){
        $validateData=$request->validate([
            'Usuario_Id'=>'required|int',
            'Grupo_Id'=>'required|int'
        ]);
        $asignacion=AsignacionGrupo::create([
            "Usuario_Id"=>$validateData['Usuario_Id'],
            "Grupo_Id"=>$validateData['Grupo_Id']
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'Grupo asignado correctamente'
        ],200);
    }
}
