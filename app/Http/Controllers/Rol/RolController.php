<?php

namespace App\Http\Controllers\Rol;

use App\Http\Controllers\Controller;
use App\Models\AsignacionRolUsuario;
use App\Models\AsignacionUpmUsuario;
use App\Models\Organizacion;
use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * Function para crear un rol
     * $validateData valida los campos, es decir require que la peticion contenga cuatro campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
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
     * Function para obtener todos los roles
     * @return \Illuminate\Http\JsonResponse     
     */
    public function getRoles()
    {
        try {
            $roles = Rol::select("rol.id", "rol.nombre", "rol.descripcion", "rol.jerarquia", "proyecto.nombre AS proyecto", "proyecto.id AS proyecto_id")
                ->join('proyecto', 'rol.proyecto_id', 'proyecto.id')
                ->where("rol.estado", 1)
                ->orderBy("rol.id", 'DESC')
                ->get();
            return response()->json($roles, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @param $request recibe la peticion del frontend
     * Function para modificar un rol
     * $validateData valida los campos, es decir requiee que la peticion contenga cuatro campos 
     * @return \Illuminate\Http\JsonResponse     
     */

    public function editRol(Request $request)
    {
        try {
            $validateData = $request->validate([
                'id' => 'required|int',
                'nombre' => 'required|string',
                'descripcion' => ''
            ]);
            $rol = Rol::find($validateData['id']); //Busca el rol por su id
            if (isset($rol)) { //Verifica que el rol exista
                $rol->nombre = $validateData['nombre']; //Modifica nombre
                $rol->descripcion = $validateData['descripcion']; //Modifca descripcion
                $rol->save(); //Metodo save equivalente a UPDATE de sql
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
     * Function que desactiva un rol a traves de su id
     * @return \Illuminate\Http\JsonResponse    
     */
    public function desactiveRol(int $id)
    {
        try {
            $rol = Rol::find($id); //Busca el rol a traves de su id
            if (isset($rol)) { //Verifica que el rol exista
                $rol->estado = 0; //Cambia estado a 0
                $rol->save(); // Metodo save equivalente a UPDATE de sql
                $usuariosRol = AsignacionRolUsuario::where('rol_id', $id)->get(); //Obtener todos los usuarios que existan en ese rol
                AsignacionRolUsuario::where('rol_id', $id)->delete(); //Eliminar todos los usuarios que existane en ese rol
                foreach ($usuariosRol as $asignment) {
                    AsignacionUpmUsuario::where('usuario_id', $asignment->usuario_id)->delete(); //Eliminar el usuario que tenga upms
                    Organizacion::where('usuario_superior', $asignment->usuario_id)->delete(); //Eliminar la asignacion si el usuario es superior
                    Organizacion::where('usuario_inferior', $asignment->usuario_id)->delete(); //Eliminar la asignacion si el usuario es inferior
                }
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

    /**
     * @param $request obtienen los datos enviados del frontend en formato JSON
     * Function que edita la jerarquia de los roles
     * @return \Illuminate\Http\JsonResponse    
     */
    public function editHierarchy(Request $request)
    {
        try {
            $array = $request->all(); //Convertir en arreglo la data JSON
            foreach ($array as $roles => $item) {
                $rolFind = Rol::find($item['id']); // buscar el rol por su id
                if (isset($rolFind)) {
                    $rolFind->jerarquia = $item['jerarquia']; //Ingresar la nueva jerarquia
                    $rolFind->save(); //Metodo save equivalente a UPDATE sql
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

    /**
     * @param $request obtienen los datos enviados del frontend en formato JSON
     * Function que obtiene los roles menores al del usuario
     * @return \Illuminate\Http\JsonResponse 
     */
    public function getMinorRoles(Request $request)
    {
        try {
            $idUser = $request->user()->id; //id del usuario autenticado 
            $validateData = $request->validate([
                "proyecto_id" => 'required|int'
            ]);
            $rolMayor = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Rol mayor del usuario
                ->join('asignacion_rol_usuario', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->where('asignacion_rol_usuario.usuario_id', $idUser)
                ->where('rol.estado', 1)
                ->orderBy('rol.jerarquia', 'DESC')
                ->first();
            $roles = Rol::select('rol.id', 'rol.nombre', 'rol.jerarquia') //Roles menores del usuario
                ->where('rol.proyecto_id', $validateData['proyecto_id'])
                ->where('rol.jerarquia', '<', $rolMayor->jerarquia)
                ->orderBy('rol.jerarquia', 'DESC')
                ->where('rol.estado', 1)
                ->get();
            return response()->json($roles,200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}