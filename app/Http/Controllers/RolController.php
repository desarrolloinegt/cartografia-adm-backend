<?php

namespace App\Http\Controllers;

use App\Models\AsignacionGrupo;
use App\Models\Rol;
use Illuminate\Http\Request;
use App\Models\Grupo;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga cuatro campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $grupo hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRol(Request $request)
    {
        try {
            $validateData = $request->validate([
                'nombre' => 'required|string|unique:rol',
                'descripcion' => '',
                'proyecto_id' => 'required|int',
            ]);
                Rol::create([
                "nombre" => $validateData['nombre'],
                "estado" => 1,
                "proyecto_id" => $validateData['proyecto_id'],
                'jerarquia' => 0,
                "descripcion" => $validateData['descripcion'] 
                ]);
            return response()->json([
                'status' => true,
                'message' => 'Rol creado correctamente'
            ], 200);
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
    public function obtenerRoles()
    {
        $roles = Rol::select("rol.id", "rol.nombre", "rol.descripcion", "rol.jerarquia", "proyecto.nombre AS proyecto", "proyecto.id AS proyecto_id")
            ->join('proyecto', 'rol.proyecto_id', 'proyecto.id')
            ->where("rol.estado", 1)
            ->get();
        return response()->json($roles);
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

    public function modificarRol(Request $request)
    {
        try {
            $validateData = $request->validate([
                'id' => 'required|int',
                'nombre' => 'required|string',
                'descripcion' => ''
            ]);
            $rol = Rol::find($validateData['id']);
            if (isset($rol)) {
                $rol->nombre = $validateData['nombre'];
                $rol->descripcion = $validateData['descripcion'];
                $rol->save();
                return response()->json([
                    'status' => true,
                    'message' => 'Rol modificado correctamente'
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
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar la encuesta que corresponde el id
     * 
     * Al obtener la encuesta podemos hacer uso de ELOQUENT con el metodo delete que hace referencia al DELETE de SQL
     * 
     * antes de eliminar la encuesta se verifica si la encuesta es usada en algun proyecto, si no es usada podra ser eliminada
     * de lo contraria no se podra eliminar  
     * @return \Illuminate\Http\JsonResponse    
     */

    public function desactivarRol(int $id)
    {
        try {
            $rol = Rol::find($id);
            if (isset($rol)) {
                $rol->estado = 0;
                $rol->save();
                return response()->json([
                    'status' => true,
                    'message' => 'Rol desactivado correctamente'
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

    public function modificarJerarquias(Request $request)
    {
        try {
            $array = $request->all();
            foreach ($array as $roles=>$item) {
                $rolFind = Rol::find($item['id']);
                if (isset($rolFind)) {
                    $rolFind->jerarquia = $item['jerarquia'];
                    $rolFind->save();
                }
            }
            return response()->json([
                "status" => true,
                "message" => "Orden actualizado"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function seleccionarRolesMenores(Request $request){
        try{
            $validateData = $request->validate([
                "proyecto_id"=>'required|int',
                "usuario_id"=>'required|int'
            ]);
            $rolMayor = Rol::select('rol.id','rol.nombre','rol.jerarquia')
                ->join('asignacion_rol_usuario','asignacion_rol_usuario.rol_id','rol.id')
                ->where('rol.proyecto_id',$validateData['proyecto_id'])
                ->where('asignacion_rol_usuario.usuario_id',$validateData['usuario_id'])
                ->where('rol.estado',1)
                ->orderBy('rol.jerarquia','DESC')
                ->first();
            $roles = Rol::select('rol.id','rol.nombre','rol.jerarquia')
                ->where('rol.proyecto_id',$validateData['proyecto_id'])
                ->where('rol.jerarquia','<',$rolMayor->jerarquia)
                ->orderBy('rol.jerarquia','DESC')
                ->get(); 

            return response()->json($roles,200);
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}