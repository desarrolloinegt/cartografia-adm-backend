<?php

namespace App\Http\Controllers;

use App\Models\AsignacionPoliticaUsuario;
use App\Models\AsignacionRolUsuario;
use App\Models\Politica;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignacionPoliticaUsuarioController extends Controller
{
    public function asignarUsuarioPolitica(Request $request){
        try{
            $validateData = $request->validate([
                "id"=>'int|required',
                "politicas"=>"array|required",
                "politicas.*"=>'int'
            ]);
            $user=User::find($validateData['id']);
            $arrayPoliticas=$validateData['politicas'];
            if(isset($user)){
                AsignacionPoliticaUsuario::where('usuario_id',$user->id)->delete();
                foreach ($arrayPoliticas as $politica) {
                    $policy=Politica::find($politica);
                    if($policy->politica_sistema==1){
                        AsignacionPoliticaUsuario::create([
                            "usuario_id"=>$user->id,
                            "politica_id"=>$politica
                        ]);
                    }
                }
                return response()->json([
                    "status"=>true,
                    "message"=>"Politicas asignadas correctamente"
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

    public function obtenerUsuarioPoliticas($id){
        try {
            $asginaciones = AsignacionPoliticaUsuario::select('politica.nombre')
                ->join('politica', 'asignacion_politica_usuario.politica_id', 'politica.id')
                ->join('usuario', 'usuario.id', 'asignacion_politica_usuario.usuario_id')
                ->where('usuario.id',$id)
                ->where('politica.estado',1)
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
