<?php

namespace App\Http\Controllers;

use App\Models\AsginacionUpmEncargado;
use App\Models\AsignacionGrupo;
use App\Models\AsignacionRolUsuario;
use App\Models\AsignacionUpm;
use App\Models\AsignacionUpmEncargado;
use App\Models\AsignacionUpmProyecto;
use App\Models\AsignacionUpmUsuario;
use App\Models\Grupo;
use App\Models\Organizacion;
use App\Models\Rol;
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
                    $upmProyecto=AsignacionUpmUsuario::where($matchThese)->get();
                    if(isset($user) && isset($upmProyecto)){
                        try{
                            AsignacionUpmUsuario::create([
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
                $grupoMayor = Rol::select('rol.id','rol.nombre','rol.jerarquia')
                ->join('asignacion_rol_usuario','asignacion_rol_usuario.rol_id','rol.id')
                ->where('rol.proyecto_id',$validateData['proyecto_id'])
                ->where('asignacion_rol_usuario.usuario_id',$validateData['usuario_id'])
                ->where('rol.estado',1)
                ->orderBy('rol.jerarquia','DESC')
                ->first();

                $upms=AsignacionUpmUsuario::select('usuario.nombres','usuario.apellidos','upm.nombre as upm')
                    ->join('upm','upm.id','asignacion_upm_usuario.upm_id')
                    ->join('usuario','usuario.id','asignacion_upm_usuario.usuario_id')
                    ->join('asignacion_rol_usuario','asignacion_rol_usuario.usuario_id','usuario.id')
                    ->join('rol','rol.id','asignacion_rol_usuario.rol_id')
                    ->where('rol.proyecto_id',$validateData['proyecto_id'])   
                    ->where('rol.jerarquia','<',$grupoMayor->jerarquia)
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

    public function obtenerUpmsAsignados(Request $request){
        try{
            $validateData=$request->validate([
                "usuario_id"=>"int|required",
                "proyecto_id"=>"int|required"
            ]);
            $rolMayor=AsignacionRolUsuario::select('rol.id','rol.nombre','rol.jerarquia')
                ->join('rol','rol.id','asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id',$validateData['proyecto_id'])
                ->orderBy('rol.jerarquia','DESC')
                ->first();
               
            $rol= Rol::select('rol.id','rol.nombre','rol.jerarquia')
                ->join('asignacion_rol_usuario','asignacion_rol_usuario.rol_id','rol.id')
                ->where('rol.proyecto_id',$validateData['proyecto_id'])
                ->where('asignacion_rol_usuario.usuario_id',$validateData['usuario_id'])
                ->where('rol.estado',1)
                ->first();
            if($rol->jerarquia==$rolMayor->jerarquia){
                $upms=$this->obtenerUpmsJefe($validateData['proyecto_id']);
                return response()->json($upms,200);
            } else{
                $upmss=$this->obtenerUpmsEmpleados($validateData['usuario_id'],$validateData['proyecto_id']);
                return response()->json($upmss,200);
            }
        }catch(\Throwable $th){
            return response()->json([
                "status"=>false,
                "message"=>$th->getMessage()
            ],500);
        }
    }

    public function obtenerUpmsJefe($id){
        try{
            $upms = AsignacionUpmProyecto::select('departamento.nombre as departamento', 'municipio.nombre as municipio', 'upm.nombre as upm', 'estado_upm.nombre as estado', 'upm.id')
            ->join('upm', 'asignacion_upm_proyecto.upm_id', 'upm.id')
            ->join('municipio', 'upm.municipio_id', 'municipio.id')
            ->join('departamento', 'departamento.id', 'municipio.departamento_id')
            ->join('estado_upm', 'estado_upm.cod_estado', 'asignacion_upm_proyecto.estado_upm')
            ->where('asignacion_upm_proyecto.proyecto_id', $id)
            ->where('upm.estado', 1)
            ->get();

            return $upms;
        }catch(\Throwable $th){
            return response()->json([
                "status"=>false,
                "message"=>$th->getMessage()
            ],500);
        }
    }

    public function obtenerUpmsEmpleados($usuario,$proyecto){
        try{
            $upms=AsignacionUpmUsuario::select('departamento.nombre as departamento', 'municipio.nombre as municipio', 'upm.nombre as upm', 'estado_upm.nombre as estado', 'upm.id')
                ->join('upm','upm.id','asignacion_upm_usuario.upm_id')
                ->join('municipio', 'upm.municipio_id', 'municipio.id')
                ->join('asignacion_upm_proyecto','asignacion_upm_proyecto.upm_id','upm.id')
                ->join('estado_upm', 'estado_upm.cod_estado', 'asignacion_upm_proyecto.estado_upm')
                ->join('departamento', 'departamento.id', 'municipio.departamento_id')
                ->where('asignacion_upm_usuario.usuario_id',$usuario)
                ->where('asignacion_upm_proyecto.proyecto_id',$proyecto)
                ->where('upm.estado', 1)
                ->get();
            return $upms;   
        }catch(\Throwable $th){
            return response()->json([
                "status"=>false,
                "message"=>$th->getMessage()
            ],500);
        }
    }
}
