<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Models\AsignacionRolUsuario;
use App\Models\AsignacionUpmProyecto;
use App\Models\AsignacionUpmUsuario;
use App\Models\Rol;
use Illuminate\Http\Request;

class OfficeWork extends Controller
{
    public function getUpmFinishedCartographer(Request $request)
    {
        try {
            $validateData = $request->validate([
                "proyecto_id" => "required|int"
            ]);
            $userId = $request->user()->id;
            $matchThese = ["usuario_id" => $userId, "proyecto_id" => $validateData['proyecto_id']];
            $assignment = AsignacionRolUsuario::where($matchThese)->first();
            if (isset($assignment)) {
                $data=AsignacionUpmProyecto::select('departamento.nombre as departamento','municipio.nombre as municipio'
                ,'upm.id','upm.nombre as upm','estado_upm.nombre as estado','estado_upm.cod_estado','usuario.id',
                'usuario.codigo_usuario','usuario.nombres','usuario.apellidos','control_de_progreso.fecha')
                    ->join('upm','upm.id','asignacion_upm_proyecto.upm_id')
                    ->join('departamento', 'departamento.id', 'upm.departamento_id')
                    ->join('municipio', function ($join) {
                        $join->on('municipio.id', 'upm.municipio_id')->on('municipio.departamento_id', 'upm.departamento_id');
                    })
                    ->join('estado_upm','estado_upm.cod_estado','asignacion_upm_proyecto.estado_upm')
                    ->join('asignacion_upm_usuario','asignacion_upm_usuario.upm_id', 'upm.id')
                    ->join('control_de_progreso',function ($join){
                        $join->on('control_de_progreso.upm_id','asignacion_upm_usuario.upm_id')
                        ->on('control_de_progreso.upm_id','upm.id');
                    })
                    ->join('equipo_campo','equipo_campo.supervisor','asignacion_upm_usuario.usuario_id')
                    ->join('usuario', function ($join) {
                        $join->on('usuario.id', 'equipo_campo.supervisor')->on('usuario.id', 'asignacion_upm_usuario.usuario_id');
                    })
                    ->where('equipo_campo.proyecto_id',$validateData['proyecto_id'])
                    ->where('asignacion_upm_usuario.proyecto_id',$validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.proyecto_id',$validateData['proyecto_id'])
                    ->where('control_de_progreso.proyecto_id',$validateData['proyecto_id'])
                    ->where('usuario.estado_usuario',1)
                    ->where('asignacion_upm_proyecto.estado_upm',3)
                    ->where('control_de_progreso.estado_upm',3)
                    ->orderBy('control_de_progreso.fecha','DESC')
                    ->get();
                    return response()->json($data, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Usted no tiene asignado el proyecto elegido"
                ], 400);
            }
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}