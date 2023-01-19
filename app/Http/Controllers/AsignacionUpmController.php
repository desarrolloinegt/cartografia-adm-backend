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

    public function obtenerUpmsProyecto(){
        try{
            $upmArray=[];
            $permisos=[];
            $asginaciones= AsignacionUpm::selectRaw('proyecto.id,proyecto.nombre,proyecto.fecha,encuesta.nombre AS encuesta ,GROUP_CONCAT(upm.nombre) AS upms')
                ->join('proyecto','asignacion_upm.proyecto_id','proyecto.id')
                ->join('upm','asignacion_upm.upm_id','upm.id')
                ->join('encuesta','proyecto.encuesta_id','encuesta.id')
                ->where('proyecto.estado_proyecto',1)
                ->where('upm.estado',1)
                ->groupBy('asignacion_upm.proyecto_id')
                ->get();
            foreach ($asginaciones as $asginacion) {
                $asginacion->upms =explode(",", $asginacion->upms);
            }
            return response()->json($asginaciones);
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga dos campos y ambos sean enteros
     * $asignacion hace uso de ELOQUENT de laravel con el metodo where y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de eliminar en la DB con el metodo delete
     */
    public function eliminarAsignacion(Request $request){
        $validateData=$request->validate([
            'upm_id'=>'required|int',
            'proyecto_id'=>'required|int'
        ]);
        $matchThese = ['upm_id' =>$validateData['upm_id'], 'proyecto_id' => $validateData['proyecto_id']];
        $asignacion=AsignacionUpm::where($matchThese)
            ->first();
        if(isset($asignacion)){
            AsignacionUpm::where($matchThese)
            ->delete();
            return response()->json([
                'status'=>true,
                'message'=>'Asignacion de rol y permiso eliminada'
            ],200);
        } else{
            return response()->json([
                'status'=>true,
                'message'=>'Datos no encontrados'
            ],404);
        }    
    }
}
