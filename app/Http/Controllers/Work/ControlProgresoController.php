<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Models\AsignacionRolUsuario;
use App\Models\AsignacionUpmProyecto;
use App\Models\ControlProgreso;
use App\Models\Departamento;
use App\Models\Rol;
use App\Models\UPM;
use Illuminate\Http\Request;

class ControlProgresoController extends Controller
{
    /**
     * @param $request datos enviados del frontend en formato JSON
     * function para obtener los log de upms
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogUpm(Request $request)
    {
        try {
            $validateData = $request->validate([
                "proyecto_id" => 'required|int',
                "upm" => 'required|string'
            ]);
            $idUser = $request->user()->id; //Id del usuario autenticado
            $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Obtener el rol mas alto del proyecto
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->orderBy('rol.jerarquia', 'DESC')
                ->first();

            $rol = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Obtener el rol del usuario
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->where('asignacion_rol_usuario.usuario_id', $idUser)
                ->where('rol.estado', 1)
                ->first();
            $upm = UPM::where('nombre', $validateData['upm'])->first(); ///obtener informacion del upm 
            if ($rol->jerarquia == $rolMayor->jerarquia) { //Verificar que el rol del usuario sea el mas alto 
                $progress = ControlProgreso::select('upm.nombre as upm', 'control_de_progreso.fecha', 'estado_upm.nombre as tipo', 'estado_upm.cod_estado', 'usuario.nombres', 'usuario.apellidos')
                    ->join('upm', 'upm.id', 'control_de_progreso.upm_id')
                    ->join('asignacion_upm_usuario', 'asignacion_upm_usuario.upm_id', 'control_de_progreso.upm_id')
                    ->join('estado_upm', 'estado_upm.cod_estado', 'control_de_progreso.estado_upm')
                    ->join('usuario', function ($join) {
                        $join->on('usuario.id', 'control_de_progreso.usuario_id')->on('usuario.id', 'asignacion_upm_usuario.usuario_id');
                    })
                    ->where('control_de_progreso.upm_id', $upm->id)
                    ->where('usuario.estado_usuario', 1)
                    ->where('control_de_progreso.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_usuario.proyecto_id', $validateData['proyecto_id'])
                    ->orderBy('control_de_progreso.fecha', 'DESC')->get();

            } else { //Si no es el rol mas alto ingresa en el else
                $progress = ControlProgreso::select('upm.nombre as upm', 'control_de_progreso.fecha', 'estado_upm.nombre as tipo', 'estado_upm.cod_estado', 'usuario.nombres', 'usuario.apellidos')
                    ->join('upm', 'upm.id', 'control_de_progreso.upm_id')
                    ->join('asignacion_upm_usuario', function ($join){
                        $join->on('asignacion_upm_usuario.usuario_id', 'control_de_progreso.usuario_id')->on('asignacion_upm_usuario.upm_id', 'control_de_progreso.upm_id');
                    } )
                    ->join('estado_upm', 'estado_upm.cod_estado', 'control_de_progreso.estado_upm')
                    ->join('usuario', function ($join) {
                        $join->on('usuario.id', 'control_de_progreso.usuario_id')->on('usuario.id', 'asignacion_upm_usuario.usuario_id');
                    })
                    ->join('organizacion', 'organizacion.usuario_inferior', 'usuario.id')
                    ->where('control_de_progreso.upm_id', $upm->id)
                    ->where('organizacion.usuario_superior', $idUser)
                    ->where('usuario.estado_usuario', 1)
                    ->where('control_de_progreso.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_usuario.proyecto_id', $validateData['proyecto_id'])
                    ->where('organizacion.proyecto_id', $validateData['proyecto_id'])
                    ->orderBy('control_de_progreso.fecha', 'DESC')->get();
            }
            return response()->json($progress, 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }


    /**
     * @param $request obtiene los datos enviados del frontend en format JSON
     * Function para obtener el progreso de los upms 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgressDashboard(Request $request)
    {
        try {
            $validateData = $request->validate([
                "proyecto_id" => 'required|int'
            ]);
            $user = $request->user()->id; //Id del usuario autenticado
            $rolUser = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol del usuario
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('asignacion_rol_usuario.usuario_id', $user)
                ->where('rol.estado', 1)
                ->first();
            $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol mas alto del proyecto
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->orderBy('rol.jerarquia', 'DESC')
                ->first();
            $inProgress = "";
            $finished = "";
            $finishedTotal = "";
            $total = "";
            $totalProject = "";
            if ($rolUser->jerarquia == $rolMayor->jerarquia) {
                $total = UPM::selectRaw('COUNT(upm.nombre) as cant') //Upms totales del proyecto
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.estado_upm', '!=', '4')
                    ->first();
                $totalProject = $total->cant;
                $inProgress = UPM::selectRaw('COUNT(upm.nombre) as cant') //Upms en progreso del proyecto
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.estado_upm', 2)
                    ->first();
                $finished = UPM::selectRaw('COUNT(upm.nombre) as cant') //Upms finalizados del proyecto
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.estado_upm', 3)
                    ->first();
                $finishedTotal = $finished->cant;
            } else {
                $totalProjectUser = UPM::selectRaw('COUNT(upm.nombre) as cant') //Upms totales del proyecto
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.estado_upm', '!=', '4')
                    ->first();
                $totalProject = $totalProjectUser->cant;
                $finishedT = UPM::selectRaw('COUNT(upm.nombre) as cant') //Upms finalizados totales del proyecto
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.estado_upm', 3)
                    ->first();
                $finishedTotal = $finishedT->cant;
                $total = UPM::selectRaw('COUNT(upm.nombre) as cant') //Upms totales asignados
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->join('asignacion_upm_usuario', 'asignacion_upm_usuario.upm_id', 'upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_usuario.usuario_id', $user)
                    ->where('asignacion_upm_usuario.proyecto_id', $validateData['proyecto_id'])
                    ->first();
                $inProgress = UPM::selectRaw('COUNT(upm.nombre) as cant') //Upms en progreso de los que estan asignados
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->join('asignacion_upm_usuario', 'asignacion_upm_usuario.upm_id', 'upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.estado_upm', 2)
                    ->where('asignacion_upm_usuario.usuario_id', $user)
                    ->where('asignacion_upm_usuario.proyecto_id', $validateData['proyecto_id'])
                    ->first();
                $finished = UPM::selectRaw('COUNT(upm.nombre) as cant') //Upms finalizados de los que tiene asignados
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->join('asignacion_upm_usuario', 'asignacion_upm_usuario.upm_id', 'upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_proyecto.estado_upm', 3)
                    ->where('asignacion_upm_usuario.usuario_id', $user)
                    ->where('asignacion_upm_usuario.proyecto_id', $validateData['proyecto_id'])
                    ->first();
            }
            return response()->json([
                "total" => $total->cant,
                "finalizados" => $finished->cant,
                "progreso" => $inProgress->cant,
                "total_proyecto" => $totalProject,
                "total_finalizados" => $finishedTotal
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @param $request obtiene los datos enviados del frontend el formato json
     * Funcion para obtener los departamentos que estan en un proyecto
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartmentsProject(Request $request)
    {
        try {
            $validateData = $request->validate([
                "proyecto_id" => 'required|int'
            ]);
            $user = $request->user()->id; //id del usuario autenticado
            $rolUser = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol del usuario
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('asignacion_rol_usuario.usuario_id', $user)
                ->where('rol.estado', 1)
                ->first();
            $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol mas alto de un proyecto
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->orderBy('rol.jerarquia', 'DESC')
                ->first();
            $data = "";
            if ($rolUser->jerarquia == $rolMayor->jerarquia) { //Verificar si el usuario pertenece al rol mas alto del proyecto
                $data = Departamento::selectRaw('departamento.id,departamento.nombre,departamento.url AS image')
                    ->join('upm', 'upm.departamento_id', 'departamento.id')
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                    ->groupBy('departamento.id')
                    ->get();
            } else {
                $data = Departamento::selectRaw('departamento.id,departamento.nombre,departamento.url AS image')
                    ->join('upm', 'upm.departamento_id', 'departamento.id')
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->join('asignacion_upm_usuario', 'asignacion_upm_usuario.upm_id', 'upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_usuario.usuario_id', $user)
                    ->where('asignacion_upm_usuario.proyecto_id', $validateData['proyecto_id'])
                    ->groupBy('departamento.id')
                    ->get();
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @param $request obtiene los datos enviados del frontend en formato JSON
     * Function para obtener los datos especifico de un proyecto
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDataDeparments(Request $request)
    {
        try {
            $validateData = $request->validate([
                "proyecto_id" => 'required|int',
                "departamento_id" => 'required|int'
            ]);
            $user = $request->user()->id; //id del usuario autenticado
            $rolUser = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol del usuario
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('asignacion_rol_usuario.usuario_id', $user)
                ->where('rol.estado', 1)
                ->first();
            $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol mayor de un proyecto
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->orderBy('rol.jerarquia', 'DESC')
                ->first();
            $data = [];
            if ($rolUser->jerarquia == $rolMayor->jerarquia) { //Verificar que el rol del usuario sea el mayor del proyecto
                $assignment = Departamento::select('departamento.id', 'departamento.nombre')
                    ->join('upm', 'upm.departamento_id', 'departamento.id')
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                    ->where('departamento.id', $validateData['departamento_id'])
                    ->first();
                if (isset($assignment)) {
                    $dto = $this->getProgressDepartments($validateData['departamento_id'], $validateData['proyecto_id'], 0);
                    $log = $this->getLogDepartment($validateData['departamento_id'], $validateData['proyecto_id'],0);
                    $data = ["nombre" => $assignment->nombre, "total" => $dto['total'], "progreso" => $dto['progress'], "finished" => $dto['finished'], "data" => $log];
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "El proyecto no tiene asignado el departamento elegido"
                    ], 400);
                }
            } else if (isset($rolUser)) { //Verificar que el rol del usuario si exista
                $assignment = Departamento::select('departamento.id', 'departamento.nombre')
                    ->join('upm', 'upm.departamento_id', 'departamento.id')
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->join('asignacion_upm_usuario', 'asignacion_upm_usuario.upm_id', 'upm.id')
                    ->where('asignacion_upm_proyecto.proyecto_id', $validateData['proyecto_id'])
                    ->where('asignacion_upm_usuario.usuario_id', $user)
                    ->where('asignacion_upm_usuario.proyecto_id', $validateData['proyecto_id'])
                    ->where('departamento.id', $validateData['departamento_id'])
                    ->first();
                if (isset($assignment)) {
                    $dto = $this->getProgressDepartments($validateData['departamento_id'], $validateData['proyecto_id'], 1, $user);
                    $log = $this->getLogDepartment($validateData['departamento_id'], $validateData['proyecto_id'], 1, $user);
                    $data = ["nombre" => $assignment->nombre, "total" => $dto['total'], "progreso" => $dto['progress'], "finished" => $dto['finished'], "data" => $log];
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "Usted no tiene asignado el departamento elegido"
                    ], 400);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "No esta asignado al proyecto elegido"
                ], 400);
            }
            return response()->json($data, 200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Function que obtiene los progresos de un departamento en especifico
     * @param $departmentId id del departamento
     * @param $projectId id del proyecto
     * @param $type obtiene el tipo de consulta, o si es para el de mayor rol, 1 para el caso contrario
     * @param $user es igual a 0 por que en el caso del mayor rol no es necesario el id del usuario
     */
    public function getProgressDepartments(int $departmentId, int $projectId, int $type, int $user = 0)
    {
        try {
            $total = "";
            $inProgress = "";
            $finished = "";
            if ($type == 0) {
                $total = UPM::selectRaw('COUNT(upm.nombre) as cant')
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->where('upm.departamento_id', $departmentId)
                    ->where('asignacion_upm_proyecto.proyecto_id', $projectId)
                    ->where('asignacion_upm_proyecto.estado_upm', '!=', '4')
                    ->first();
                $inProgress = UPM::selectRaw('COUNT(upm.nombre) as cant')
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->where('upm.departamento_id', $departmentId)
                    ->where('asignacion_upm_proyecto.proyecto_id', $projectId)
                    ->where('asignacion_upm_proyecto.estado_upm', 2)
                    ->first();
                $finished = UPM::selectRaw('COUNT(upm.nombre) as cant')
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->where('upm.departamento_id', $departmentId)
                    ->where('asignacion_upm_proyecto.proyecto_id', $projectId)
                    ->where('asignacion_upm_proyecto.estado_upm', 3)
                    ->first();
            } else if ($type == 1) {
                $total = UPM::selectRaw('COUNT(upm.nombre) as cant')
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->join('asignacion_upm_usuario', 'asignacion_upm_usuario.upm_id', 'upm.id')
                    ->where('upm.departamento_id', $departmentId)
                    ->where('asignacion_upm_proyecto.proyecto_id', $projectId)
                    ->where('asignacion_upm_usuario.usuario_id', $user)
                    ->where('asignacion_upm_usuario.proyecto_id', $projectId)
                    ->first();
                $inProgress = UPM::selectRaw('COUNT(upm.nombre) as cant')
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->join('asignacion_upm_usuario', 'asignacion_upm_usuario.upm_id', 'upm.id')
                    ->where('upm.departamento_id', $departmentId)
                    ->where('asignacion_upm_proyecto.proyecto_id', $projectId)
                    ->where('asignacion_upm_proyecto.estado_upm', 2)
                    ->where('asignacion_upm_usuario.usuario_id', $user)
                    ->where('asignacion_upm_usuario.proyecto_id', $projectId)
                    ->first();
                $finished = UPM::selectRaw('COUNT(upm.nombre) as cant')
                    ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                    ->join('asignacion_upm_usuario', 'asignacion_upm_usuario.upm_id', 'upm.id')
                    ->where('upm.departamento_id', $departmentId)
                    ->where('asignacion_upm_proyecto.proyecto_id', $projectId)
                    ->where('asignacion_upm_proyecto.estado_upm', 3)
                    ->where('asignacion_upm_usuario.usuario_id', $user)
                    ->where('asignacion_upm_usuario.proyecto_id', $projectId)
                    ->first();
            }
            return ["total" => $total->cant, "progress" => $inProgress->cant, "finished" => $finished->cant];
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Function que obtiene los datos de upms de un departamento 
     *@param $departmentId id del departamento
     * @param $projectId id del proyecto
     * @param $type obtiene el tipo de consulta, o si es para el de mayor rol, 1 para el caso contrario
     * @param $user es igual a 0 por que en el caso del mayor rol no es necesario el id del usuario
     */
    public function getLogDepartment(int $departmentId, int $projectId, int $type, int $user = 0)
    {
        try {
            $asginaciones = [];
            if ($type == 0) {
                $asginaciones = AsignacionUpmProyecto::selectRaw('departamento.nombre as departamento,municipio.nombre as municipio,
                upm.nombre as upm,estado_upm.nombre as estado,upm.id,estado_upm.cod_estado')
                    ->join('upm', 'upm.id', 'asignacion_upm_proyecto.upm_id')
                    ->join('departamento', 'departamento.id', 'upm.departamento_id')
                    ->join('municipio', function ($join) {
                        $join->on('municipio.id', 'upm.municipio_id')->on('municipio.departamento_id', 'upm.departamento_id');
                    })
                    ->join('estado_upm', 'estado_upm.cod_estado', 'asignacion_upm_proyecto.estado_upm')
                    ->where('asignacion_upm_proyecto.proyecto_id', $projectId)
                    ->where('upm.departamento_id', $departmentId)
                    ->where('upm.estado', 1)
                    ->orderBy('departamento.nombre', 'ASC')
                    ->get();
            } else if ($type == 1) {
                $asginaciones = AsignacionUpmProyecto::selectRaw('departamento.nombre as departamento,municipio.nombre as municipio,
                    upm.nombre as upm,estado_upm.nombre as estado,upm.id,estado_upm.cod_estado')
                    ->join('upm', 'upm.id', 'asignacion_upm_proyecto.upm_id')
                    ->join('departamento', 'departamento.id', 'upm.departamento_id')
                    ->join('municipio', function ($join) {
                        $join->on('municipio.id', 'upm.municipio_id')->on('municipio.departamento_id', 'upm.departamento_id');
                    })
                    ->join('asignacion_upm_usuario', 'asignacion_upm_usuario.upm_id', 'upm.id')
                    ->join('estado_upm', 'estado_upm.cod_estado', 'asignacion_upm_proyecto.estado_upm')
                    ->where('upm.departamento_id', $departmentId)
                    ->where('asignacion_upm_proyecto.proyecto_id', $projectId)
                    ->where('asignacion_upm_usuario.proyecto_id', $projectId)
                    ->where('asignacion_upm_usuario.usuario_id', $user)
                    ->where('upm.estado', 1)
                    ->orderBy('departamento.nombre', 'ASC')
                    ->get();
            }
            return $asginaciones;
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }
}