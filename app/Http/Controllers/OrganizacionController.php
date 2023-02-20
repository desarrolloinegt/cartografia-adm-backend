<?php

namespace App\Http\Controllers;

use App\Models\AsignacionRolUsuario;
use App\Models\Rol;
use App\Models\Organizacion;
use App\Models\User;
use Illuminate\Http\Request;

class OrganizacionController extends Controller
{
    public function asignarPersonal( Request $request)
    {
        $errores = [];
        $array = $request->all();
        try {
            foreach ($array as $key => $value) {
                try {
                    $superior = AsignacionRolUsuario::select('rol.jerarquia', 'usuario.id')
                        ->join('usuario', 'usuario.id', 'asignacion_rol_usuario.usuario_id')
                        ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                        ->join('proyecto', 'proyecto.id', 'rol.proyecto_id')
                        ->where('proyecto.id', $value['proyecto_id'])
                        ->where('usuario.codigo_usuario', $value['codigo_superior'])
                        ->first();
                    $inferior = AsignacionRolUsuario::select('rol.jerarquia', 'usuario.id')
                        ->join('usuario', 'usuario.id', 'asignacion_rol_usuario.usuario_id')
                        ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                        ->where('usuario.codigo_usuario', $value['codigo_inferior'])
                        ->first();
                    if ($superior->jerarquia > $inferior->jerarquia && $value['codigo_superior']!=$value['codigo_inferior']) {
                        Organizacion::create([
                            "usuario_superior" => $superior->id,
                            "usuario_inferior" => $inferior->id,
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

    public function obtenerAsignacionesPersonal(Request $request){
        try{
           $validateData=$request->validate([
                "usuario_id"=>"int|required",
                "proyecto_id"=>"int|required"
            ]);
            $user=User::find($validateData['usuario_id']);
            if(isset($user)){
                $rol= Rol::select('rol.id','rol.nombre','rol.jerarquia')
                ->join('asignacion_rol_usuario','asignacion_rol_usuario.rol_id','rol.id')
                ->where('asignacion_rol_usuario.usuario_id',$validateData['usuario_id'])
                ->where('rol.estado',1)
                ->first();
                $users=Organizacion::selectRaw('CONCAT(rs.nombre,\'-\',s.codigo_usuario,\' \', s.nombres,\' \', s.apellidos) as encargado,
                 CONCAT(ri.nombre,\'-\',i.codigo_usuario,\' \', i.nombres, \' \', i.apellidos) AS empleado')
                    ->join('usuario AS s','s.id','organizacion.usuario_superior')
                    ->join('usuario AS i','i.id','organizacion.usuario_inferior')
                    ->join('asignacion_rol_usuario AS ars','ars.usuario_id','s.id')
                    ->join('asignacion_rol_usuario AS ari','ari.usuario_id','i.id')
                    ->join('rol AS rs','rs.id','ars.rol_id')
                    ->join('rol AS ri','ri.id','ari.rol_id')
                    ->where('rs.proyecto_id',$validateData['proyecto_id'])   
                    ->where('rs.jerarquia','<',$rol->jerarquia)
                    ->get();
                    //ars= asignacion rol-usuario superior
                    //ari= asignacion rol-usuario inferior
                    //rs= rol superior
                    //ri= rol inferior
                return response()->json($users,200);    
                
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