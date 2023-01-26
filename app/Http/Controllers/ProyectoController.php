<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proyecto;
use App\Models\Encuesta;
use App\Models\AsignacionUpm;
use App\Http\Controllers\AsignacionUpmController;

class ProyectoController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $encuesta hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     * @return \Illuminate\Http\JsonResponse
     */
    public function crearProyecto(Request $request)
    {
        try {
            $validateData = $request->validate([
                'nombre' => 'required|string|unique:proyecto',
                'year' => 'required|max:4|min:4',
                'descripcion'=>'string',
                'encuesta_id' => 'required|int'
            ]);
            $encuesta = Encuesta::find($validateData['encuesta_id']);
            if (isset($encuesta)) {
                if ($encuesta->estado) {
                    $proyecto = Proyecto::create([
                        "nombre" => $validateData['nombre'],
                        "year" => $validateData['year'],
                        "encuesta_id" => $validateData['encuesta_id'],
                        "descripcion"=>$validateData['descripcion'],
                        "progreso" => 0,
                        "estado_proyecto" => 1
                    ]);
                    return response()->json([
                        'status' => true,
                        'message' => 'Proyecto creado correctamente',
                        'id' => $proyecto->id,
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Encuesta no disponible'
                    ], 201);
                }
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
     * @param $request recibe la peticion del frontend
     * A traves de ELOQUENT podemos usar el metodo select y seleccionar los campos con la condicion de que el estado
     * sea 1, es decir este activo      
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerProyectos()
    {
        $proyectos = Proyecto::select("proyecto.id", "proyecto.nombre", "proyecto.year", "encuesta.nombre AS encuesta", "proyecto.progreso","proyecto.descripcion")
            ->join('encuesta', 'proyecto.encuesta_id', 'encuesta.id')
            ->where("proyecto.estado_proyecto", 1)
            ->get();
        return response()->json($proyectos);
    }

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir requiee que la peticion contenga cuatro campos 
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar la encuesta que corresponde el id
     * 
     * Al obtener el proyecto podemos hacer uso de sus variables y asignarle el valor obtenido en el validateData
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL     
     * @return \Illuminate\Http\JsonResponse 
     */

    public function modificarProyecto(Request $request)
    {
        try{
            $validateData = $request->validate([
                'proyecto_id' => 'required|int',
                'nombre' => 'required|string',
                'year' => 'required|min:4|max:4',
                'encuesta_id' => 'required|int',
                'descripcion' => '',
            ]);
            $proyecto = Proyecto::find($validateData['proyecto_id']);
            if (isset($proyecto)) {
                    $proyecto->nombre = $validateData['nombre'];
                    $proyecto->year = $validateData['year'];
                    $proyecto->encuesta_id = $validateData['encuesta_id'];
                    $proyecto->descripcion = $validateData['descripcion'];
                    $proyecto->save();
                return response()->json([
                    'status' => true,
                    'message' => 'Proyecto modificado correctamente'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Dato no encontrado'
                ], 404);
            }
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }

    /**
     * @param $id recibe el id en la peticion GET
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar el proyecto que corresponde el id
     * 
     * Al obtener el proyecto podemos hacer uso de ELOQUENT y obtener su variable estado_proyecto y asignarle 0
     * El metodo save() de ELOQUENT es equivalente al UPDATE de SQL
     * @return \Illuminate\Http\JsonResponse
     */

    public function desactivarProyecto(int $id)
    {
        try {
            $proyecto = Proyecto::find($id);
            if (isset($proyecto)) {
                $proyecto->estado_proyecto = 0;
                $proyecto->save();
                return response()->json([
                    'status' => true,
                    'message' => 'Proyecto desactivado correctamente'
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

    /**
     * @param $id recibe el id en la peticion GET
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar el proyecto que corresponde el id
     * 
     * Al obtener el proyecto podemos hacer uso de ELOQUENT y obtener su variable progreso y asignarle 1
     * que significa que esta finalizado
     * El metodo save() de ELOQUENT es equivalente al UPDATE de SQL
     * @return \Illuminate\Http\JsonResponse  
     */
    public function finalizarProyecto(int $id)
    {
        try {
            $proyecto = Proyecto::find($id);
            if (isset($proyecto)) {
                $proyecto->progreso = 1;
                $proyecto->save();
                return response()->json([
                    'status' => true,
                    'message' => 'Proyecto Finalizado'
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