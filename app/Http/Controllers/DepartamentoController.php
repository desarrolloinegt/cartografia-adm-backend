<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departamento;

class DepartamentoController extends Controller
{
    /**
     * Summary of obtenerDepartamentos
     * funcion que devuelve todos los departamentos guardados en la DB
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerDepartamentos()
    {
        $departamentos = Departamento::all();
        return response()->json($departamentos);
    }

    public function cargarDepartamentos(Request $request){
        try{
            $errores=[];
            $array=$request->all();
            foreach ($array as $departamento=>$value) {
                try{
                    Departamento::create([
                        "nombre"=>$value['nombre']
                    ]);
                }catch(\Throwable $th){
                    array_push($errores,$th->getMessage());
                }
            }
            return response()->json([
                "status"=>true,
                "message"=>"Departamentos creados",
                "errores"=>$errores
           ],200);
        }catch(\Throwable $th){
            return response()->json([
                "status"=>true,
                "message"=>$th->getMessage()
           ],500);
        }
    }
}