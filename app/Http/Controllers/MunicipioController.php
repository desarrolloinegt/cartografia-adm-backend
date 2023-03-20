<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Municipio;

class MunicipioController extends Controller
{
    /**
     * Funcion que devuleve la tabla municipios, mostrando el nombre del departamento
     * al que pertenece  cada municipio
     * Summary of obtenerMunicipios
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerMunicipios()
    {
        $municipios = Municipio::select('municipio.id', 'municipio.nombre', 'departamento.nombre AS departamento')
            ->join('departamento', 'municipio.departamento_id', 'departamento.id')
            ->get();
        return response()->json($municipios);
    }

    public function cargarMunicipios(Request $request){
        try{
            $errores=[];
            $array=$request->all();
            foreach ($array as $departamento=>$value) {
                try{
                    Municipio::create([
                        "nombre"=>$value['nombre'],
                        "departamento_id"=>$value['departamento_id'],
                        "id"=>$value['id']
                    ]);
                }catch(\Throwable $th){
                    array_push($errores,$th->getMessage());
                }
            }
            return response()->json([
                "status"=>true,
                "message"=>"Municipios creados",
                "errores"=>$errores
           ],200);
        }catch(\Throwable $th){
            return response()->json([
                "status"=>true,
                "message"=>$th->getMessage(),
           ],500);
        }
    }
}