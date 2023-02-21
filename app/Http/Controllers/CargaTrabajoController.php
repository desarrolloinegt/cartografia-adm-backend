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
    public function asignarUpmsAPersonal(Request $request)
    {
        $idUser = $request->user()->id;
        $errores = [];
        try {
            $array = $request->all();
            $rol = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia')
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('asignacion_rol_usuario.usuario_id', $idUser)
                ->where('rol.estado', 1)
                ->first();
            foreach ($array as $key => $value) {
                try {
                    $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia')
                        ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                        ->where('rol.proyecto_id', $value['proyecto_id'])
                        ->orderBy('rol.jerarquia', 'DESC')
                        ->first();
                    $upm = UPM::where("nombre", $value['upm'])->first();
                    $user = User::where('codigo_usuario', $value['codigo_usuario'])->first();
                    //Inicio de la parte donde el usuario de mayor rango puede usar todos lo upms del proyecto y todos
                    //los usuarios del rol
                    if ($rol->jerarquia == $rolMayor->jerarquia) {
                        if (isset($upm)) {
                            $matchThese = ["proyecto_id" => $value['proyecto_id'], "upm_id" => $upm->id,"estado_upm"=>1];
                            $upmProyecto = AsignacionUpmProyecto::where($matchThese)->get();//Evalua si el upm existe en el proyecto
                            if (isset($user) && isset($upmProyecto)) {
                                $this->createAssignmet($upm->id, $user->id, $value['proyecto_id'], $idUser);
                            } else {
                                array_push($errores, "El usuario" . $value['codigo_usuario'] . " no existe");
                            }
                        } else {
                            array_push($errores, "El upm" . $value['upm'] . " no existe");
                        }
                        //Fin
                    } else {
                        //Solo puede hacer uso de los upms que le asignaron y los usuarios que le asignaron
                        if (isset($upm)) {
                            $matchThese = ["proyecto_id" => $value['proyecto_id'], "upm_id" => $upm->id,"estado_upm"=>1];
                            $upmProyecto = AsignacionUpmProyecto::where($matchThese)->get();//Evaluar si el upm existe en el proyecto
                            $matchTheseUpmAssigned = ["usuario_id" => $idUser, "upm_id" => $upm->id]; 
                            $matchTheseUserAssigned = ["usuario_superior" => $idUser, "usuario_inferior" => $user->id];
                            $upmAssigned = AsignacionUpmUsuario::where($matchTheseUpmAssigned)->first();// Evalura si el upm lo tiene asignado el usuario
                            $userAssigned = Organizacion::where($matchTheseUserAssigned)->first();//Evaluar si el que desea asignar esta asignado al encargado previamente
                            if (isset($user)) {
                                if (isset($upmAssigned) && isset($userAssigned) && isset($upmProyecto)) {
                                    $this->createAssignmet($upm->id, $user->id, $value['proyecto_id'], $idUser);
                                } else {
                                    array_push($errores, "El usuario no tiene asignado el upm: " . $value['upm'] . ", y el usuario
                                    : " . $value['codigo_usuario']);
                                }
                            } else {
                                array_push($errores, "El usuario" . $value['codigo_usuario'] . " no existe");
                            }
                        } else {
                            array_push($errores, "El upm" . $value['upm'] . " no existe");
                        }
                    }
                } catch (\Throwable $th) {
                    array_push($errores, $th->getMessage());
                }
            }
            return response()->json([
                "status" => true,
                "message" => "UPMs Asignados",
                "errores" => $errores
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function createAssignmet($upm, $usuario, $proyecto, $asignador)
    {
        AsignacionUpmUsuario::create([
            "upm_id" => $upm,
            "usuario_id" => $usuario,
            "proyecto_id" => $proyecto,
            "usuario_asignador" => $asignador
        ]);
    }
    public function obtenerUpmsPersonal(Request $request)
    {
        try {
            $idUser=$request->user()->id;
            $validateData = $request->validate([
                "proyecto_id" => "int|required"
            ]);
            $user = User::find($idUser);
            if (isset($user)) {
                $upms = AsignacionUpmUsuario::selectRaw('rol.nombre as rol,CONCAT(u.codigo_usuario,\' \',
                u.nombres,\' \',u.apellidos) AS encargado,upm.nombre as upm')
                    ->join('upm', 'upm.id', 'asignacion_upm_usuario.upm_id')
                    ->join('usuario AS u', 'u.id', 'asignacion_upm_usuario.usuario_id')
                    ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.usuario_id', 'u.id')
                    ->join('usuario AS as','as.id','asignacion_upm_usuario.usuario_asignador')
                    ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                    ->where('rol.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_usuario.usuario_asignador',$idUser)
                    ->get();
                return response()->json($upms, 200);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Usuario no encontrado"
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerUpmsAsignados(Request $request)
    {
        try {
            $validateData = $request->validate([
                "usuario_id" => "int|required",
                "proyecto_id" => "int|required"
            ]);
            $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia')
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->orderBy('rol.jerarquia', 'DESC')
                ->first();

            $rol = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia')
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->where('asignacion_rol_usuario.usuario_id', $validateData['usuario_id'])
                ->where('rol.estado', 1)
                ->first();
            if ($rol->jerarquia == $rolMayor->jerarquia) {
                $upms = $this->obtenerUpmsJefe($validateData['proyecto_id']);
                return response()->json($upms, 200);
            } else {
                $upmss = $this->obtenerUpmsEmpleados($validateData['usuario_id'], $validateData['proyecto_id']);
                return response()->json($upmss, 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerUpmsJefe($id)
    {
        try {
            $upms = AsignacionUpmProyecto::select('departamento.nombre as departamento', 'municipio.nombre as municipio', 'upm.nombre as upm', 'estado_upm.nombre as estado', 'upm.id')
                ->join('upm', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                ->join('municipio', 'upm.municipio_id', 'municipio.id')
                ->join('departamento', 'departamento.id', 'municipio.departamento_id')
                ->join('estado_upm', 'estado_upm.cod_estado', 'asignacion_upm_proyecto.estado_upm')
                ->where('asignacion_upm_proyecto.proyecto_id', $id)
                ->where('estado_upm.cod_estado', 1)
                ->get();

            return $upms;
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerUpmsEmpleados($usuario, $proyecto)
    {
        try {
            $upms = AsignacionUpmUsuario::select('departamento.nombre as departamento', 'municipio.nombre as municipio', 'upm.nombre as upm', 'estado_upm.nombre as estado', 'upm.id')
                ->join('upm', 'upm.id', 'asignacion_upm_usuario.upm_id')
                ->join('municipio', 'upm.municipio_id', 'municipio.id')
                ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                ->join('estado_upm', 'estado_upm.cod_estado', 'asignacion_upm_proyecto.estado_upm')
                ->join('departamento', 'departamento.id', 'municipio.departamento_id')
                ->where('asignacion_upm_usuario.usuario_id', $usuario)
                ->where('asignacion_upm_proyecto.proyecto_id', $proyecto)
                ->where('estado_upm.cod_estado', 1)
                ->get();
            return $upms;
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerUpmCartografos(Request $request){
        try {
            $usuario=$request->user()->id;
            $validateData = $request->validate([
                "proyecto_id" => "int|required"
            ]);
            $upms = AsignacionUpmUsuario::select('departamento.nombre as departamento', 'municipio.nombre as municipio', 'upm.nombre as upm', 'estado_upm.nombre as estado', 'upm.id')
                ->join('upm', 'upm.id', 'asignacion_upm_usuario.upm_id')
                ->join('municipio', 'upm.municipio_id', 'municipio.id')
                ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                ->join('estado_upm', 'estado_upm.cod_estado', 'asignacion_upm_proyecto.estado_upm')
                ->join('departamento', 'departamento.id', 'municipio.departamento_id')
                ->where('asignacion_upm_usuario.usuario_id', $usuario)
                ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                ->where('upm.estado', 1)
                ->get();
            return response()->json($upms, 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }
}