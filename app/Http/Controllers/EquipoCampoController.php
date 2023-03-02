<?php

namespace App\Http\Controllers;

use App\Models\EquipoCampo;
use App\Models\Organizacion;
use App\Models\Proyecto;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipoCampoController extends Controller
{
    public function createTeams(Request $request)
    {
        try {
            $idUser = $request->user()->id;
            $array = $request->all();
            foreach ($array as $key => $value) {
                $user = User::where("codigo_usuario", $value['codigo_supervisor'])->first();
                if (isset($user)) {
                    $vehiculo = Vehiculo::where('placa', $value['placa'])->first();
                    $matchThese = ["supervisor" => $user->id, "proyecto_id" => $value['proyecto_id']];
                    $assignment = EquipoCampo::where($matchThese)->first();
                    $matchThese = ["usuario_superior" => $idUser, "usuario_inferior" => $user->id];
                    $userAssigned = Organizacion::where($matchThese)->first();
                    if (!isset($assignment) && isset($vehiculo) && isset($userAssigned)) {
                        EquipoCampo::create([
                            "supervisor" => $user->id,
                            "proyecto_id" => $value['proyecto_id'],
                            "usuario_asignador" => $idUser,
                            "vehiculo_id" => $vehiculo->id,
                            "descripcion"=>$value['descripcion']
                        ]);
                    } else if (!isset($assignment) && !isset($vehiculo)) {
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

    public function addTeam(Request $request)
    {
        try {
            $idUser = $request->user()->id;
            $validateData = $request->validate([
                "codigo_usuario" => 'required|int',
                "placa" => 'nullable|string',
                "proyecto_id" => 'required|int',
                "descripcion"=>''
            ]);
            $user = User::where("codigo_usuario", $validateData['codigo_usuario'])->first();
            if (isset($user)) {
                $matchThese = ["supervisor" => $user->id, "proyecto_id" => $validateData['proyecto_id']];
                $assignment = EquipoCampo::where($matchThese)->first();
                if (!isset($assignment)) {
                    $matchThese = ["usuario_superior" => $idUser, "usuario_inferior" => $user->id];
                    $userAssigned = Organizacion::where($matchThese)->first();
                    if (isset($userAssigned)) {
                        if ($validateData['placa']) {
                            $vehicle = Vehiculo::where('placa', $validateData['placa'])->first();
                            if (isset($vehicle)) {
                                $matchThese = ["vehiculo_id" => $vehicle->id, "proyecto_id" => $validateData['proyecto_id']];
                                $assignmentVehicule = EquipoCampo::where($matchThese)->first();
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

    public function editTeam(Request $request){
        try{
            $validateData=$request->validate([
                "usuario_id"=>'required|int',
                "proyecto_id"=>"required|int",
                "descripcion"=>"",
            ]);
            $idUser=$request->user()->id;
            $matchThese=["supervisor"=>$validateData['usuario_id'],"proyecto_id"=>$validateData['proyecto_id'],"usuario_asignador"=>$idUser];
            $assignment=EquipoCampo::where($matchThese)->first();
            if(isset($assignment)){
                EquipoCampo::where($matchThese)->update(["descripcion"=>$validateData['descripcion']]);
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
    public function deleteTeam(Request $request)
    {
        try {
            $validateData = $request->validate([
                "usuario_id" => 'required|int',
                "proyecto_id" => 'required|int'
            ]);
            $matchThese = ["supervisor" => $validateData['usuario_id'], "vehiculo_id" => $validateData['vehiculo_id']];
            $assignment = EquipoCampo::where($matchThese)->first();
            if (isset($assignment)) {
                EquipoCampo::where($matchThese)->delete();
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


    public function getTeams(Request $request)
    {
        try {
            $idUser = $request->user()->id;
            $validateData = $request->validate([
                "proyecto" => "required|string"
            ]);
            $project = Proyecto::where("nombre", $validateData['proyecto'])->first();
            $teams = EquipoCampo::select('equipo_campo.descripcion','usuario.id', 'usuario.codigo_usuario', 'vehiculo.placa', 'vehiculo.modelo', 'usuario.nombres', 'usuario.apellidos')
                ->join('usuario', 'usuario.id', 'equipo_campo.supervisor')
                ->leftJoin('vehiculo', 'vehiculo.id', 'equipo_campo.vehiculo_id')
                ->where('equipo_campo.usuario_asignador', $idUser)
                ->where('usuario.estado_usuario', 1)
                ->where('equipo_campo.proyecto_id', $project->id)
                ->get();
            return response()->json($teams, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getUsersTeam(Request $request)
    {
        try {
            $idUser = $request->user()->id;
            $validateData = $request->validate([
                "supervisor" => 'required|int',
                "proyecto_id" => 'required|int'
            ]);
            $users = Organizacion::select('usuario.codigo_usuario', 'usuario.nombres', 'usuario.apellidos', 'usuario.id')
                ->join('usuario', 'usuario.id', 'organizacion.usuario_inferior')
                ->where('organizacion.usuario_superior', $validateData['supervisor'])
                ->where('organizacion.usuario_asignador', $idUser)
                ->get();
            return response()->json($users, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function modifyVehicle (Request $request){
        try{
            $validateData=$request->validate([
                "usuario_id"=>'required|int',
                "proyecto_id"=>"required|int",
                "placa_nueva"=>"string|required",
                "placa_anterior"=>"string|required"
            ]);
            $idUser=$request->user()->id;
            $newVehicle=Vehiculo::where("placa",$validateData['placa_nueva'])->first();
            if(isset($newVehicle)){
                $matchThese=["proyecto_id"=>$validateData['proyecto_id'],"vehiculo_id"=>$newVehicle->id];
                $assignment=EquipoCampo::where($matchThese)->first();
                if(!isset($assignment)){
                    $vehicle=Vehiculo::where("placa",$validateData['placa_anterior'])->first();
                    $matchThese=["proyecto_id"=>$validateData['proyecto_id'],"vehiculo_id"=>$vehicle->id,"supervisor"=>$validateData['usuario_id'],"usuario_asignador"=>$idUser];
                    $team=EquipoCampo::where($matchThese)->first();
                    if(isset($team)){
                        EquipoCampo::where($matchThese)->update(["vehiculo_id"=>$newVehicle->id]);
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

    public function assignVehicle(Request $request){
        try{
            $validateData=$request->validate([
                "usuario_id"=>'required|int',
                "proyecto_id"=>"required|int",
                "placa"=>"string|required",
            ]);
            $idUser=$request->user()->id;
            $vehicle=Vehiculo::where("placa",$validateData['placa'])->first();
            if(isset($vehicle)){
                $matchThese=["proyecto_id"=>$validateData['proyecto_id'],"vehiculo_id"=>$vehicle->id];
                $assignment=EquipoCampo::where($matchThese)->first();
                if(!isset($assignment)){
                    $matchThese=["proyecto_id"=>$validateData['proyecto_id'],"supervisor"=>$validateData['usuario_id'],"usuario_asignador"=>$idUser];
                    $team=EquipoCampo::where($matchThese)->first();
                    if(isset($team)){
                        EquipoCampo::where($matchThese)->update(["vehiculo_id"=>$vehicle->id]);
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