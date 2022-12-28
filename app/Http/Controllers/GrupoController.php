<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grupo;

class GrupoController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga tres campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $grupo hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
    public function createGroup(Request $request){
        $validateData=$request->validate([
            'nombre'=>'required|string|unique:grupo',
            'descripcion'=>'',
            'proyecto_id'=>'required|int',
        ]);
        $grupo=Grupo::create([
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
