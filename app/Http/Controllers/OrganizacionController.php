<?php

namespace App\Http\Controllers;

use App\Models\AsignacionRolUsuario;
use App\Models\Rol;
use App\Models\Organizacion;
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
                    $superior = AsignacionRolUsuario::select('rol.jerarquia', 'usuario.id', 'usuario.codigo_usuario AS usuario')
                        ->join('usuario', 'usuario.id', 'asignacion_rol.usuario_id')
                        ->join('rol', 'rol.id', 'asignacion_rol.rol_id')
                        ->join('proyecto', 'proyecto.id', 'rol.proyecto_id')
                        ->where('proyecto.id', $value['proyecto_id'])
                        ->where('usuario.codigo_usuario', $value['codigo_superior'])
                        ->fist();
                    $inferior = AsignacionRolUsuario::select('rol.jerarquia', 'usuario.id', 'usuario.codigo_usuario AS usuario')
                        ->join('usuario', 'usuario.id', 'asignacion_rol.usuario_id')
                        ->join('rol', 'rol.id', 'asignacion_rol.rol_id')
                        ->where('usuario.codigo_usuario', $value['codigo_inferior'])
                        ->first();
                    if ($superior->jerarquia > $inferior->jerarquia && $value['codigo_superior']!=$value['codigo_inferior']) {
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
                $rolMayor = rol::select('rol.id','rol.nombre','rol.jerarquia')
                ->join('asignacion_rol','asignacion_rol.rol_id','rol.id')
                ->where('rol.proyecto_id',$validateData['proyecto_id'])
                ->where('asignacion_rol.usuario_id',$validateData['usuario_id'])
                ->where('rol.estado',1)
                ->orderBy('rol.jerarquia','DESC')
                ->first();

                $upms=AsignacionUpmEncargado::select('usuario.nombres','usuario.apellidos','upm.nombre as upm')
                    ->join('upm','upm.id','asignacion_upm_usuario.upm_id')
                    ->join('usuario','usuario.id','asignacion_upm_usuario.usuario_id')
                    ->join('asignacion_rol','asignacion_rol.usuario_id','usuario.id')
                    ->join('rol','rol.id','asignacion_rol.rol_id')
                    ->where('rol.proyecto_id',$validateData['proyecto_id'])   
                    ->where('rol.jerarquia','<',$rolMayor->jerarquia)
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

    public function obtenerPersonalAsignado(Request $request){
        try{
            $validateData=$request->validate([
                'proyecto_id'=>'required|int',
                'usuario_id'=>'required|int',
                'rol_id'=>'required:int'
            ]);
            $rolMayor=AsignacionRolUsuario::select('rol.id','rol.nombre','rol.jerarquia')
                ->join('rol','rol.id','asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id',$validateData['proyecto_id'])
                ->orderBy('rol.jerarquia','DESC')
                ->first();
               
            $rol= Rol::select('rol.id','rol.nombre','rol.jerarquia')
                ->join('asignacion_rol_usuario','asignacion_rol_usuario.rol_id','rol.id')
                ->where('asignacion_rol_usuario.usuario_id',$validateData['usuario_id'])
                ->where('rol.estado',1)
                ->first();
            if($rolMayor->jerarquia==$rol->jerarquia){
                $users=$this->obtenerPersonalJefe($validateData['proyecto_id'],$validateData['rol_id']);
                return response()->json($users,200);
            }else{
                $userss=$this->obtenerPersonalEmpleado($validateData['proyecto_id'],$validateData['usuario_id'],$validateData['rol_id']);
                return response()->json($userss,200);
            }     
        }catch(\Throwable $th){
            return response()->json([
                "status"=>false,
                "message"=>$th->getMessage()
            ],500);
        }
    }

    public function obtenerPersonalJefe($proyecto,$rol){
        try{
            $asginaciones = AsignacionRolUsuario::select('usuario.codigo_usuario','usuario.nombres','usuario.apellidos')
            ->join('usuario', 'asignacion_rol_usuario.usuario_id', 'usuario.id')
            ->join('rol', 'asignacion_rol_usuario.rol_id', 'rol.id')
            ->where('rol.proyecto_id',$proyecto)
            ->where('rol.id',$rol)
            ->where('usuario.estado_usuario',1)
            ->where('rol.estado', 1)
            ->get();
            return $asginaciones;
        }catch(\Throwable $th){
            return response()->json([
                "status"=>false,
                "message"=>$th->getMessage()
            ],500);
        }
    }

    public function obtenerPersonalEmpleado($proyecto,$usuario,$rol){
        try{
            $users=Organizacion::select('usuario.codigo_usuario','usuario.nombres','usuario.apellidos')
                ->join('usuario','usuario.id','organizacion.usuario_inferior')
                ->join('asignacion_rol_usuario','asignacion_rol_usuario.usuario_id','usuario.id')
                ->join('rol','rol.id','asignacion_rol_usuario.rol_id')
                ->where('organizacion.usuario_superior',$usuario)
                ->where('rol.proyecto_id',$proyecto)
                ->where('rol.id',$rol)
                ->where('usuario.estado_usuario',1)
                ->get();
            return $users;
        }catch(\Throwable $th){
            return response()->json([
                "status"=>false,
                "message"=>$th->getMessage()
            ],500);
        }
    }
}