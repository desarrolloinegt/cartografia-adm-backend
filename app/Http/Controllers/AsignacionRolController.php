<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionRol;

class AsignacionRolController extends Controller
{
    public function asignarRolGrupo(Request $request){
        $validateData=$request->validate([
            'Rol_Id'=>'required|int',
            'Grupo_Id'=>'required|int'
        ]);
        $asignacion=AsignacionRol::create([
            "Rol_Id"=>$validateData['Rol_Id'],
            "Grupo_Id"=>$validateData['Grupo_Id']
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'Rol asignado correctamente'
        ],200);
    }
}
