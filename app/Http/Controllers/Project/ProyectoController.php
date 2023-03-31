<?php

namespace App\Http\Controllers\Project;
use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;
use App\Models\Proyecto;
use App\Models\Encuesta;

class ProyectoController extends Controller
{
    /**
     * @param $request recibe la peticion con los datos enviados desde el frontend
     * Function para crear un proyecto
     * $validateData valida los campos, es decir require que la peticion contenga un campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * @return \Illuminate\Http\JsonResponse
     */
    public function createProject(Request $request)
    {
        try {
            $validateData = $request->validate([
                'nombre' => 'required|string|unique:proyecto',
                'year' => 'required|max:4|min:4',
                'descripcion'=>'',
                'encuesta_id' => 'required|int'
            ]);
            $encuesta = Encuesta::find($validateData['encuesta_id']); //busca la encuesta por su id
            if (isset($encuesta)) { //Veririca que la encueseta exista
                if ($encuesta->estado==1) {//Verifica que la encuesta este activa
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
     * Function para obtener todos los proyectos
     * A traves de ELOQUENT podemos usar el metodo select y seleccionar los campos con la condicion de que el estado
     * sea 1, es decir este activo      
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjects()
    {
        $proyectos = Proyecto::select("proyecto.id", "proyecto.nombre", "proyecto.year", "encuesta.nombre AS encuesta", "proyecto.progreso","proyecto.descripcion")
            ->join('encuesta', 'proyecto.encuesta_id', 'encuesta.id')
            ->where("proyecto.estado_proyecto", 1)
            ->get();
        return response()->json($proyectos);
    }

    /**
     * @param $request recibe los datos enviados del  frontend
     * Function para modificar un proyecto
     * $validateData valida los campos, es decir requiee que la peticion contenga cuatro campos 
     * @return \Illuminate\Http\JsonResponse 
     */
    public function editProject(Request $request)
    {
        try{
            $validateData = $request->validate([
                'proyecto_id' => 'required|int',
                'nombre' => 'required|string',
                'year' => 'required|min:4|max:4',
                'encuesta_id' => 'required|int',
                'descripcion' => '',
            ]);
            $proyecto = Proyecto::find($validateData['proyecto_id']); //busca el proyecto por su id
            if (isset($proyecto)) { //Verifica que el proyecto exista
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
     * Function para desactivar un proyecto
     * @return \Illuminate\Http\JsonResponse
     */
    public function desactiveProject(int $id)
    {
        try {
            $proyecto = Proyecto::find($id); //Busca el proyecto por su id
            if (isset($proyecto)) { //Verifica que el proyecto exista
                $proyecto->estado_proyecto = 0; //Cambia de estado
                $proyecto->save(); //Metodo save equivalente al UPDATE de sql
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
     * Function para finalizar un proyecto
     * @return \Illuminate\Http\JsonResponse  
     */
    public function finishProject(int $id)
    {
        try {
            $proyecto = Proyecto::find($id);//Busca el proyecto por su id
            if (isset($proyecto)) { //Verifica que el proyecto exista
                $proyecto->progreso = 1; //Cambiar a 1 el progres
                $proyecto->save(); //Metodo save equivalente a UPDATE de sql
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

    /**
     * @param $proyecto obtieene el nombre del proyecto
     * function para obtener roles por proyecto
     * @return \Illuminate\Http\JsonResponse  
     */
    public function getRolesProject($proyecto){
        try{
            $asignments = Rol::select('rol.id','rol.nombre', 'jerarquia','rol.descripcion','rol.proyecto_id')
                ->join('proyecto','proyecto.id','rol.proyecto_id')
                ->where('proyecto.nombre',$proyecto)
                ->where('proyecto.estado_proyecto',1)
                ->where('rol.estado',1)
                ->orderBy('rol.jerarquia','DESC')
                ->get();
            return response()->json($asignments,200); 
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

     /**
     * @param $project obtiene el nombre del proyecto
     * function para obtener el id del proyecto
     * @return \Illuminate\Http\JsonResponse  
     */
    public function getProjectId($project){
        try{
            $result = Proyecto::select('id')
                ->where('proyecto.nombre',$project)
                ->where('proyecto.estado_proyecto',1)
                ->first();
            if(isset($result)){
                return response()->json($result->id,200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Proyecto no encontrado"
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