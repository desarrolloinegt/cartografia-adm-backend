<?php

namespace App\Http\Controllers;

use App\Models\AsignacionGrupo;
use App\Models\Grupo;
use App\Models\Organizacion;
use App\Models\Proyecto;
use Illuminate\Http\Request;

class CargaTrabajoController extends Controller
{
    public function asignarPersonal($id,Request $request){
        $errores = [];
        $array = $request->all();
        try{
            $proyecto = Proyecto::where('id',$id)->first();
            if(isset($proyecto)){
                foreach ($array as $key => $value) {
                    $superior = AsignacionGrupo::select('grupo.jerarquia','usuario.id','usuario.codigo_usuario AS usuario')
                        ->join('usuario','usuario.id','asignacion_grupo.usuario_id')
                        ->join('grupo','grupo.id','asignacion_grupo.grupo_id')
                        ->where('usuario.codigo_usuario',$value['codigo_superior']);
                    $inferior = AsignacionGrupo::select('grupo.jerarquia','usuario.id','usuario.codigo_usuario AS usuario')
                        ->join('usuario','usuario.id','asignacion_grupo.usuario_id')
                        ->join('grupo','grupo.id','asignacion_grupo.grupo_id')
                        ->where('usuario.codigo_usuario',$value['codigo_inferior']);
                    if($superior->jerarquia>$inferior->jerarquia && ($superior->jerarquia-$inferior->jerarquia)==1){
                        Organizacion::create([
                            "usuario_superior"=>$superior->usuario,
                            "usuario_inferior"=>$inferior->usuario,
                            "proyecto_id"=>$proyecto->id
                        ]);
                    }else{
                        array_push($errores,[$superior->codigo_usuario,$inferior->codigo_usuario]);
                    }    
                }
                return response()->json([
                    "status"=>true,
                    "message"=>"Personal Asignado",
                    "errores"=>$errores
                ],200);
            }
        }catch(\Throwable $th){
            return response()->json([
                "status"=>false,
                "message"=>$th->getMessage()
            ],500);
        } 
    }

    public function asignarUpmsAPersonal(Request $request){
        try{
            $array = $request->all();
            foreach ($array as $key => $value) {
                
            }
        }catch(\Throwable $th){
            return response()->json([
                "status"=>false,
                "message"=>$th->getMessage()
            ],500);
        }
    }

    public function asignarEquipos(Request $request){
        
    }
}
