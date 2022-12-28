<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $role hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
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
