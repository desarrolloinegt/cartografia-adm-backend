<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Models\AsignacionRolUsuario;
use App\Models\AsignacionUpmProyecto;
use App\Models\AsignacionUpmUsuario;
use App\Models\ControlProgreso;
use App\Models\Organizacion;
use App\Models\Rol;
use App\Models\UPM;
use App\Models\User;
use App\Models\Proyecto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CargaTrabajoController extends Controller
{
    /**
     * @param $request datos enviados del frontend en formato json
     * Function para asignar upms a usuarios
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignn(Request $request)
    {
        $idUser = $request->user()->id; //Id del usuario autenticado
        $errors = [];
        try {
            $array = $request->all(); //Convertir el json en un array
            $idProject = $array[0]['proyecto_id'];//Obtener el id del proyecto
            $errors = $this->verifiUpmAndUserExists($array);
            if (empty($errors)) {
                $errors = $this->verifyUserUpmProject($array); //Verificar si hay errores en que los usuarios y upms si existan en el proyecto
                if (empty($errors)) {
                    $errors = $this->verifyNotAssignmet($array);
                    if (empty($errors)) {
                        $rolUser = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia')//busca el rol del usuario
                            ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                            ->where('asignacion_rol_usuario.usuario_id', $idUser)
                            ->where('rol.estado', 1)
                            ->first();
                        $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia')//busca el rol mayor del proyecto
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
        DB::disconnect();
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
        DB::disconnect();
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
                ->where('asignacion_upm_usuario.proyecto_id',$value['proyecto_id'])
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
        DB::disconnect();
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
        DB::disconnect();
        return $errors;
    }

    public function createAssignmet($array, $asignador)
    {
        $fecha=new \DateTime("now",new \DateTimeZone('America/Guatemala'));
        foreach ($array as $key => $value) {
            try {
                $upm = UPM::where("nombre", $value['upm'])->first();
                $user = User::where('codigo_usuario', $value['codigo_usuario'])->first();
                AsignacionUpmUsuario::create([
                    "upm_id" => $upm->id,
                    "usuario_id" => $user->id,
                    "proyecto_id" => $value['proyecto_id'],
                    "usuario_asignador" => $asignador,
                    "fecha_asignacion"=>$fecha
                ]);
            } catch (\Throwable $th) {

            }
        }
    }

    /**
     * @param $request obtiene los datos enviados desde el frontend en formato JSON
     * function para obtener la lista de upms y su personal
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpmsPersonal(Request $request)
    {
        try {
            $idUser = $request->user()->id;//id del usuario autenticado
            $validateData = $request->validate([
                "proyecto_id" => "int|required"
            ]);
            $user = User::find($idUser);//busca el usuario por su id
            if (isset($user)) { //Verifica que el usuario exista
                $upms=AsignacionUpmUsuario::selectRaw('rol.nombre as rol,usuario.id ,CONCAT(usuario.codigo_usuario,\' \',
                usuario.nombres,\' \',usuario.apellidos) AS encargado,upm.nombre as upm')
                    ->join('upm','upm.id','asignacion_upm_usuario.upm_id')
                    ->join('usuario','usuario.id','asignacion_upm_usuario.usuario_id')
                    ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.usuario_id', 'usuario.id')
                    ->join('rol','rol.id','asignacion_rol_usuario.rol_id')
                    ->where('asignacion_upm_usuario.proyecto_id',$validateData['proyecto_id'])
                    ->where('asignacion_rol_usuario.proyecto_id',$validateData['proyecto_id'])
                    ->where('asignacion_upm_usuario.usuario_asignador',$idUser)
                    ->where('rol.proyecto_id',$validateData['proyecto_id'])
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

    /**
     * @param $request obtiene los datos enviados por el frontend el formato JSON
     * function para obtener los upms asignados a un usuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpmsAssigned(Request $request)
    {
        try {
            $validateData = $request->validate([
                "proyecto_id" => "int|required"
            ]);
            $userId=$request->user()->id;
            $rolMayor = AsignacionRolUsuario::select('rol.id', 'rol.nombre', 'rol.jerarquia')//Busca el rol mayor del proyecto
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->orderBy('rol.jerarquia', 'DESC')
                ->first();

            $rol = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Busca el rol del proyecto
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->where('asignacion_rol_usuario.usuario_id', $userId)
                ->where('rol.estado', 1)
                ->first();
            if ($rol->jerarquia == $rolMayor->jerarquia) {
                $upms = $this->getUpmsChief($validateData['proyecto_id']);
                return response()->json($upms, 200);
            } else {
                $upmss = $this->getUpmsEmployee($userId, $validateData['proyecto_id']);
                return response()->json($upmss, 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @param $id obtiene el id del proyecto
     * Function para obtener los upm siendo parte del rol mas alto del proyecto
     */
    public function getUpmsChief($id)
    {
        try {
            $upms = AsignacionUpmProyecto::select('departamento.nombre as departamento', 'municipio.nombre as municipio', 'upm.nombre as upm', 'estado_upm.nombre as estado', 'upm.id')
                ->join('upm', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                ->join('municipio',function ($join){
                    $join->on('municipio.id','upm.municipio_id')->on('municipio.departamento_id','upm.departamento_id');
                })
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

    /**
     * @param $usuario id del usuario
     * @param $proyecto id del proyecto
     * Function para obtener los upms asignados siendo parte de los roles superiores al mayor
     */
    public function getUpmsEmployee($userId, $projectId)
    {
        try {
            $upms = AsignacionUpmUsuario::select('departamento.nombre as departamento', 'municipio.nombre as municipio', 'upm.nombre as upm', 'estado_upm.nombre as estado', 'upm.id')
                ->join('upm', 'upm.id', 'asignacion_upm_usuario.upm_id')
                ->join('municipio',function ($join){
                    $join->on('municipio.id','upm.municipio_id')->on('municipio.departamento_id','upm.departamento_id');
                })
                ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                ->join('estado_upm', 'estado_upm.cod_estado', 'asignacion_upm_proyecto.estado_upm')
                ->join('departamento', 'departamento.id', 'municipio.departamento_id')
                ->where('asignacion_upm_usuario.usuario_id', $userId)
                ->where('asignacion_upm_usuario.proyecto_id', $projectId)
                ->where('asignacion_upm_proyecto.proyecto_id', $projectId)
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

    /**
     * @param $request obtiene los datos enviados por el frontend el formato JSON
     * function para obtener los upms asignados a el cartografo
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpmCartographer(Request $request)
    {
        try {
            $userId = $request->user()->id;//Id del usuario autenticado
            $validateData = $request->validate([
                "proyecto_id" => "int|required"
            ]);
            $upms = AsignacionUpmUsuario::select('departamento.nombre as departamento', 'municipio.nombre as municipio', 'upm.nombre as upm', 'estado_upm.nombre as estado'
            , 'upm.id','estado_upm.cod_estado')
                ->join('upm', 'upm.id', 'asignacion_upm_usuario.upm_id')
                ->join('municipio',function ($join){
                    $join->on('municipio.id','upm.municipio_id')->on('municipio.departamento_id','upm.departamento_id');
                })
                ->join('asignacion_upm_proyecto', 'asignacion_upm_proyecto.upm_id', 'upm.id')
                ->join('estado_upm', 'estado_upm.cod_estado', 'asignacion_upm_proyecto.estado_upm')
                ->join('departamento', 'departamento.id', 'upm.departamento_id')
                ->where('asignacion_upm_usuario.usuario_id', $userId)
                ->where('asignacion_upm_usuario.proyecto_id', $validateData['proyecto_id'])
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

    /**
     * @param $request obtiene los datos enviados por el frontend el formato JSON
     * function para obtener los upms asignados a el supervisor
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpmSupervisor(Request $request){
        try{
            $idUser = $request->user()->id; //id del usuario autenticado
            $validateData = $request->validate([
                "proyecto_id" => "int|required"
            ]);
            $data=[];
            $upms=AsignacionUpmUsuario::select('upm.id','upm.nombre as upm','estado_upm.nombre as estado','estado_upm.cod_estado')
                ->join('usuario','usuario.id','asignacion_upm_usuario.usuario_id')
                ->join('upm','upm.id','asignacion_upm_usuario.upm_id')
                ->join('asignacion_upm_proyecto','asignacion_upm_proyecto.upm_id','upm.id')
                ->join('estado_upm','estado_upm.cod_estado','asignacion_upm_proyecto.estado_upm')
                ->where('asignacion_upm_usuario.usuario_id',$idUser)
                ->where('asignacion_upm_usuario.proyecto_id',$validateData['proyecto_id'])
                ->where('asignacion_upm_proyecto.proyecto_id',$validateData['proyecto_id'])
                ->where('usuario.estado_usuario',1)
                ->get();
            foreach ($upms as $upm) {
                $assignment=AsignacionUpmUsuario::select("usuario.codigo_usuario","usuario.nombres","usuario.apellidos")
                    ->join('organizacion','organizacion.usuario_inferior','asignacion_upm_usuario.usuario_id')
                    ->join('usuario','usuario.id','asignacion_upm_usuario.usuario_id')
                    ->where('asignacion_upm_usuario.upm_id',$upm->id)
                    ->where('organizacion.usuario_superior',$idUser)
                    ->where('organizacion.proyecto_id',$validateData['proyecto_id'])
                    ->where('asignacion_upm_usuario.proyecto_id',$validateData['proyecto_id'])
                    ->first();
                if(isset($assignment)) {
                    $dto=["upm"=>$upm->upm,"estado"=>$upm->estado,"cod_estado"=>$upm->cod_estado,"codigo_usuario"=>$assignment->codigo_usuario,"nombres"=>$assignment->nombres,"apellidos"=>
                    $assignment->apellidos];
                    array_push($data,$dto);
                } else {
                    $dto=["upm"=>$upm->upm,"estado"=>$upm->estado,"cod_estado"=>$upm->cod_estado,"codigo_usuario"=>"","nombres"=>"","apellidos"=>""];
                    array_push($data,$dto);
                }  
            }
            return response()->json($data,200);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }


    /**
     * @param $request obtiene los datos enviados por el frontend el formato JSON
     * function para obtener los usuarios asignados al superisor
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCartographerSupervisor(Request $request){
        try{
            $validateData=$request->validate([
                "proyecto_id"=>"required|int"
            ]);
            $idUser=$request->user()->id; // id del usuario autenticado
            $users=Organizacion::select()
                ->join('usuario','usuario.id','organizacion.usuario_inferior')
                ->where('organizacion.usuario_superior',$idUser)
                ->where('organizacion.proyecto_id',$validateData['proyecto_id'])
                ->where('usuario.estado_usuario',1)->get();
            return response()->json($users,200);    
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @param $request obtiene los datos enviados por el frontend el formato JSON
     * function para modificar el cartografo encargado de upms
     * @return \Illuminate\Http\JsonResponse
     */
    public function modifyUpmCartographer(Request $request){
        try{
            $fecha=new \DateTime("now",new \DateTimeZone('America/Guatemala'));//Fecha para guardar logs
            $validateData = $request->validate([
                "proyecto_id" => "int|required",
                "usuario_nuevo"=>"required|int",
                "upm"=>"required|string"
            ]);
            $idUser=$request->user()->id; //id del usuario autenticado
            $upm=UPM::where("nombre",$validateData['upm'])->first(); //Busca el upm por su id
            $cartographer=User::where("codigo_usuario",$validateData['usuario_nuevo'])->first(); //busca el cartografo por su codigo
            $project=Proyecto::find($validateData['proyecto_id']); //Busca el proyecto por su id
            if(isset($upm) && isset($cartographer) && isset($project)){ 
                $matchThese=["usuario_superior"=>$idUser,"usuario_inferior"=>$cartographer->id];
                $assignmentUser=Organizacion::where($matchThese)->first();//Verifica que el usuario autenticado sea superior 
                if(isset($assignmentUser)){
                    $matchThese=["proyecto_id"=>$project->id,"upm_id"=>$upm->id,"usuario_asignador"=>$idUser];
                    $assignment=AsignacionUpmUsuario::where($matchThese)->first(); //Verifica si el upm ya esta asignado a un usuario
                    if(isset($assignment)){
                        AsignacionUpmUsuario::where($matchThese)->update(["usuario_id"=>$cartographer->id,"fecha_asignacion"=>$fecha]);
                        return response()->json([
                            "status" => true,
                            "message" => "Modificacion correcta"
                        ], 200);
                    } else{ //Si no es el caso crea un asignacion nueva
                        $matchThese=["proyecto_id"=>$project->id,"upm_id"=>$upm->id];
                        $assignment=AsignacionUpmUsuario::where($matchThese)->first();
                        if(isset($assignment)){
                            AsignacionUpmUsuario::create([
                                "upm_id" => $upm->id,
                                "usuario_id" => $cartographer->id,
                                "proyecto_id" => $project->id,
                                "usuario_asignador" => $idUser,
                                "fecha_asignacion"=>$fecha
                            ]);
                            return response()->json([
                                "status" => true,
                                "message" => "Modificacion correcta"
                            ], 200);
                        }
                        return response()->json([
                            "status" => false,
                            "message" => "Error al modificar"
                        ], 400);
                    }
                } else{
                    return response()->json([
                        "status" => false,
                        "message" => "Usted no tiene asignado el usuario"
                    ], 400);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Datos no encontrados"
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
     * @param $request obtiene los datos enviados por el frontend el formato JSON
     * function para inicializar la actualizacion de un upm
     * 
     */
    public function initActualization(Request $request){
        try{
            $fecha=new \DateTime("now",new \DateTimeZone('America/Guatemala'));//fecha para logs
            $validateData=$request->validate([
                "upm"=>"required|string",
                "proyecto_id"=>"required|int"
            ]);
            $upm=UPM::where("nombre",$validateData['upm'])->first(); //Busca el upm por su id
            $idUser=$request->user()->id; //id del usuario autenticado
            if(isset($upm)){ //Verifica que el upm exista
                $matchThese=["usuario_id"=>$idUser,"upm_id"=>$upm->id,"proyecto_id"=>$validateData['proyecto_id']];
                $assignment=AsignacionUpmUsuario::where($matchThese)->first(); //Verifica que la asignacion exista
                if(isset($assignment)){
                    $matchThese=["upm_id"=>$upm->id,"proyecto_id"=>$validateData['proyecto_id']];
                    AsignacionUpmProyecto::where($matchThese)->update(['estado_upm'=>2]); //Actualiza el estado a 2:Iniciado
                    ControlProgreso::create([ //Se guarda el log en la tabla progreso
                        "fecha"=>$fecha,
                        "proyecto_id"=>$validateData['proyecto_id'],
                        "usuario_id"=>$assignment->usuario_id,
                        "upm_id"=>$upm->id,
                        "estado_upm"=>2
                    ]);
                    return response()->json([
                        "status" => true,
                        "message" => "Actualizacion de upm iniciada"
                    ], 200);
                }
            } 
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }


    /**
     * @param $request obtiene los datos enviados por el frontend el formato JSON
     * function para finalizar la actualizacion de un upm
     * 
     */
    public function finishActualization(Request $request){
        try{
            $fecha=new \DateTime("now",new \DateTimeZone('America/Guatemala')); //Fecha para logs
            $validateData=$request->validate([
                "upm"=>"required|string",
                "proyecto_id"=>"required|int"
            ]);
            $upm=UPM::where("nombre",$validateData['upm'])->first(); //Busca el upm por su id
            $idUser=$request->user()->id; //id del usuario autenticado
            if(isset($upm)){ //Verifica que el upm exista
                $matchThese=["usuario_id"=>$idUser,"upm_id"=>$upm->id,"proyecto_id"=>$validateData['proyecto_id']];
                $assignment=AsignacionUpmUsuario::where($matchThese)->first(); //Verifica que la asignacion exista
                if(isset($assignment)){
                    $matchThese=["upm_id"=>$upm->id,"proyecto_id"=>$validateData['proyecto_id']];
                    AsignacionUpmProyecto::where($matchThese)->update(['estado_upm'=>3]); //Actualiza el estado a 3: Finalizado
                    ControlProgreso::create([ //Crea un nuevo log en la tabla progreso
                        "fecha"=>$fecha,
                        "proyecto_id"=>$validateData['proyecto_id'],
                        "usuario_id"=>$assignment->usuario_id,
                        "upm_id"=>$upm->id,
                        "estado_upm"=>3
                    ]);
                    return response()->json([
                        "status" => true,
                        "message" => "Actualizacion de upm Finalizada"
                    ], 200);
                }
            }
        } catch(\Throwable $th){
            return response()->json([
                "status" => false,
                "message" => $th->getMessage()
            ], 500);
        }
    }
}