<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\AsignacionAdministrador;

class AsignacionAdministradorController extends Controller
{
    public function asignarAdmin(Request $request){
        try{
            $validateData = $request->validate([
                "id"=>'int|required',
                "cadena"=>"string|required"
            ]);
            $rol = Role::where('nombre',$validateData['cadena'])->first();
            if(isset($rol)){
                AsignacionAdministrador::create([
                    "usuario_id"=>$validateData['id'],
                    "rol_id"=>$rol->id
                ]);
                return response()->json([
                    "status"=>true,
                    "message"=>"Usuario asignado como administrador"
                ],200); 
            }else{
                AsignacionAdministrador::where('usuario_id',$validateData['id'])->delete();
                return response()->json([
                    "status"=>true,
                    "message"=>"Usuario eliminado como administrador"
                ],200); 
            }
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }
}
