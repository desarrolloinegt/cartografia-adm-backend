<?php

namespace App\Http\Controllers;

use App\Models\AsignacionRolUsuario;
use App\Models\ControlProgreso;
use App\Models\Departamento;
use App\Models\ReemplazoUpm;
use App\Models\Rol;
use App\Models\UPM;
use Illuminate\Http\Request;

class ControlProgresoController extends Controller
{
    public function getLogUpm(Request $request){
        try{
            $validateData=$request->validate([
                "proyecto_id"=>'required|int',
                "upm"=>'required|string'
            ]);
            $idUser=$request->user()->id;    
            $progress=ControlProgreso::select('upm.nombre as upm','control_de_progreso.fecha','estado_upm.nombre as tipo','estado_upm.cod_estado','usuario.nombres','usuario.apellidos')
                ->join('upm','upm.id','control_de_progreso.upm_id')
                ->join('asignacion_upm_usuario','asignacion_upm_usuario.upm_id','control_de_progreso.upm_id')
                ->join('estado_upm','estado_upm.cod_estado','control_de_progreso.estado_upm')
                ->join('usuario','usuario.id','asignacion_upm_usuario.usuario_id')
                ->join('organizacion','organizacion.usuario_inferior','usuario.id')
                ->where('upm.nombre',$validateData['upm'])
                ->where('organizacion.usuario_superior',$idUser)
                ->where('usuario.estado_usuario',1)
                ->where('control_de_progreso.proyecto_id',$validateData['proyecto_id'])
                ->where('asignacion_upm_usuario.proyecto_id',$validateData['proyecto_id'])
                ->orderBy('control_de_progreso.fecha','DESC')->get();
          
            return response()->json($progress,200);     
        } catch(\Throwable $th){
            return response()->json([
                "status"=>true,
                "message"=>$th->getMessage()
            ],500);
        }
    }


    public function getProgressDashboard(Request $request){
        try{
            $validateData=$request->validate([
                "proyecto_id"=>'required|int'
            ]);
            $user=$request->user()->id;
            $rolUser = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia')
                    ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                    ->where('asignacion_rol_usuario.usuario_id', $user)
                    ->where('rol.estado', 1)
                    ->first();
            $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia')
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->orderBy('rol.jerarquia', 'DESC')
                ->first();
            $inProgress="";
            $finished="";
            $total="";
            if($rolUser->jerarquia == $rolMayor->jerarquia){
                $total=UPM::selectRaw('COUNT(upm.nombre)')
                    ->join('asignacion_upm_proyecto','asignacion_upm_proyecto.upm_id','upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id',$validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.estado_upm','!=','4')
                    ->get();
                $inProgress=UPM::selectRaw('COUNT(upm.nombre)')
                    ->join('asignacion_upm_proyecto','asignacion_upm_proyecto.upm_id','upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id',$validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.estado_upm',2)
                    ->get(); 
                $finished=UPM::selectRaw('COUNT(upm.nombre)')
                    ->join('asignacion_upm_proyecto','asignacion_upm_proyecto.upm_id','upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id',$validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.estado_upm',3)
                    ->get();  
            } else {
                $total=UPM::selectRaw('COUNT(upm.nombre)')
                    ->join('asignacion_upm_proyecto','asignacion_upm_proyecto.upm_id','upm.id')
                    ->join('asignacion_upm_usuario','asignacion_upm_usuario.upm_id','upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id',$validateData['proyecto_id'])
                    ->where('asignacion_upm_usuario.usuario_id',$user)
                    ->where('asignacion_upm_usuario.proyecto_id',$validateData['proyecto_id'])
                    ->get();
                $inProgress=UPM::selectRaw('COUNT(upm.nombre)')
                    ->join('asignacion_upm_proyecto','asignacion_upm_proyecto.upm_id','upm.id')
                    ->join('asignacion_upm_usuario','asignacion_upm_usuario.upm_id','upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id',$validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.estado_upm',2)
                    ->where('asignacion_upm_usuario.usuario_id',$user)
                    ->where('asignacion_upm_usuario.proyecto_id',$validateData['proyecto_id'])
                    ->get();
                $finished=UPM::selectRaw('COUNT(upm.nombre)')
                    ->join('asignacion_upm_proyecto','asignacion_upm_proyecto.upm_id','upm.id')
                    ->join('asignacion_upm_usuario','asignacion_upm_usuario.upm_id','upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id',$validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.estado_upm',3)
                    ->where('asignacion_upm_usuario.usuario_id',$user)
                    ->where('asignacion_upm_usuario.proyecto_id',$validateData['proyecto_id'])
                    ->get();  
            } 
            return response()->json([
                "total"=>$total,
                "finalizados"=>$finished,
                "progreso"=>$inProgress
            ],200);
        } catch(\Throwable $th){
            return response()->json([
                "status"=>true,
                "message"=>$th->getMessage()
            ],500);
        }
    }

    public function getDepartmentsProject(Request $request){
        try {
            $validateData=$request->validate([
                "proyecto_id"=>'required|int'
            ]);
            $user=$request->user()->id;
            $rolUser = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia')
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('asignacion_rol_usuario.usuario_id', $user)
                ->where('rol.estado', 1)
                ->first();
            $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia')
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->orderBy('rol.jerarquia', 'DESC')
                ->first();
            $data="";
            if($rolUser->jerarquia == $rolMayor->jerarquia){
                $data=Departamento::selectRaw('departamento.id,departamento.nombre')
                    ->join('upm','upm.departamento_id','departamento.id')
                    ->join('asignacion_upm_proyecto','asignacion_upm_proyecto.upm_id','upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id',$validateData['proyecto_id'])
                    ->groupBy('departamento.id')
                    ->get();
            } else {
                $data=Departamento::selectRaw('departamento.id,departamento.nombre')
                ->join('upm','upm.departamento_id','departamento.id')
                    ->join('asignacion_upm_proyecto','asignacion_upm_proyecto.upm_id','upm.id')
                    ->join('asignacion_upm_usuario','asignacion_upm_usuario.upm_id','upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id',$validateData['proyecto_id'])
                    ->where('asignacion_upm_usuario.usuario_id',$user)
                    ->where('asignacion_upm_usuario.proyecto_id',$validateData['proyecto_id'])
                    ->groupBy('departamento.id')
                    ->get();
            } 
            return response()->json($data,200);
        } catch(\Throwable $th){
            return response()->json([
                "status"=>true,
                "message"=>$th->getMessage()
            ],500);
        }
    }
}
