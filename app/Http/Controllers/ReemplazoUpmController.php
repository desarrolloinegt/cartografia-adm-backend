<?php

namespace App\Http\Controllers;

use App\Models\ReemplazoUpm;
use Illuminate\Http\Request;

class ReemplazoUpmController extends Controller
{
    public function verDetalle($id)
    {
        try{
            $reemplazo=ReemplazoUpm::where('upm_anterior',$id);
            if(isset($reemplazo)){
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
