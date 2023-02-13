<?php

namespace App\Http\Controllers;

use App\Models\AsignacionRolUsuario;
use App\Models\Role;
use Illuminate\Http\Request;

class AsignacionRolUsuarioController extends Controller
{
    public function asignarUsuario(Request $request){
        try{
            $validateData = $request->validate([
                "id"=>'int|required',
                "cadena"=>"string|required"
            ]);
            $rol = Role::where('nombre',$validateData['cadena'])->first();
            if(isset($rol)){
                AsignacionRolUsuario::create([
                    "usuario_id"=>$validateData['id'],
                    "rol_id"=>$rol->id
                ]);
                return response()->json([
                    "status"=>true,
                    "message"=>"Usuario asignado como administrador"
                ],200); 
            }else{
                
            }
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }
}
