<?php

namespace App\Http\Controllers\Work;

use App\Http\Controllers\Controller;
use App\Models\EquipoCampo;
use App\Models\Organizacion;
use App\Models\Proyecto;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Http\Request;

class EquipoCampoController extends Controller
{
    /**
     * @param $request obtiene datos del frontend en formato JSON
     * Function para crear equipos
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTeams(Request $request)
    {
        try {
            $idUser = $request->user()->id; //Id del usuario autenticado
            $array = $request->all(); //Convertir el JSON en arreglo
            foreach ($array as $key => $value) { //Recorrer todo el arreglo
                $user = User::where("codigo_usuario", $value['codigo_supervisor'])->first(); //Buscar el usuario enviado 
                if (isset($user)) { //Verificar que exista
                    $vehiculo = Vehiculo::where('placa', $value['placa'])->first(); //Buscar el vehiculo
                    $matchThese = ["supervisor" => $user->id, "proyecto_id" => $value['proyecto_id']];
                    $assignment = EquipoCampo::where($matchThese)->first(); //Verificar que el equipo existe
                    $matchThese = ["usuario_superior" => $idUser, "usuario_inferior" => $user->id];
                    $userAssigned = Organizacion::where($matchThese)->first(); //Verificar que el usuario sea empleado del autenticado
                    if (!isset($assignment) && isset($vehiculo) && isset($userAssigned)) { //Verificar que no se encuentre el equipo, que el vehiculo exista
                        // y que el usuario si este asigando
                        EquipoCampo::create([
                            "supervisor" => $user->id,
                            "proyecto_id" => $value['proyecto_id'],
                            "usuario_asignador" => $idUser,
                            "vehiculo_id" => $vehiculo->id,
                            "descripcion"=>$value['descripcion']
                        ]);
                    } else if (!isset($assignment) && !isset($vehiculo)) { //El vehiculo no viene en las peticiones
                        EquipoCampo::create([
                            "supervisor" => $user->id,
                            "proyecto_id" => $value['proyecto_id'],
                            "usuario_asignador" => $idUser,
                            "descripcion"=>$value['descripcion']
                        ]);
                    }
                }
            }
            return response()->json([
                'status' => true,
                'message' => "Equipos creados"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @param $request datos enviados desde el frontend en formato JSON
     * function para agregar un solo equipo
     * @return \Illuminate\Http\JsonResponse
     */
    public function addTeam(Request $request)
    {
        try {
            $idUser = $request->user()->id;//id del usuario autenticado
            $validateData = $request->validate([
                "codigo_usuario" => 'required|int',
                "placa" => 'nullable|string',
                "proyecto_id" => 'required|int',
                "descripcion"=>''
            ]);
            $user = User::where("codigo_usuario", $validateData['codigo_usuario'])->first(); //Id del usuario enviado 
            if (isset($user)) {
                $matchThese = ["supervisor" => $user->id, "proyecto_id" => $validateData['proyecto_id']]; //Verificar si existe este equipo
                $assignment = EquipoCampo::where($matchThese)->first();
                if (!isset($assignment)) { //Verificar que no exista el equipo
                    $matchThese = ["usuario_superior" => $idUser, "usuario_inferior" => $user->id];
                    $userAssigned = Organizacion::where($matchThese)->first(); //Verificar que el usuario autenticado sea superior al enviado en la peticion
                    if (isset($userAssigned)) {//Verificar asiganacion
                        if ($validateData['placa']) { //verificar que la placa venga en la peticion
                            $vehicle = Vehiculo::where('placa', $validateData['placa'])->first(); //Verificar que el vehiculo exista
                            if (isset($vehicle)) {
                                $matchThese = ["vehiculo_id" => $vehicle->id, "proyecto_id" => $validateData['proyecto_id']];
                                $assignmentVehicule = EquipoCampo::where($matchThese)->first(); //Verificar que el vehiculo no se encuentre en otro equipo
                                if (!isset($assignmentVehicule)) {
                                    EquipoCampo::create([
                                        "supervisor" => $user->id,
                                        "proyecto_id" => $validateData['proyecto_id'],
                                        "usuario_asignador" => $idUser,
                                        "vehiculo_id" => $vehicle->id
                                    ]);
                                    return response()->json([
                                        'status' => true,
                                        'message' => "Equipo creado"
                                    ], 200);
                                } else {
                                    return response()->json(['status' => false,
                                    'message' => "El vehiculo ya se encuentra en uso"], 404);
                                }
                            } else {
                                return response()->json(['status' => false,'message' => "Vehiculo no encontrado"], 404);
                            }
                        } else {
                            EquipoCampo::create([
                                "supervisor" => $user->id,
                                "proyecto_id" => $validateData['proyecto_id'],
                                "usuario_asignador" => $idUser,
                            ]);
                            return response()->json([
                                'status' => true,
                                'message' => "Equipo creado"
                            ], 200);
                        }
                    } else {
                        return response()->json(['status' => false,'message' => "No tiene asignado este usuario"], 400);
                    }
                } else {
                    return response()->json(['status' => false,
                        'message' => "Este usuario ya se encuentra asignado a un equipo de campo"], 400);
                }
            } else {
                return response()->json(['status' => false,'message' => "Usuario no encontrado"], 404);
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    /**
     * @param $request datos enviados desde el frontend en formato JSON
     * function para editar un  equipo
     * @return \Illuminate\Http\JsonResponse
     */
    public function editTeam(Request $request){
        try{
            $validateData=$request->validate([
                "usuario_id"=>'required|int',
                "proyecto_id"=>"required|int",
                "descripcion"=>"",
            ]);
            $idUser=$request->user()->id; //Id del usuario autenticado
            $matchThese=["supervisor"=>$validateData['usuario_id'],"proyecto_id"=>$validateData['proyecto_id'],"usuario_asignador"=>$idUser];
            $assignment=EquipoCampo::where($matchThese)->first();//Veriricar que el equipo exista
            if(isset($assignment)){
                EquipoCampo::where($matchThese)->update(["descripcion"=>$validateData['descripcion']]); //Modificar la descripcion
                return response()->json([
                    'status' => true,
                    'message' => "Equipo modificado"
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Equipo no encontrado"
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

     /**
     * @param $request datos enviados desde el frontend en formato JSON
     * function para eliminar un equipo
     * 
     */
    public function deleteTeam(Request $request)
    {
        try {
            $validateData = $request->validate([
                "usuario_id" => 'required|int',
                "proyecto_id" => 'required|int'
            ]);
            $matchThese = ["supervisor" => $validateData['usuario_id'], "vehiculo_id" => $validateData['vehiculo_id']];
            $assignment = EquipoCampo::where($matchThese)->first(); //Veriricar que el equipo exita
            if (isset($assignment)) {
                EquipoCampo::where($matchThese)->delete(); //Eliminar equipo
                return response()->json([
                    'status' => true,
                    'message' => "Equipo eliminado"
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


     /**
     * @param $request datos enviados desde el frontend en formato JSON
     * function para obtener los equipo
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeams(Request $request)
    {
        try {
            $idUser = $request->user()->id; //id del usuario autenticado
            $validateData = $request->validate([
                "proyecto" => "required|string"
            ]);
            $project = Proyecto::where("nombre", $validateData['proyecto'])->first(); //Buscar el proyecto 
            $teams = EquipoCampo::select('equipo_campo.descripcion','usuario.id', 'usuario.codigo_usuario', 'vehiculo.placa', 'vehiculo.modelo', 'usuario.nombres', 'usuario.apellidos')
                ->join('usuario', 'usuario.id', 'equipo_campo.supervisor')
                ->leftJoin('vehiculo', 'vehiculo.id', 'equipo_campo.vehiculo_id')
                ->where('equipo_campo.usuario_asignador', $idUser)
                ->where('usuario.estado_usuario', 1)
                ->where('equipo_campo.proyecto_id', $project->id)
                ->get(); //Obtener todos los equipos en que el usuario autenticado haya creado dichos equipos
            return response()->json($teams, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

     /**
     * @param $request datos enviados desde el frontend en formato JSON
     * function para obtener los usuarios de un equipo
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersTeam(Request $request)
    {
        try {
            $idUser = $request->user()->id;//id del usuario autenticado
            $validateData = $request->validate([
                "supervisor" => 'required|int',
                "proyecto_id" => 'required|int'
            ]);
            $users = Organizacion::select('usuario.codigo_usuario', 'usuario.nombres', 'usuario.apellidos', 'usuario.id')
                ->join('usuario', 'usuario.id', 'organizacion.usuario_inferior')
                ->where('organizacion.usuario_superior', $validateData['supervisor'])
                ->where('organizacion.usuario_asignador', $idUser)
                ->where('usuario.estado_usuario',1)
                ->where('organizacion.proyecto_id',$validateData['proyecto_id'])
                ->get(); //Obtener los uaurios de un equipo
            return response()->json($users, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


     /**
     * @param $request datos enviados desde el frontend en formato JSON
     * function para modificar un vehiculo de un equipo
     * @return \Illuminate\Http\JsonResponse
     */
    public function modifyVehicle (Request $request){
        try{
            $validateData=$request->validate([
                "usuario_id"=>'required|int',
                "proyecto_id"=>"required|int",
                "placa_nueva"=>"string|required",
                "placa_anterior"=>"string|required"
            ]);
            $idUser=$request->user()->id; //Id del usuario autenticado
            $newVehicle=Vehiculo::where("placa",$validateData['placa_nueva'])->first();//Buscar el vehiculo nuevo
            if(isset($newVehicle)){ //Verificar que el vehiculo nuevo exista
                $matchThese=["proyecto_id"=>$validateData['proyecto_id'],"vehiculo_id"=>$newVehicle->id];
                $assignment=EquipoCampo::where($matchThese)->first(); //Verificar que el vehiculo no este asigando a otro equipo
                if(!isset($assignment)){
                    $vehicle=Vehiculo::where("placa",$validateData['placa_anterior'])->first();
                    $matchThese=["proyecto_id"=>$validateData['proyecto_id'],"vehiculo_id"=>$vehicle->id,"supervisor"=>$validateData['usuario_id'],"usuario_asignador"=>$idUser];
                    $team=EquipoCampo::where($matchThese)->first(); //Verificar que el equipo tenga el vehiculo anterior
                    if(isset($team)){
                        EquipoCampo::where($matchThese)->update(["vehiculo_id"=>$newVehicle->id]);//Modificar vehiculo
                        return response()->json([
                            'status' => true,
                            'message' => "Vehiculo reemplazado"
                        ], 200);
                    }else{
                        return response()->json([
                            'status' => false,
                            'message' => "No se pudo reemplazar el vehiculo"
                        ], 400);
                    }
                    
                } else{
                    return response()->json([
                        'status' => false,
                        'message' => "El vehiculo ingresado ya se encuentra asignado a un equipo"
                    ], 400);   
                }
               
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Vehiculo no encontrado"
                ], 404);  
            }
            
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


     /**
     * @param $request datos enviados desde el frontend en formato JSON
     * function para asginar un  vehiculo a equipo
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignVehicle(Request $request){
        try{
            $validateData=$request->validate([
                "usuario_id"=>'required|int',
                "proyecto_id"=>"required|int",
                "placa"=>"string|required",
            ]);
            $idUser=$request->user()->id; //Id del usuario autenticado
            $vehicle=Vehiculo::where("placa",$validateData['placa'])->first();//buscar el vehiculo por su placa
            if(isset($vehicle)){//Verificar que el vehiculo exista
                $matchThese=["proyecto_id"=>$validateData['proyecto_id'],"vehiculo_id"=>$vehicle->id];
                $assignment=EquipoCampo::where($matchThese)->first(); //Verificar que el vehiculo no este en otro equipo
                if(!isset($assignment)){
                    $matchThese=["proyecto_id"=>$validateData['proyecto_id'],"supervisor"=>$validateData['usuario_id'],"usuario_asignador"=>$idUser];
                    $team=EquipoCampo::where($matchThese)->first();//Buscar el equipo
                    if(isset($team)){
                        EquipoCampo::where($matchThese)->update(["vehiculo_id"=>$vehicle->id]); //Asginar vehiculo
                        return response()->json([
                            'status' => true,
                            'message' => "Vehiculo asignado"
                        ], 200); 
                    } else {
                        return response()->json(['status' => false,
                            'message' => "No se pudo asignar el vehiculo"], 400); 
                    }
                } else {
                    return response()->json([ 'status' => false, 'message' => "El vehiculo ya esta asignado a un equipo"
                    ], 400); 
                }
                
            } else {
                return response()->json(['status' => false,'message' => "Vehiculo no encontrado" ], 404); 
            }
        } catch (\Throwable $th) {
            return response()->json(['status' => false,'message' => $th->getMessage() ], 500);
        }
    }
}