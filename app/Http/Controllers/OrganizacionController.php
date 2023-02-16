<?php

namespace App\Http\Controllers;

use App\Models\AsignacionGrupo;
use App\Models\Organizacion;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\Request;

class OrganizacionController extends Controller
{
    public function asignarPersonal($id, Request $request)
    {
        $errores = [];
        $array = $request->all();
        try {
            foreach ($array as $key => $value) {
                try {
                    $superior = AsignacionGrupo::select('grupo.jerarquia', 'usuario.id', 'usuario.codigo_usuario AS usuario')
                        ->join('usuario', 'usuario.id', 'asignacion_grupo.usuario_id')
                        ->join('grupo', 'grupo.id', 'asignacion_grupo.grupo_id')
                        ->join('proyecto', 'proyecto.id', 'grupo.proyecto_id')
                        ->where('proyecto.id', $value['proyecto_id'])
                        ->where('usuario.codigo_usuario', $value['codigo_superior'])
                        ->get();
                    $inferior = AsignacionGrupo::select('grupo.jerarquia', 'usuario.id', 'usuario.codigo_usuario AS usuario')
                        ->join('usuario', 'usuario.id', 'asignacion_grupo.usuario_id')
                        ->join('grupo', 'grupo.id', 'asignacion_grupo.grupo_id')
                        ->where('usuario.codigo_usuario', $value['codigo_inferior'])
                        ->get();
                    if(count($superior)>1){

                    }
                    if(count($inferior)>1){

                    }
                    if ($superior->jerarquia > $inferior->jerarquia ) {
                        Organizacion::create([
                            "usuario_superior" => $superior->usuario,
                            "usuario_inferior" => $inferior->usuario,
                            "proyecto_id" => $value['proyecto_id']
                        ]);
                    } else {
                        array_push($errores, [$superior->codigo_usuario, $inferior->codigo_usuario]);
                    }

                } catch (\Throwable $th) {
                    array_push($errores, $th->getMessage());
                }
            }
            return response()->json([
                "status" => true,
                "message" => "Personal Asignado",
                "errores" => $errores
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerAsignacionesPersonal(){
        try{
           /* $validateData=$request->validate([
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
            }*/
        }catch(\Throwable $th){
            return response()->json([
                "status"=>false,
                "message"=>$th->getMessage()
            ],500);
        }
    }
}