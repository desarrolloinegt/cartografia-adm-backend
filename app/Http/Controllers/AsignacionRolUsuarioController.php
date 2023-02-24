<?php

namespace App\Http\Controllers;

use App\Models\AsignacionRolUsuario;
use App\Models\AsignacionUpmUsuario;
use App\Models\Organizacion;
use App\Models\Proyecto;
use App\Models\Rol;
use Illuminate\Http\Request;
use App\Models\AsignacionGrupo;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AsignacionRolUsuarioController extends Controller
{
    
    public function store($validateData)
    {
        $rol = Rol::find($validateData['rol_id']);
        $usuario = User::find($validateData['usuario_id']);
        if (isset($rol) && isset($usuario)) {
            if ($rol->estado == 1 && $usuario->estado_usuario == 1) {
                try {
                    $asignacion = AsignacionRolUsuario::create([
                        "usuario_id" => $validateData['usuario_id'],
                        "rol_id" => $validateData['rol_id']
                    ]);
                    return response()->json([
                        'status' => true,
                        'message' => 'Usuario asignado a rol correctamente'
                    ], 200);
                } catch (\Throwable $th) {
                    return response()->json([
                        'status' => false,
                        'message' => $th->getMessage()
                    ], 500);
                }

            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Datos no disponibles'
                ], 401);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Datos no encontrados'
            ], 404);
        }
    }
    public function asignacionMasiva(Request $request)
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
            $proyecto=Proyecto::where('nombre',$validateData['proyecto'])->first() ;
            $idRol = $validateData['rol_id'];
            foreach ($array as $codigo_usuario) {
                try {
                    $user = User::where('codigo_usuario', $codigo_usuario)->first();
                    if(isset($user)){
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
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga un campos  entero y un arreglo de enteros
     * $grupo obtenemos la verificacion si el grupo existe o no
     * Para asignar nuevos usuarios al grupo eliminamos los anteriores y se crean de nuevo
     * y se llama a la funcion asignarGrupoUsuario(), esta se encarga de crear las nuevas asignaciones
     * @return \Illuminate\Http\JsonResponse
     */
    public function asignarUsuariosRol(Request $request)
    {
        $validateData = $request->validate([
            'codigo_usuario' => 'required|int',
            'rol_id' => 'required|int',
            'proyecto'=>'required|string'
        ]);
        
        $proyecto=Proyecto::where('nombre',$validateData['proyecto'])->first();
        $rol = Rol::find($validateData['rol_id']);
        $user = User::where('codigo_usuario',$validateData['codigo_usuario'])->first();
        if (isset($rol) && isset($proyecto)) {
            if(isset($user)){
                $matchTheseExisit = ["usuario_id"=>$user->id,"proyecto_id"=>$proyecto->id];
                $exist=AsignacionRolUsuario::where($matchTheseExisit)->first();
                if(isset($exist)){
                    $rolAsignado=Rol::find($exist->rol_id);
                    return response()->json([
                        'status' => true,
                        'message' => 'El usuario '.$user->codigo_usuario.' ya existe en este proyecto y esta asignado al rol: '.
                        $rolAsignado->nombre
                    ], 404);
                }else{
                    $matchThese = ["usuario_id"=>$user->id,"rol_id"=>$rol->id];
                    $asignacion = AsignacionRolUsuario::where($matchThese)->first();
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


    public function eliminarUsuarioRol(Request $request){
        try{
            $validateData = $request->validate([
                "rol_id"=>'required|int',
                "codigo_usuario"=>'required|int'
            ]);
            $user = User::where('codigo_usuario',$validateData['codigo_usuario'])->first();
            if(isset($user) ){
                $matchThese = ["rol_id"=>$validateData['rol_id'],"usuario_id"=>$user->id];
                $asignacion=AsignacionRolUsuario::where($matchThese)->first();
                if(isset($asignacion)){
                    AsignacionRolUsuario::where($matchThese)->delete();
                    AsignacionUpmUsuario::where('usuario_id',$user->id)->delete();
                    Organizacion::where('usuario_superior',$user->id)->delete();
                    Organizacion::where('usuario_inferior',$user->id)->delete();
                    
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
     * Con esta funcion se obtine una tabla con el grupo y los usuarios que tiene ese grupo
     * siempre que el grupo y usuarios esten activos y se agrupa los usuarios por el grupo
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerUsuariosRol($id)
    {
        try {
            $asginaciones = AsignacionRolUsuario::select('usuario.codigo_usuario','usuario.nombres','usuario.apellidos')
                ->join('usuario', 'asignacion_rol_usuario.usuario_id', 'usuario.id')
                ->join('rol', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->where('usuario.estado_usuario', 1)
                ->where('rol.id',$id)
                ->where('usuario.estado_usuario',1)
                ->where('rol.estado', 1)
                ->get();
            return response()->json($asginaciones,200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

}