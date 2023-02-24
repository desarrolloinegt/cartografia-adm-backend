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
        $errors = [];
        try {
            $array = $request->all();
            $idProject = $array[0]['proyecto_id'];
            $errors = $this->verifiUpmAndUserExists($array);
            if (empty($errors)) {
                $errors = $this->verifyUserUpmProject($array); //Verificar si hay errores en que los usuarios y upms si existan en el proyecto
                if (empty($errors)) {
                    $errors = $this->verifyNotAssignmet($array);
                    if (empty($errors)) {
                        $rolUser = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia')
                            ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                            ->where('asignacion_rol_usuario.usuario_id', $idUser)
                            ->where('rol.estado', 1)
                            ->first();
                        $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia')
                            ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                            ->where('rol.proyecto_id', $idProject)
                            ->orderBy('rol.jerarquia', 'DESC')
                            ->first();
                        if ($rolUser->jerarquia == $rolMayor->jerarquia) {
                            $this->createAssignmet($array, $idUser);
                        } else {
                            $errors = $this->verifyUpmUserAssigned($array, $idUser);
                            if (empty($errors)) {
                                $this->createAssignmet($array, $idUser);
                            } else {
                                return response()->json([
                                    "status" => false,
                                    "message" => $errors
                                ], 400);
                            }
                        }
                    } else {
                        return response()->json([
                            "status" => false,
                            "message" => $errors
                        ], 400);
                    }
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => $errors
                    ], 400);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => $errors
                ], 404);
            }
            return response()->json([
                "status" => true,
                "message" => "UPMs Asignados"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function verifiUpmAndUserExists($asignments)
    {
        $errors = [];
        foreach ($asignments as $key => $value) {
            $upm = UPM::where("nombre", $value['upm'])->first();
            $user = User::where('codigo_usuario', $value['codigo_usuario'])->first();
            if (!isset($upm)) {
                array_push($errors, "El upm: " . $value['upm'] . " no existe");
            }
            if (!isset($user)) {
                array_push($errors, "El usuario: " . $value['codigo_usuario'] . " no existe");
            }
        }
        return $errors;
    }

    public function verifyUserUpmProject($asignments)
    { //Funcion para verificar que el usuario si este asignado al proyecto
        $errors = [];
        foreach ($asignments as $key => $value) {
            //Verificacion del usuario
            $user = User::where('codigo_usuario', $value['codigo_usuario'])->first();
            $rolUsuario = AsignacionRolUsuario::select('usuario.id') //Evaluar si el upm existe en el proyecto
                ->join('usuario', 'usuario.id', 'asignacion_rol_usuario.usuario_id')
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $value['proyecto_id'])
                ->where('asignacion_rol_usuario.usuario_id', $user->id)
                ->first();
            if (!isset($rolUsuario)) {
                array_push($errors, "El usuario: " . $value['codigo_usuario'] . " no esta asignado a este proyecto");
            }

            //Verificacion de upm
            $upm = UPM::where("nombre", $value['upm'])->first();
            $matchThese = ["proyecto_id" => $value['proyecto_id'], "upm_id" => $upm->id, "estado_upm" => 1];
            $upmProject = AsignacionUpmProyecto::where($matchThese)->first(); //Evaluar si el upm existe en el proyecto
            if (!isset($upmProject)) {
                array_push($errors, "El upm: " . $value['upm'] . " no esta asignado a este proyecto");
            }
        }
        return $errors;
    }

    public function verifyNotAssignmet($asignments)
    { //Verificar que una upm no este asignada en el proyecto a una persona con el mismo rol
        $errors = [];
        foreach ($asignments as $key => $value) {
            $user = User::where('codigo_usuario', $value['codigo_usuario'])->first();
            $rolNewUser = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol del usuario al que se le quiere asignar
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('asignacion_rol_usuario.usuario_id', $user->id)
                ->where('rol.estado', 1)
                ->first();
            $assignment = AsignacionUpmUsuario::select('upm.id', 'usuario.id as usuario')
                ->join('upm', 'upm.id', 'asignacion_upm_usuario.upm_id')
                ->join('usuario', 'usuario.id', 'asignacion_upm_usuario.usuario_id')
                ->where('upm.nombre', $value['upm'])
                ->first();
            if (isset($assignment)) {
                $rolUserExist = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol del usuario del que ya esta asignado el upm
                    ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                    ->where('asignacion_rol_usuario.usuario_id', $assignment->usuario)
                    ->where('rol.estado', 1)
                    ->first();
                if ($rolNewUser->jerarquia == $rolUserExist->jerarquia) {
                    array_push($errors, "Upm: " . $value['upm'] . " ya se encuentra asignada al rol: " . $rolUserExist->nombre);
                }
            }
        }
        return $errors;
    }

    public function verifyUpmUserAssigned($asignments, $idUser)
    {
        $errors = [];
        foreach ($asignments as $key => $value) {
            $upm = UPM::where("nombre", $value['upm'])->first();
            $user = User::where('codigo_usuario', $value['codigo_usuario'])->first();
            $matchTheseUpmAssigned = ["usuario_id" => $idUser, "upm_id" => $upm->id];
            $matchTheseUserAssigned = ["usuario_superior" => $idUser, "usuario_inferior" => $user->id];
            $upmAssigned = AsignacionUpmUsuario::where($matchTheseUpmAssigned)->first(); // Evalura si el upm lo tiene asignado el usuario
            $userAssigned = Organizacion::where($matchTheseUserAssigned)->first(); //Evaluar si el que desea asignar esta asignado al encargado previamente
            if (!isset($upmAssigned)) {
                array_push($errors, "Usted no tiene asignado el upm: " . $upm->nombre);
            }
            if (!isset($userAssigned)) {
                array_push($errors, "Usted no tiene asignado el usuario: " . $user->codigo_usuario);
            }
        }
        return $errors;
    }

    public function createAssignmet($array, $asignador)
    {
        foreach ($array as $key => $value) {
            try {
                $upm = UPM::where("nombre", $value['upm'])->first();
                $user = User::where('codigo_usuario', $value['codigo_usuario'])->first();
                AsignacionUpmUsuario::create([
                    "upm_id" => $upm->id,
                    "usuario_id" => $user->id,
                    "proyecto_id" => $value['proyecto_id'],
                    "usuario_asignador" => $asignador
                ]);
            } catch (\Throwable $th) {

            }
        }
    }

    public function obtenerUpmsPersonal(Request $request)
    {
        try {
            $idUser = $request->user()->id;
            $validateData = $request->validate([
                "proyecto_id" => "int|required"
            ]);
            $user = User::find($idUser);
            if (isset($user)) {
                $upms = AsignacionUpmUsuario::selectRaw('rol.nombre as rol,u.id ,CONCAT(u.codigo_usuario,\' \',
                u.nombres,\' \',u.apellidos) AS encargado,upm.nombre as upm')
                    ->join('upm', 'upm.id', 'asignacion_upm_usuario.upm_id')
                    ->join('usuario AS u', 'u.id', 'asignacion_upm_usuario.usuario_id')
                    ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.usuario_id', 'u.id')
                    ->join('usuario AS as', 'as.id', 'asignacion_upm_usuario.usuario_asignador')
                    ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                    ->where('rol.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_usuario.usuario_asignador', $idUser)
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

    public function obtenerUpmCartografos(Request $request)
    {
        try {
            $usuario = $request->user()->id;
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