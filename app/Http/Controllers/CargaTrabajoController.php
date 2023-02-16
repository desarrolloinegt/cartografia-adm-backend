<?php

namespace App\Http\Controllers;

use App\Models\AsginacionUpmEncargado;
use App\Models\AsignacionGrupo;
use App\Models\AsignacionUpm;
use App\Models\AsignacionUpmEncargado;
use App\Models\Grupo;
use App\Models\Organizacion;
use App\Models\UPM;
use App\Models\User;
use App\Models\Proyecto;
use Illuminate\Http\Request;

class CargaTrabajoController extends Controller
{
   

    public function asignarUpmsAPersonal(Request $request){
        try{
            $errores=[];
            $array = $request->all();
            foreach ($array as $key => $value) {
                $upm=UPM::where("nombre",$value['upm'])->first();
                $user=User::where('codigo_usuario',$value['codigo_usuario'])->first();
                if(isset($upm)){
                    $matchThese=["proyecto_id"=>$value['proyecto_id'],"upm_id"=>$upm->id];
                    $upmProyecto=AsignacionUpm::where($matchThese)->get();
                    if(isset($user) && isset($upmProyecto)){
                        try{
                            AsignacionUpmEncargado::create([
                                "upm_id"=>$upm->id,
                                "usuario_id"=>$user->id,
                                "proyecto_id"=>$value['proyecto_id']
                            ]);
                        }catch(\Throwable $th) { array_push($errores,$th->getMessage());}
                    }else { array_push($errores,"El usuario".$value['codigo_usuario']." no existe"); }        
                }else { array_push($errores,"El upm".$value['upm']." no existe"); }   
            }
            return response()->json([
                "status"=>true,
                "message"=>"UPMs Asignados",
                "errores"=>$errores
            ],200);
        }catch(\Throwable $th){
            return response()->json([
                "status"=>false,
                "message"=>$th->getMessage()
            ],500);
        }
    }
    public function obtenerUpmsPersonal(Request $request){
        try{
            $validateData=$request->validate([
                "usuario_id"=>"int|required",
                "proyecto_id"=>"int|required"
            ]);
            $user=User::find($validateData['usuario_id']);
            if(isset($user)){
                $grupoMayor = Grupo::select('grupo.id','grupo.nombre','grupo.jerarquia')
                ->join('asignacion_grupo','asignacion_grupo.grupo_id','grupo.id')
                ->where('grupo.proyecto_id',$validateData['proyecto_id'])
                ->where('asignacion_grupo.usuario_id',$validateData['usuario_id'])
                ->where('grupo.estado',1)
                ->orderBy('grupo.jerarquia','DESC')
                ->first();

                $upms=AsignacionUpmEncargado::select('usuario.nombres','usuario.apellidos','upm.nombre as upm')
                    ->join('upm','upm.id','asignacion_upm_usuario.upm_id')
                    ->join('usuario','usuario.id','asignacion_upm_usuario.usuario_id')
                    ->join('asignacion_grupo','asignacion_grupo.usuario_id','usuario.id')
                    ->join('grupo','grupo.id','asignacion_grupo.grupo_id')
                    ->where('grupo.proyecto_id',$validateData['proyecto_id'])   
                    ->where('grupo.jerarquia','<',$grupoMayor->jerarquia)
                    ->get();
                return response()->json($upms,200);
            }else{
                return response()->json([
                    "status"=>false,
                    "message"=>"Usuario no encontrado"
                ],404); 
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
