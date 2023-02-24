<?php

namespace App\Http\Controllers;

use App\Models\EquipoCampo;
use App\Models\Organizacion;
use App\Models\Proyecto;
use Illuminate\Http\Request;

class EquipoCampoController extends Controller
{
    public function createTeam(Request $request){
        try{
            $validateData=$request->validate([
                "usuario_id"=>'required|int',
                "vehiculo_id"=>'int|nullable',
                "proyecto_id"=>'required|int'
            ]);

            $assignmentOrganization=Organizacion::where("codigo_superior",$validateData['usuario_id'])->first();
            $project=Proyecto::where("id",$validateData['proyecto_id'])->first();
            if(isset($assignmentOrganization) && isset($project)){
                EquipoCampo::create([
                    "supervisor"=>$validateData['usuario_id'],
                    "vehiculo_id"=>$validateData['vehiculo_id'],
                    "proyecto_id"=>$validateData['proyecto_id'],
                ]);
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
                "proyecto_id"=>"required|int"
            ]);
            $teams=EquipoCampo::select('usuario.id','usuario.nombres','usuario.apellidos','vehiculo.modelo')
                ->join('usuario','usuario.id','equipo_campo.supervisor')
                ->join('vehiculo','vehiculo.id','equipo_campo.vehiculo_id')
                ->where('vehiculo.estado',1)
                ->where('usuario.estado_usuario',1)
                ->where('equipo_campo.proyecto_id',$validateData['proyecto_id'])
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
                ->join('usuario','usuario.id','organizacion.codigo_inferior')
                ->where('organizacion.codigo_superior',$validateData['supervisor'])
                ->where('organizacion.usuario_asignador',$idUser);
            return response()->json($users,200);
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
