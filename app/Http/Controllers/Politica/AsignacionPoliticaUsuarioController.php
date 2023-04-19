<?php

namespace App\Http\Controllers\Politica;

use App\Http\Controllers\Controller;
use App\Models\AsignacionPoliticaUsuario;
use App\Models\Politica;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsignacionPoliticaUsuarioController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * Functio para asignar una politica a un usuario, es decir asignar politicas de sistema
     *$validateData valida los campos, es decir require que la peticion contenga un campo y un array de numeros
     *@return \Illuminate\Http\JsonResponse
     */
    public function asignnUserPolicy(Request $request){
        try{
            $validateData = $request->validate([
                "id"=>'int|required',
                "politicas"=>"array|required",
                "politicas.*"=>'int'
            ]);
            $user=User::find($validateData['id']); //busca el usuario para luego verificar que exista
            $arrayPoliticas=$validateData['politicas'];
            if(isset($user)){
                AsignacionPoliticaUsuario::where('usuario_id',$user->id)->delete();
                foreach ($arrayPoliticas as $politica) {
                    $policy=Politica::find($politica);
                    if($policy->politica_sistema==1){ //Si la politica es 1 es decir si es politica del sistema
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


    /**
    *@param $id recibe el id del usuario 
    *function para obtener las politicas de sisteama de un usuario
    *@return \Illuminate\Http\JsonResponse
    */
    public function getUserPolicy($id){
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
