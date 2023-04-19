<?php

namespace App\Http\Controllers\Rol;

use App\Http\Controllers\Controller;
use App\Models\AsignacionRolUsuario;
use App\Models\AsignacionUpmUsuario;
use App\Models\Organizacion;
use App\Models\Proyecto;
use App\Models\Rol;
use Illuminate\Http\Request;
use App\Models\User;

class AsignacionRolUsuarioController extends Controller
{
    
    /**
     * @param $request obtiene los datos enviados desde el frontend en formato JSON
     * function para asignar usuarios a un rol
     * @return \Illuminate\Http\JsonResponse
     */
    public function asignnRolUsers(Request $request)
    {
        $errores = [];
        try{
            $validateData=$request->validate([
                "rol_id"=>'required|int',
                "proyecto"=>'required|string',
                "usuarios"=>"required|array",
                "usuarios.*"=>'int'
            ]);
            $array = $validateData['usuarios'];
            $proyecto=Proyecto::where('nombre',$validateData['proyecto'])->first(); //Busca el proyecto por su nombre
            $idRol = $validateData['rol_id'];
            foreach ($array as $codigo_usuario) {
                try {
                    $user = User::where('codigo_usuario', $codigo_usuario)->first(); // busca el usuario por su codigo
                    if(isset($user)){ //Verifica que el usuario exista
                        $asignacion = AsignacionRolUsuario::create([
                            "usuario_id" => $user->id,
                            "rol_id" => $idRol,
                            "proyecto_id"=>$proyecto->id
                        ]);
                    }else{
                        array_push($errores, "El usuario $codigo_usuario no existe");
                    }
                } catch (\Throwable $th) {  
                }
            }
            return response()->json([
                "status"=>true,
                "message"=>"Usuarios asignados correctamente",
                "errores"=>$errores
            ], 200);
        }catch(\Throwable $th){
            return response()->json([
                "status"=>false,
                "message"=>$th->getMessage()
            ], 500);
        } 
    }

    /**
     * @param $request recibe la datos enviados desde el frontend en formato JSON
     * $validateData valida los campos, es decir require que la peticion contenga un campos  entero y un arreglo de enteros
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignUserRol(Request $request)
    {
        $validateData = $request->validate([
            'codigo_usuario' => 'required|int',
            'rol_id' => 'required|int',
            'proyecto'=>'required|string'
        ]); 
        $proyecto=Proyecto::where('nombre',$validateData['proyecto'])->first();//Busca el proyecto por su nombre
        $rol = Rol::find($validateData['rol_id']);//Busca el rol por su id
        $user = User::where('codigo_usuario',$validateData['codigo_usuario'])->first(); //Busca el usuario por su codigo de usuario
        if (isset($rol) && isset($proyecto)) { //Verifica que el rol y proyecto exista
            if(isset($user)){ //Verifica que el usuario exista
                $matchTheseExisit = ["usuario_id"=>$user->id,"proyecto_id"=>$proyecto->id];
                $exist=AsignacionRolUsuario::where($matchTheseExisit)->first(); //verifica si el usuario ya esta asignado a un rol del poroyecto
                if(isset($exist)){
                    $rolAsignado=Rol::find($exist->rol_id);
                    return response()->json([
                        'status' => true,
                        'message' => 'El usuario '.$user->codigo_usuario.' ya existe en este proyecto y esta asignado al rol: '.
                        $rolAsignado->nombre
                    ], 404);
                }else{
                    $matchThese = ["usuario_id"=>$user->id,"rol_id"=>$rol->id];
                    $asignacion = AsignacionRolUsuario::where($matchThese)->first();//Obtiene la asignacion del rol y verifica si el usuario ya esta
                    if(isset($asignacion)){
                        return response()->json([
                            'status' => true,
                            'message' => 'El usuario ya existe en este rol'
                        ], 404);
                    }else{
                        AsignacionRolUsuario::create([
                            "usuario_id" => $user->id,
                            "rol_id" => $validateData['rol_id'],
                            "proyecto_id"=>$proyecto->id
                        ]);
                        return response()->json([
                            'status' => true,
                            'message' => 'Usuario agregado correctamente'
                        ], 200);
                    }
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'El usuario ingresado no existe'
                ], 404);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Dato no encontrado'
            ], 404);
        }
    }


    /**
     * @param $request obtiene los datos enviados por el frontend en formato JSON
     * function para eliminar un usuario de un rol
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUserRol(Request $request){
        try{
            $validateData = $request->validate([
                "rol_id"=>'required|int',
                "codigo_usuario"=>'required|int'
            ]);
            $user = User::where('codigo_usuario',$validateData['codigo_usuario'])->first();//Busca el usuario por su codigo
            $rol=Rol::find($validateData['rol_id']);
            if(isset($user) && isset($rol) ){
                $matchThese = ["rol_id"=>$validateData['rol_id'],"usuario_id"=>$user->id];
                $asignacion=AsignacionRolUsuario::where($matchThese)->first();//Busca la asignacion del usuario con el rol
                if(isset($asignacion)){
                    AsignacionRolUsuario::where($matchThese)->delete();//Eliminar la asignacion del rol con el usuario
                    $matchThese=['usuario_id'=>$user->id,"proyecto_id"=>$rol->proyecto_id];
                    AsignacionUpmUsuario::where($matchThese)->delete();//Eliminar el usuario y sus upms asignada
                    $matchThese=['usuario_superior'=>$user->id,"proyecto_id"=>$rol->proyecto_id];
                    Organizacion::where($matchThese)->delete(); //Eliminar donde el usuario sea superior
                    $matchThese=['usuario_inferior'=>$user->id,"proyecto_id"=>$rol->proyecto_id];
                    Organizacion::where($matchThese)->delete();//Elimina donde el usuario sea inferior
                }
                return response()->json([
                    'status' => true,
                    'message' =>"Usuario eliminado del rol"
                ], 200); 
            }else{
                return response()->json([
                    'status' => false,
                    'message' =>"Datos no encontrado"
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
     * @param $id id del rol que se desea obtener los usuario
     * Function para obtener los usuarios del rol
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersRol($id)
    {
        try {
            $assignment = AsignacionRolUsuario::select('usuario.codigo_usuario','usuario.nombres','usuario.apellidos')
                ->join('usuario', 'asignacion_rol_usuario.usuario_id', 'usuario.id')
                ->join('rol', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('usuario.estado_usuario', 1)
                ->where('rol.id',$id)
                ->where('usuario.estado_usuario',1)
                ->where('rol.estado', 1)
                ->get();
            return response()->json($assignment,200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

}