<?php

namespace App\Http\Controllers\UPM;

use App\Http\Controllers\Controller;
use App\Models\AsignacionUpmProyecto;
use App\Models\ReemplazoUpm;
use Illuminate\Http\Request;
use App\Models\UPM;
use App\Models\Proyecto;

class AsignacionUpmProyectoController extends Controller
{
    /**
     * @param $request recibe los datos enviados desde el frontend en format JSON
     * Function para asignar upms a un proyecto
     * $validateData valida los campos, es decir require que la peticion contenga un campo entero y un array de enteros
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign(Request $request)
    {
        $errores = [];
        try {
            $validateData = $request->validate([
                'upms' => 'array|required',
                'umps.*' => 'string',
                'proyecto_id' => 'required|int'
            ]);
            $proyecto = Proyecto::find($validateData['proyecto_id']);//Buscar el proyecto en la db
            $arrayUpms = $validateData['upms'];
            if (isset($proyecto)) { //Verificar que el proyecto exista
                foreach ($arrayUpms as $upms) {
                    try {
                        $upm = UPM::where('nombre', $upms)->first();
                        if (isset($upm)) {
                            $asignacion = AsignacionUpmProyecto::create([ //Metodo create de eloquent que hacer referencia a insert de sql
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
                    'message' => 'Upm asignada',
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
     * @param $id id del proyecto que se desea obtener los upms
     * Funcion se obtine una tabla con el proyecto y los upms que tiene ese proyecto
     * siempre que el proyetco y upm esten activos y se agrupa los upms por el proyecto
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpmsProject($id)
    {
        try {
            $asginaciones = AsignacionUpmProyecto::selectRaw('departamento.nombre as departamento,municipio.nombre as municipio,
            upm.nombre as upm,estado_upm.nombre as estado,upm.id,estado_upm.cod_estado')
                ->join('upm','upm.id', 'asignacion_upm_proyecto.upm_id' )
                ->join('departamento', 'departamento.id', 'upm.departamento_id')
                ->join('municipio',function ($join){
                    $join->on('municipio.id','upm.municipio_id')->on('municipio.departamento_id','upm.departamento_id');
                }) //Join con doble condicion para evitar union de municipios con departamentos
                ->join('estado_upm', 'estado_upm.cod_estado', 'asignacion_upm_proyecto.estado_upm')
                ->where('asignacion_upm_proyecto.proyecto_id', $id)
                ->where('upm.estado', 1)
                ->orderBy('departamento.nombre','ASC')
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
    * @param $request obtiene los datos enviados por el frontend en formato JSON
    * Function para sustituir upm, requisoto el proyecto, upm anterior, upm nuevo, motivo
    *@return \Illuminate\Http\JsonResponse
    */
    public function replaceUpm(Request $request)
    {
        try {
            $fecha=new \DateTime("now",new \DateTimeZone('America/Guatemala'));//Fecha cuando se realiza la susticion
            $user=$request->user()->id; //se obtiene el id del usuario que esta realizando las acciones por medio de su antenticacion
            $validateData = $request->validate([
                'proyecto_id' => 'int|required',
                'upm_anterior' => 'required|int',
                'upm_nuevo' => 'required|string',
                'descripcion'=>'required|string'
            ]);
            $matchTheseAnterior = ['proyecto_id' => $validateData['proyecto_id'], 'upm_id' => $validateData['upm_anterior']];
            $asignacionAnterior = AsignacionUpmProyecto::where($matchTheseAnterior)->first();//Verifica que si exista la asignacion anterior
            if (isset($asignacionAnterior)) {
                $upm = UPM::where('upm.nombre', $validateData['upm_nuevo'])->first();//Obtener informacion del ump nuevo
                if (isset($upm)) { //Se verifica que si exista el nuevo upm
                    $matchThese = ['proyecto_id' => $validateData['proyecto_id'], 'upm_id' => $upm->id];
                    $asignacionNueva = AsignacionUpmProyecto::where($matchThese)->first();//Obtener informacion si el upm nuevo ya esta asignado
                    if (isset($asignacionNueva)) { //Si ya esta asignado, devolvera error 400
                        return response()->json([
                            'status' => true,
                            'message' => 'Error, la UPM escrita ya existe en este proyecto'
                        ], 400);
                    }else{
                        $asignacion = AsignacionUpmProyecto::create([
                            'upm_id' => $upm->id,
                            'proyecto_id' => $validateData['proyecto_id'],
                            "estado_upm" => 1
                        ]); //Reqliza la nuevo asigancion
                        $asignacionAnterior = AsignacionUpmProyecto::where($matchTheseAnterior)->update(['estado_upm'=>4]);//cambia el estado a sustituido
                        ReemplazoUpm::create([
                            "usuario_id"=>$user,
                            "descripcion"=>$validateData['descripcion'],
                            "upm_anterior"=>$validateData['upm_anterior'],
                            "upm_nuevo"=>$upm->id,
                            "fecha"=>$fecha
                        ]); //Guardamos el log de la sustitucion
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