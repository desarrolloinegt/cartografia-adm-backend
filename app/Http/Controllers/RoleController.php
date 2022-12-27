<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function createRole(Request $request){
        $validateData=$request->validate([
            'Nombre'=>'required|string|unique:Rol'
        ]);
        $role=Role::create(["Nombre"=>$validateData['Nombre']]);
        return response()->json([
            'status'=>true,
            'message'=>'rol creado correctamente'
        ],200);
    }
}
