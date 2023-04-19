<?php

namespace App\Http\Controllers\DepartmentMunicipality;
use App\Http\Controllers\Controller;
use App\Models\Departamento;

class DepartamentoController extends Controller
{
    /**
     * Summary of obtenerDepartamentos
     * funcion que devuelve todos los departamentos guardados en la DB
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartaments()
    {
        $departamentos = Departamento::all();
        return response()->json($departamentos,200);
    }

    
    /**
     * @param $request obteiene los datos del frontend en formato json
     * Function para cargar los departamentos a la DB
     *------------------ Function desactivada, usada solo para desarrollo------------------
     */
    /*
    public function chargeDepartments(Request $request){
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
    }*/
}