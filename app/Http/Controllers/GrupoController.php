<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;

class GrupoController extends Controller
{
    public function createGroup(Request $request){
        $validateData=$request->validate([
            'nombre'=>'required|string|unique:grupo',
            'descripcion'=>'',
            'proyecto_id'=>'required|int',
        ]);
        $role=Grupo::create([
            "nombre"=>$validateData['nombre'],
            "descripcion"=>$validateData['descripcion'],
            "estado"=>1,
            "proyecto_id"=>$validateData['proyecto_id']
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'Grupo creado correctamente'
        ],200);
    }
}
