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
                'upms'=>'array|required',
                'umps.*'=>'int',
                'proyecto_id'=>'required|int'
            ]);
            $proyecto=Proyecto::find($validateData['proyecto_id']);
            $arrayUpms=$validateData['upms'];
            if(isset($proyecto)){
                foreach($arrayUpms as $upm){
                    $asignacion=AsignacionUpm::create([
                        "upm_id"=>$upm,
                        "proyecto_id"=>$proyecto->id
                    ]);             
                }
                return response()->json([
                    'status'=>true,
                    'message'=>'UPMs asignados correctamente'
                ],200); 
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Upm no Econtrado"
                ], 404);
            }    
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
