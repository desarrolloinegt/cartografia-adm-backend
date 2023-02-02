<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UPM;

class UPMController extends Controller
{

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