<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UPM;

class UPMController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $upm hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     * @return \Illuminate\Http\JsonResponse
     */
    public function crearUpm(Request $request)
    {
        try {
            $validateData = $request->validate([
                'descripcion' => 'required|string|max:200',
                'municipio_id' => 'required|int',
                'nombre' => '|required|string|unique:upm'
            ]);
            $upm = UPM::create([
                "nombre" => $validateData['nombre'],
                'descripcion' => $validateData['descripcion'],
                'municipio_id' => $validateData['municipio_id'],
                "estado" => 1
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Upm creado correctamente'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * A traves de ELOQUENT podemos usar el metodo select y seleccionar el id, nombre,descripcion y 
     * con el JOIN obtenemos el nombre del municipio del upm con la condicion de que el estado
     * sea 1, es decir este activo  
     * @return \Illuminate\Http\JsonResponse    
     */
    public function obtenerUpms()
    {
        $upms = UPM::select("upm.id", "upm.nombre", "upm.descripcion", "municipio.nombre AS municipio")
            ->join('municipio', 'municipio.id', 'upm.municipio_id')
            ->where("estado", 1)
            ->get();
        return response()->json($upms);
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir requiee que la peticion contenga cuatro campos 
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar el rol que corresponde el id
     * 
     * Al obtener el upm podemos hacer uso de sus variables y asignarle el valor obtenido en el validateData
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL 
     * @return \Illuminate\Http\JsonResponse     
     */

    public function modificarUpm(Request $request)
    {
        try {
            $validateData = $request->validate([
                'id' => 'required|int',
                'descripcion' => 'required|string|max:200',
                'municipio_id' => 'required|int',
                'nombre' => '|required|string|'
            ]);
            $upm = UPM::find($validateData['id']);
            if (isset($upm)) {
                $upm->nombre = $validateData['nombre'];
                $upm->descripcion = $validateData['descripcion'];
                $upm->municipio_id = $validateData['municipio_id'];
                $upm->save();
                return response()->json([
                    'status' => true,
                    'message' => 'UPM modificado correctamente'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Dato no encontrado'
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }

    /**
     * @param $id recibe el id en la peticion GET
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar el upm que corresponde el id
     * 
     * Al obtener el upm podemos hacer uso de sus variables y asignarle el valor 0 al estado del  upm
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL      
     * @return \Illuminate\Http\JsonResponse
     */

    public function desactivarUpm(int $id)
    {
        try {
            $upm = UPM::find($id);
            if (isset($upm)) {
                $upm->estado = 0;
                $upm->save();
                return response()->json([
                    'status' => true,
                    'message' => 'UPM desactivado correctamente'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'ERROR, dato no encontrado'
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}