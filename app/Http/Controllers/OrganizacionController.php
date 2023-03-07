<?php

namespace App\Http\Controllers;

use App\Models\AsignacionRolUsuario;
use App\Models\AsignacionUpmUsuario;
use App\Models\Rol;
use App\Models\Organizacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizacionController extends Controller
{
    public function asignarPersonal(Request $request)
    {
        $errors = [];
        $idUser = $request->user()->id;
        $array = $request->all();
        try {
            $idProject = $array[0]['proyecto_id'];
            $errors = $this->verifyExistUser($array);
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
                    $errors = $this->verifyUserProject($array);
                    if (empty($errors)) {
                        $errors = $this->verifyNotAssignmet($array);
                        if (empty($errors)) {
                            $errors = $this->verifyHierarchy($array);
                            if (empty($errors)) {
                                $this->createOrganization($array, $idUser);
                            } else {
                                return response()->json([
                                    "status" => false,
                                    "message" => $errors
                                ], 404);
                            }
                        } else {
                            return response()->json([
                                "status" => false,
                                "message" => $errors
                            ], 404);
                        }
                    } else {
                        return response()->json([
                            "status" => false,
                            "message" => $errors
                        ], 404);
                    }
                } else {
                    $errors = $this->verifyUserProject($array);
                    if (empty($errors)) {
                        $errors = $this->verifyUserAssignment($array, $idUser, $idProject);
                        if (empty($errors)) {
                            $errors = $this->verifyHierarchy($array);
                            if (empty($errors)) {
                                $errors = $this->verifyNotAssignmet($array);
                                if (empty($errors)) {
                                    $this->createOrganization($array, $idUser);
                                } else {
                                    return response()->json([
                                        "status" => false,
                                        "message" => $errors
                                    ], 404);
                                }
                            } else {
                                return response()->json([
                                    "status" => false,
                                    "message" => $errors
                                ], 404);
                            }
                        } else {
                            return response()->json([
                                "status" => false,
                                "message" => $errors
                            ], 404);
                        }
                    } else {
                        return response()->json([
                            "status" => false,
                            "message" => $errors
                        ], 404);
                    }
                }
            }
            return response()->json([
                "status" => true,
                "message" => "Personal Asignado",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    private function verifyExistUser($array)
    {
        $errors = [];
        foreach ($array as $key => $value) {
            $superior = User::where('codigo_usuario', $value['codigo_superior'])->first();
            ;
            $inferior = User::where('codigo_usuario', $value['codigo_inferior'])->first();
            if (!isset($superior)) {
                array_push($errors, "El usuario: " . $value['codigo_superior'] . " no existe");
            }
            if (!isset($inferior)) {
                array_push($errors, "El usuario: " . $value['codigo_inferior'] . " no existe");
            }
        }
        return $errors;
    }

    public function verifyUserProject($asignments)
    { //Funcion para verificar que el usuario si este asignado al proyecto
        $errors = [];
        foreach ($asignments as $key => $value) {
            //Verificacion del usuario
            $superior = User::where('codigo_usuario', $value['codigo_superior'])->first();
            ;
            $inferior = User::where('codigo_usuario', $value['codigo_inferior'])->first();
            $userSuperiorProject = AsignacionRolUsuario::select('usuario.id') //Evaluar si el upm existe en el proyecto
                ->join('usuario', 'usuario.id', 'asignacion_rol_usuario.usuario_id')
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $value['proyecto_id'])
                ->where('asignacion_rol_usuario.usuario_id', $superior->id)
                ->first();
            $userInferiorProject = AsignacionRolUsuario::select('usuario.id') //Evaluar si el upm existe en el proyecto
                ->join('usuario', 'usuario.id', 'asignacion_rol_usuario.usuario_id')
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $value['proyecto_id'])
                ->where('asignacion_rol_usuario.usuario_id', $inferior->id)
                ->first();
            if (!isset($userSuperiorProject)) {
                array_push($errors, "El usuario: " . $value['codigo_superior'] . " no esta asignado a este proyecto");
            }
            if (!isset($userInferiorProject)) {
                array_push($errors, "El usuario: " . $value['codigo_inferior'] . " no esta asignado a este proyecto");
            }
            DB::disconnect();
        }
        return $errors;
    }

    public function verifyNotAssignmet($asignments)
    { //Verificar que un usuario no este asignada en el proyecto a una persona con el mismo rol
        $errors = [];
        foreach ($asignments as $key => $value) {
            $superior = User::where('codigo_usuario', $value['codigo_superior'])->first();
            $inferior = User::where('codigo_usuario', $value['codigo_inferior'])->first();
            $rolNewSuperior = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol del usuario al que se le quiere asignar
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('asignacion_rol_usuario.usuario_id', $superior->id)
                ->where('rol.estado', 1)
                ->first();
            $assignment = Organizacion::select('su.id', 'su.codigo_usuario')
                ->join('usuario AS in', 'in.id', 'organizacion.usuario_inferior')
                ->join('usuario AS su', 'su.id', 'organizacion.usuario_superior')
                ->where('in.id', $inferior->id)
                ->where('organizacion.proyecto_id',$value['proyecto_id'])
                ->first();
            if (isset($assignment)) {
                $rolUserExist = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol del encargado del usuario inferior
                    ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                    ->where('asignacion_rol_usuario.usuario_id', $assignment->id)
                    ->where('rol.estado', 1)
                    ->first();
                if ($rolNewSuperior->jerarquia == $rolUserExist->jerarquia) {
                    array_push($errors, "Usuario: " . $value['codigo_inferior'] . " ya se encuentra asignado al usuario: " . $assignment->codigo_usuario);
                }
            }
            DB::disconnect();
        }
        return $errors;
    }


    private function verifyHierarchy($asignments)
    {
        $errors = [];
        foreach ($asignments as $key => $value) {
            $superior = User::where('codigo_usuario', $value['codigo_superior'])->first();
            $inferior = User::where('codigo_usuario', $value['codigo_inferior'])->first();
            $rolSuperior = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol del usuario al que se le quiere asignar
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('asignacion_rol_usuario.usuario_id', $superior->id)
                ->where('rol.estado', 1)
                ->first();
            $rolInferior = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol del usuario al que se le quiere asignar
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('asignacion_rol_usuario.usuario_id', $inferior->id)
                ->where('rol.estado', 1)
                ->first();
            if ($rolSuperior->jerarquia > $rolInferior->jerarquia && $value['codigo_superior'] != $value['codigo_inferior']) {
            } else {
                array_push($errors, "El usuario: " . $value['codigo_superior'] . " no puede ser encardo del usuario: " . $value['codigo_inferior']);
            }
            DB::disconnect();
        }
        return $errors;
    }

    private function verifyUserAssignment($asignments, $idUser, $idProject)
    {
        $errors = [];
        foreach ($asignments as $key => $value) {
            $superior = User::where('codigo_usuario', $value['codigo_superior'])->first();
            $inferior = User::where('codigo_usuario', $value['codigo_inferior'])->first();
            $matchTheseSuperior = ["usuario_superior" => $idUser, "usuario_inferior" => $superior->id, "proyecto_id" => $idProject];
            $matchTheseInferior = ["usuario_superior" => $idUser, "usuario_inferior" => $inferior->id, "proyecto_id" => $idProject];
            $asignacionSuperior = Organizacion::where($matchTheseSuperior)->first();
            $asignacionInferior = Organizacion::where($matchTheseInferior)->first();
            if (!isset($asignacionSuperior)) {
                array_push($errors, "Usted no tiene asignado el usuario: " . $value['codigo_superior']);
            }
            if (!isset($asignacionInferior)) {
                array_push($errors, "Usted no tiene asignado el usuario: " . $value['codigo_inferior']);
            }
            DB::disconnect();
        }
        return $errors;
    }
    public function createOrganization($asignments, $asignador)
    {
        $array=[];
        foreach ($asignments as $key => $value) {
            try {
                $superior = User::where('codigo_usuario', $value['codigo_superior'])->first();
                $inferior = User::where('codigo_usuario', $value['codigo_inferior'])->first();
                Organizacion::create([
                    "usuario_superior" => $superior->id,
                    "usuario_inferior" => $inferior->id,
                    "proyecto_id" => $value['proyecto_id'],
                    "usuario_asignador" => $asignador
                ]);
            } catch (\Throwable $th) {

            }
        }
    }

    public function deleteAssignmentOrganization(Request $request)
    {
        try {
            $idUser=$request->user()->id;
            $validateData = $request->validate([
                "encargado_id" => "int|required",
                "empleado_id" => "int|required",
                "proyecto_id"=>"int|required"
            ]);
            $matchThese=["usuario_superior"=>$validateData["encargado_id"],"usuario_inferior"=>$validateData["empleado_id"]
            ,"proyecto_id"=>$validateData["proyecto_id"],"usuario_asignador"=>$idUser];
            $assignment=Organizacion::where($matchThese)->first();
            if(isset($assignment)){
                Organizacion::where($matchThese)->delete();
                $matchThese=["usuario_superior"=>$validateData['empleado_id'],"proyecto_id"=>$validateData["proyecto_id"]];
                Organizacion::where($matchThese)->delete();
                $matchThese=["usuario_id"=>$validateData['empleado_id'],"proyecto_id"=>$validateData['proyecto_id']];
                AsignacionUpmUsuario::where($matchThese)->delete();
            }
            return response()->json([
                "status" => true,
                "message" => "Asignacion eliminada",
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }

    }
    public function obtenerAsignacionesPersonal(Request $request)
    {
        try {
            $idUser = $request->user()->id;
            $validateData = $request->validate([
                "proyecto_id" => "int|required"
            ]);
            $user = User::find($idUser);
            if (isset($user)) {

                $users = Organizacion::selectRaw('s.id AS encargado_id,CONCAT(rs.nombre,\'-\',s.codigo_usuario,\' \', s.nombres,\' \', s.apellidos) as encargado,
                i.id AS empleado_id,CONCAT(ri.nombre,\'-\',i.codigo_usuario,\' \', i.nombres, \' \', i.apellidos) AS empleado')
                    ->join('usuario AS s', 's.id', 'organizacion.usuario_superior')
                    ->join('usuario AS i', 'i.id', 'organizacion.usuario_inferior')
                    ->join('usuario AS as', 'as.id', 'organizacion.usuario_asignador')
                    ->join('asignacion_rol_usuario AS ars', 'ars.usuario_id', 's.id')
                    ->join('asignacion_rol_usuario AS ari', 'ari.usuario_id', 'i.id')
                    ->join('rol AS rs', 'rs.id', 'ars.rol_id')
                    ->join('rol AS ri', 'ri.id', 'ari.rol_id')
                    ->where('rs.proyecto_id', $validateData['proyecto_id'])
                    ->where('as.id', $idUser)
                    ->get();
                //ars= asignacion rol-usuario superior
                //ari= asignacion rol-usuario inferior
                //rs= rol superior
                //ri= rol inferior
                return response()->json($users, 200);
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

    public function obtenerPersonalAsignado(Request $request)
    {
        try {
            $validateData = $request->validate([
                'proyecto_id' => 'required|int',
                'usuario_id' => 'required|int',
                'rol_id' => 'required|int'
            ]);
            $idUser=$request->user()->id;
            $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia')
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->orderBy('rol.jerarquia', 'DESC')
                ->first();

            $rol = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia')
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('asignacion_rol_usuario.usuario_id', $idUser)
                ->where('rol.estado', 1)
                ->first();
            if ($rolMayor->jerarquia == $rol->jerarquia) {
                $users = $this->obtenerPersonalJefe($validateData['proyecto_id'], $validateData['rol_id']);
                return response()->json($users, 200);
            } else {
                $userss = $this->obtenerPersonalEmpleado($validateData['proyecto_id'], $validateData['usuario_id'], $validateData['rol_id']);
                return response()->json($userss, 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerPersonalJefe($proyecto, $rol)
    {
        try {
            $asginaciones = AsignacionRolUsuario::select('usuario.codigo_usuario', 'usuario.nombres', 'usuario.apellidos')
                ->join('usuario', 'asignacion_rol_usuario.usuario_id', 'usuario.id')
                ->join('rol', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('rol.proyecto_id', $proyecto)
                ->where('rol.id', $rol)
                ->where('usuario.estado_usuario', 1)
                ->where('rol.estado', 1)
                ->get();
            return $asginaciones;
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerPersonalEmpleado($proyecto, $usuario, $rol)
    {
        try {
            $users = Organizacion::select('usuario.codigo_usuario', 'usuario.nombres', 'usuario.apellidos')
                ->join('usuario', 'usuario.id', 'organizacion.usuario_inferior')
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.usuario_id', 'usuario.id')
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('organizacion.usuario_superior', $usuario)
                ->where('rol.proyecto_id', $proyecto)
                ->where('rol.id', $rol)
                ->where('usuario.estado_usuario', 1)
                ->get();
            return $users;
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }
}