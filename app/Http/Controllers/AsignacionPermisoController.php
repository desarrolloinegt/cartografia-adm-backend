<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionPermiso;

class AsignacionPermisoController extends Controller
{
    public function asignarPermisoRol(Request $request){
        $validateData=$request->validate([
            'Rol_Id'=>'required|int',
            'Permiso_Id'=>'required|int'
        ]);
        $asignacion=AsignacionPermiso::create([
            "Rol_Id"=>$validateData['Rol_Id'],
            "Permiso_Id"=>$validateData['Permiso_Id']
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'Permiso asignado correctamente'
        ],200);
    }
}
