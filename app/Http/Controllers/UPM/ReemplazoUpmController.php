<?php

namespace App\Http\Controllers\UPM;

use App\Http\Controllers\Controller;
use App\Models\ReemplazoUpm;

class ReemplazoUpmController extends Controller
{
    /**
     * @param $id obtiene el id del upm
     * Function para ver detalle de sustitucion de un upm
     * @return \Illuminate\Http\JsonResponse
     */
    public function seeDetails($id)
    {
        try{
            $replace=ReemplazoUpm::where('upm_anterior',$id);//buscar que dicho reemplazo exista
            if(isset($replace)){ //Vericia que si exista
                $response=ReemplazoUpm::select('usuario.codigo_usuario','upm.nombre','reemplazo_upm.fecha','reemplazo_upm.descripcion')
                    ->join('usuario','usuario.id','reemplazo_upm.usuario_id')
                    ->join('upm','upm.id','reemplazo_upm.upm_nuevo')
                    ->where('reemplazo_upm.upm_anterior',$id)
                    ->first();
                return response()->json($response,200);    
            }else{
                return response()->json([
                    'status' => false,
                    'message' =>"UPM no encontrado"
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
