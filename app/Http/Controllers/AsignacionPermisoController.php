<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionPermiso;

class AsignacionPermisoController extends Controller
{
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
