<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permiso;

class PermisoController extends Controller
{
    public function createPermiso(Request $request){
        $validateData=$request->validate([
            'Nombre'=>'required|string|unique:Permiso'
        ]);
        $permiso=Permiso::create(["Nombre"=>$validateData['Nombre']]);
        return response()->json([
            'status'=>true,
            'message'=>'Permiso creado correctamente'
        ],200);
    }
}
