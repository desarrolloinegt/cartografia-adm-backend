<?php

namespace App\Http\Controllers;

use App\Models\EquipoCampo;
use App\Models\Organizacion;
use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipoCampoController extends Controller
{
    public function createTeams(Request $request){
        try{
            $idUser=$request->user()->id;
            $validateData=$request->validate([
                "proyecto_id"=>'required|int',
                "supervisores"=>'required|array',
                "supervisores.*"=>'int',
            ]);

            $project=Proyecto::find($validateData['proyecto_id']);
            $users=$validateData['supervisores'];
            if(isset($project)){
                foreach ($users as $codigoUsuario) {
                    $user=User::where("codigo_usuario",$codigoUsuario)->first();;
                    $assignmentOrganization=Organizacion::where("usuario_superior",$user->id)->first();
                    if(isset($assignmentOrganization)){
                        EquipoCampo::create([
                            "supervisor"=>$user->id,
                            "proyecto_id"=>$validateData['proyecto_id'],
                            "usuario_asignador"=>$idUser
                        ]);
                    }
                   DB::disconnect();
                }
                return response()->json([
                    'status' => true,
                    'message' => "Equipo creado"
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function deleteTeam(Request $request){
        try{
            $validateData=$request->validate([
                "usuario_id"=>'required|int',
                "proyecto_id"=>'required|int'
            ]);
            $matchThese=["supervisor"=>$validateData['usuario_id'],"vehiculo_id"=>$validateData['vehiculo_id']];
            $assignment=EquipoCampo::where($matchThese)->first();
            if(isset($assignment)){
                EquipoCampo::where($matchThese)->delete();
                return response()->json([
                    'status' => true,
                    'message' => "Equipo eliminado"
                ], 200);
            }
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }  
    } 


    public function getTeams(Request $request){
        try{
            $idUser=$request->user()->id;
            $validateData=$request->validate([
                "proyecto"=>"required|string"
            ]);
            $project=Proyecto::where("nombre",$validateData['proyecto'])->first();
            $teams=EquipoCampo::select('usuario.id','usuario.nombres','usuario.apellidos')
                ->join('usuario','usuario.id','equipo_campo.supervisor')
                ->where('equipo_campo.usuario_asignador',$idUser)
                ->where('usuario.estado_usuario',1)
                ->where('equipo_campo.proyecto_id',$project->id)
                ->get();
            return response()->json($teams,200);
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function getUsersTeam(Request $request){
        try{
            $idUser=$request->user()->id;
            $validateData=$request->validate([
                "supervisor"=>'required|int',
                "proyecto_id"=>'required|int'
            ]);
            $users=Organizacion::select('usuario.codigo_usuario','usuario.nombres','usuario.apellidos','usuario.id')
                ->join('usuario','usuario.id','organizacion.usuario_inferior')
                ->where('organizacion.usuario_superior',$validateData['supervisor'])
                ->where('organizacion.usuario_asignador',$idUser)
                ->get();
            return response()->json($users,200);
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
