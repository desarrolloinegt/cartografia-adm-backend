<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function createRole(Request $request){
        $validateData=$request->validate([
            'nombre'=>'required|string|unique:rol'
        ]);
        $role=Role::create([
            "nombre"=>$validateData['nombre'],
            "estado"=>1
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'rol creado correctamente'
        ],200);
    }
}
