<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;

class GrupoController extends Controller
{
    public function createGroup(Request $request){
        $validateData=$request->validate([
            'Nombre'=>'required|string|unique:Grupo',
            'Descripcion'=>'',
            'Proyecto_Id'=>'required|int',
        ]);
        $role=Grupo::create([
            "Nombre"=>$validateData['Nombre'],
            "Descripcion"=>$validateData['Descripcion'],
            "estado"=>1,
            "Proyecto_Id"=>$validateData['Proyecto_Id']
        ]);
        return response()->json([
            'status'=>true,
            'message'=>'Grupo creado correctamente'
        ],200);
    }
}
