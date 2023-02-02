<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UPM;
use App\Models\Proyecto;
use App\Models\AsignacionUpm;
use Illuminate\Support\Facades\DB;

class AsignacionUpmController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campo entero y un array de enteros
     * Foreach para recorrer el array que es un array de ids de upms
     * $asignacion hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     * @return \Illuminate\Http\JsonResponse
     */
    public function asignacionMasiva(Request $request)
    {
        $errores = [];
        try {
            $validateData = $request->validate([
                'upms' => 'array|required',
                'umps.*' => 'string',
                'proyecto_id' => 'required|int'
            ]);
            $proyecto = Proyecto::find($validateData['proyecto_id']);
            $arrayUpms = $validateData['upms'];
            if (isset($proyecto)) {

                foreach ($arrayUpms as $upms) {
                    try {
                        $upm = UPM::where('nombre', $upms)->first();
                        if (isset($upm)) {
                            $asignacion = AsignacionUpm::create([
                                "upm_id" => $upm->id,
                                "proyecto_id" => $proyecto->id,
                                "estado_upm" => 1
                            ]);
                        }
                    } catch (\Throwable $th) {
                        array_push($errores, $th->getMessage());
                    }
                }

                return response()->json([
                    'status' => true,
                    'message' => 'UPMs asignados correctamente',
                    'errores' => $errores
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Proyecto no Econtrado"
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
     * Con esta funcion se obtine una tabla con el proyecto y los upms que tiene ese proyecto
     * siempre que el proyetco y upm esten activos y se agrupa los upms por el proyecto
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerUpmsProyecto($id)
    {
        try {
            $asginaciones = AsignacionUpm::select('departamento.nombre as departamento', 'municipio.nombre as municipio', 'upm.nombre as upm', 'estado_upm.nombre as estado', 'upm.id')
                ->join('upm', 'asignacion_upm.upm_id', 'upm.id')
                ->join('municipio', 'upm.municipio_id', 'municipio.id')
                ->join('departamento', 'departamento.id', 'municipio.departamento_id')
                ->join('estado_upm', 'estado_upm.cod_estado', 'asignacion_upm.estado_upm')
                ->where('asignacion_upm.proyecto_id', $id)
                ->where('upm.estado', 1)
                ->get();
            return response()->json($asginaciones, 200);
        } catch (\Throwable $th) {
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function eliminarAsignacion(Request $request)
    {
        $validateData = $request->validate([
            'upm_id' => 'required|int',
            'proyecto_id' => 'required|int'
        ]);
        $matchThese = ['upm_id' => $validateData['upm_id'], 'proyecto_id' => $validateData['proyecto_id']];
        $asignacion = AsignacionUpm::where($matchThese)
            ->first();
        if (isset($asignacion)) {
            AsignacionUpm::where($matchThese)
                ->delete();
            return response()->json([
                'status' => true,
                'message' => 'Asignacion de rol y permiso eliminada'
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Datos no encontrados'
            ], 404);
        }
    }


    public function sustituirUpm(Request $request)
    {
        try {
            $validateData = $request->validate([
                'proyecto_id' => 'int|required',
                'upm_anterior' => 'required|int',
                'upm_nuevo' => 'required|string'
            ]);
            $matchTheseAnterior = ['proyecto_id' => $validateData['proyecto_id'], 'upm_id' => $validateData['upm_anterior']];
            $asignacionAnterior = AsignacionUpm::where($matchTheseAnterior)->first();
            if (isset($asignacionAnterior)) {
                $upm = UPM::where('upm.nombre', $validateData['upm_nuevo'])->first();
                if (isset($upm)) {
                    $matchThese = ['proyecto_id' => $validateData['proyecto_id'], 'upm_id' => $upm->id];
                    $asignacionNueva = AsignacionUpm::where($matchThese)->first();
                    if (isset($asignacionNueva)) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Error, la UPM escrita ya existe en este proyecto'
                        ], 404);
                    }else{
                        $asignacion = AsignacionUpm::create([
                            'upm_id' => $upm->id,
                            'proyecto_id' => $validateData['proyecto_id'],
                            "estado_upm" => 1
                        ]);
                        $asignacionAnterior = AsignacionUpm::where($matchTheseAnterior)->update(['estado_upm'=>4]);
                        return response()->json([
                            'status' => true,
                            'message' => 'UPM sustituido'
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'UPM no existe, corrija el nombre'
                    ], 404);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'UPM que desea sustituir no existe'
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => true,
                'message' => $th->getMessage()
            ], 500);
        }

    }
}