<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UPM;
use App\Models\Proyecto;
use App\Models\AsignacionUpm;
class AsignacionUpmController extends Controller
{
    public function asignacionMasiva(Request $request){
        try{
            $validateData=$request->validate([
                'upm_id'=>'required|int',
                'proyectos'=>'array|required',
                'proyectos.*'=>'int'
            ]);
            $upm=UPM::find($validateData['upm_id']);
            $arrayProyectos=$validateData['proyectos'];
            if(isset($upm)){
                foreach($arrayProyectos as $proyecto){
                    $asignacion=AsignacionUpm::create([
                        "upm_id"=>$upm->id,
                        "proyecto_id"=>$proyecto
                    ]);             
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Upm no Econtrado"
                ], 404);
            }   
            return response()->json([
                'status'=>true,
                'message'=>'UPMs asignados correctamente'
            ],200);  
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
