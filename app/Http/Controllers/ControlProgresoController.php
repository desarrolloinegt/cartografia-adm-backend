<?php

namespace App\Http\Controllers;

use App\Models\ControlProgreso;
use App\Models\ReemplazoUpm;
use Illuminate\Http\Request;

class ControlProgresoController extends Controller
{
    public function getLogUpm(Request $request){
        try{
            $validateData=$request->validate([
                "proyecto_id"=>'required|int'
            ]);
            $idUser=$request->user()->id;    
            $progress=ControlProgreso::select('upm.nombre as upm','control_de_progreso.fecha','estado_upm.nombre as tipo','estado_upm.cod_estado','usuario.nombres','usuario.apellidos')
                ->join('upm','upm.id','control_de_progreso.upm_id')
                ->join('asignacion_upm_usuario','asignacion_upm_usuario.upm_id','control_de_progreso.upm_id')
                ->join('estado_upm','estado_upm.cod_estado','control_de_progreso.estado_upm')
                ->join('usuario','usuario.id','asignacion_upm_usuario.usuario_id')
                ->join('organizacion','organizacion.usuario_inferior','usuario.id')
                ->where('organizacion.usuario_superior',$idUser)
                ->where('usuario.estado_usuario',1)
                ->where('control_de_progreso.proyecto_id',$validateData['proyecto_id'])
                ->orderBy('control_de_progreso.fecha')->get();
            return response()->json($progress,200);     
                  
        } catch(\Throwable $th){
            return response()->json([
                "status"=>true,
                "message"=>$th->getMessage()
           ],500);
        }
      
    }
}
