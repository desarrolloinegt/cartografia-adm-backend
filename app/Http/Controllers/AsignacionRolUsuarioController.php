<?php

namespace App\Http\Controllers;

use App\Models\AsignacionRolUsuario;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignacionRolUsuarioController extends Controller
{
    public function asignarUsuarioRol(Request $request){
        try{
            $validateData = $request->validate([
                "id"=>'int|required',
                "roles"=>"array|required",
                "roles.*"=>'int'
            ]);
            $user=User::find($validateData['id']);
            $arrayRoles=$validateData['roles'];
            if(isset($user)){
                AsignacionRolUsuario::where('usuario_id',$user->id)->delete();
                foreach ($arrayRoles as $rol) {
                    try{
                        AsignacionRolUsuario::create([
                            "usuario_id"=>$user->id,
                            "rol_id"=>$rol
                        ]);
                    }catch(\Throwable $th){

                    } 
                }
                return response()->json([
                    "status"=>true,
                    "message"=>"Roles asignados correctamente"
                ],200); 
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Usuario no encontrado"
                ], 404);
            }
        }catch(\Throwable $th){
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }

    public function obtenerRolesUsuario($id){
        try {
            $asginaciones = AsignacionRolUsuario::select('rol.nombre')
                ->join('rol', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->join('usuario', 'usuario.id', 'asignacion_rol_usuario.usuario_id')
                ->where('usuario.id',$id)
                ->where('rol.estado',1)
                ->where('usuario.estado_usuario', 1)
                ->get();
            return response()->json($asginaciones);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
