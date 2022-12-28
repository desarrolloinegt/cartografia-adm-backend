<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionRol;

class AsignacionRolController extends Controller
{
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
