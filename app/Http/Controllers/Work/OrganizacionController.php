<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Models\AsignacionRolUsuario;
use App\Models\AsignacionUpmUsuario;
use App\Models\Rol;
use App\Models\Organizacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizacionController extends Controller
{
    /**
     * @param $request obtiene los datos enviados por el frontend en formato JSON
     * function para asignar el personal 
     *  @return \Illuminate\Http\JsonResponse  
     */
    public function assignn(Request $request)
    {
        $errors = [];
        $idUser = $request->user()->id; //Id de usuario autenticado
        $array = $request->all(); //Convierte el json en array
        try {
            $idProject = $array[0]['proyecto_id']; //Obtener el id del proyecto
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
                        ], 400);
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
                            ], 400);
                        }
                    } else {
                        return response()->json([
                            "status" => false,
                            "message" => $errors
                        ], 400);
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

    /**
     * @param $array recibe el array con los datos requerido
     * @param $idUser recibe el id del usuario autenticado
     * @param $idProject recbie el id del proyecto 
     */
    private function verifyErrors($array, $idUser, $idProject)
    {
        $errors = [];
        foreach ($array as $key => $value) {
            //Verificacion de que el usuario exista
            $superior = User::where('codigo_usuario', $value['codigo_superior'])->first();
            $inferior = User::where('codigo_usuario', $value['codigo_inferior'])->first();
            if (!isset($superior)) {
                array_push($errors, "El usuario: " . $value['codigo_superior'] . " no existe");
            }
            if (!isset($inferior)) {
                array_push($errors, "El usuario: " . $value['codigo_inferior'] . " no existe");
            }

            //Verificacion de que el usuario exista en el proyecto
            $superior = User::where('codigo_usuario', $value['codigo_superior'])->first();
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

            //Verificacion de que el usuario no se encuentre asignado a alguien del mismo rol
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
                ->where('organizacion.proyecto_id', $value['proyecto_id'])
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

            //Verificar que el usuario si este asignado al autenticado
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

            //Verificar la jerarquia
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
        }
        return $errors;
    }
    private function verifyExistUser($array)
    {
        $errors = [];
        foreach ($array as $key => $value) {
            $superior = User::where('codigo_usuario', $value['codigo_superior'])->first();
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
                ->where('organizacion.proyecto_id', $value['proyecto_id'])
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
        $fecha = new \DateTime("now", new \DateTimeZone('America/Guatemala'));
        foreach ($asignments as $key => $value) {
            try {
                $superior = User::where('codigo_usuario', $value['codigo_superior'])->first();
                $inferior = User::where('codigo_usuario', $value['codigo_inferior'])->first();
                Organizacion::create([
                    "usuario_superior" => $superior->id,
                    "usuario_inferior" => $inferior->id,
                    "proyecto_id" => $value['proyecto_id'],
                    "usuario_asignador" => $asignador,
                    "fecha_asignacion" => $fecha
                ]);
            } catch (\Throwable $th) {

            }
        }
    }

    /**
     * @param $request obtiene los datos enviados desde el frontend en formato JSON
     * function para eliminar un asignacion de personal
     *  @return \Illuminate\Http\JsonResponse  
     */
    public function deleteAssignmentOrganization(Request $request)
    {
        try {
            $idUser = $request->user()->id; //id del usuario autenticado
            $validateData = $request->validate([
                "encargado_id" => "int|required",
                "empleado_id" => "int|required",
                "proyecto_id" => "int|required"
            ]);
            $matchThese = [
                "usuario_superior" => $validateData["encargado_id"],
                "usuario_inferior" => $validateData["empleado_id"],
                "proyecto_id" => $validateData["proyecto_id"],
                "usuario_asignador" => $idUser
            ];
            $assignment = Organizacion::where($matchThese)->first(); //Busca que los datos enviados si correspondadn a una asignacion
            if (isset($assignment)) {
                Organizacion::where($matchThese)->delete(); //Elimina la asignacion
                $matchThese = ["usuario_superior" => $validateData['empleado_id'], "proyecto_id" => $validateData["proyecto_id"]];
                Organizacion::where($matchThese)->delete(); //Elimina donde el usuario sea superior
                $matchThese = ["usuario_id" => $validateData['empleado_id'], "proyecto_id" => $validateData['proyecto_id']];
                AsignacionUpmUsuario::where($matchThese)->delete(); //Elimina donde el usuario sea inferior
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

    /**
     * @param $request obtiene los datos enviados desde el frontend en formato JSON
     * function para obtener la lista de asiganciones hechas por un usuario
     *  @return \Illuminate\Http\JsonResponse  
     */
    public function getAsignnments(Request $request)
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
                    ->join('asignacion_rol_usuario AS ars', 'ars.usuario_id', 's.id')
                    ->join('asignacion_rol_usuario AS ari', 'ari.usuario_id', 'i.id')
                    ->join('rol AS rs', 'rs.id', 'ars.rol_id')
                    ->join('rol AS ri', 'ri.id', 'ari.rol_id')
                    ->where('rs.proyecto_id', $validateData['proyecto_id'])
                    ->where('ri.proyecto_id', $validateData['proyecto_id'])
                    ->where('ars.proyecto_id', $validateData['proyecto_id'])
                    ->where('ari.proyecto_id', $validateData['proyecto_id'])
                    ->where('organizacion.proyecto_id', $validateData['proyecto_id'])
                    ->where('organizacion.usuario_asignador', $idUser)
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

     /**
     * @param $request obtiene los datos enviados desde el frontend en formato JSON
     * function para obtener el personal asignado, es decir los usuarios donde el auntenticado sea superior
     *  @return \Illuminate\Http\JsonResponse  
     */
    public function obtenerPersonalAsignado(Request $request)
    {
        try {
            $validateData = $request->validate([
                'proyecto_id' => 'required|int',
                'usuario_id' => 'required|int',
                'rol_id' => 'required|int'
            ]);
            $idUser = $request->user()->id;
            $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia') //busca el rol mayor del proyecto
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->orderBy('rol.jerarquia', 'DESC')
                ->first();

            $rol = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia')//Busca el rol del usuario
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('asignacion_rol_usuario.usuario_id', $idUser)
                ->where('rol.estado', 1)
                ->first();
            if ($rolMayor->jerarquia == $rol->jerarquia) {
                $users = $this->getPersonalChief($validateData['proyecto_id'], $validateData['rol_id']);
                return response()->json($users, 200);
            } else {
                $userss = $this->getPersonalEmployee($validateData['proyecto_id'], $validateData['usuario_id'], $validateData['rol_id']);
                return response()->json($userss, 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Function para obtener el personal cuando el usuario esta asignado al rol mas alto
     * @param $proyecto id del proyecto
     * @param $rol id del rol
     */
    public function getPersonalChief($proyecto, $rol)
    {
        try {
            $asginaciones = AsignacionRolUsuario::select('usuario.codigo_usuario', 'usuario.nombres', 'usuario.apellidos')
                ->join('usuario', 'asignacion_rol_usuario.usuario_id', 'usuario.id')
                ->join('rol', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('rol.proyecto_id', $proyecto)
                ->where('asignacion_rol_usuario.proyecto_id', $proyecto)
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

    /**
     * Function para obtener el personal asignado cuando no se esta en el rol superior del proyecto
     * @param $proyecto id del proyecto
     * @param $usuario id del usuario
     * @param $rol id del rol
     */
    public function getPersonalEmployee($proyecto, $usuario, $rol)
    {
        try {
            $users = Organizacion::select('usuario.codigo_usuario', 'usuario.nombres', 'usuario.apellidos')
                ->join('usuario', 'usuario.id', 'organizacion.usuario_inferior')
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.usuario_id', 'usuario.id')
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('organizacion.usuario_superior', $usuario)
                ->where('rol.proyecto_id', $proyecto)
                ->where('organizacion.proyecto_id', $proyecto)
                ->where('asignacion_rol_usuario.proyecto_id', $proyecto)
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