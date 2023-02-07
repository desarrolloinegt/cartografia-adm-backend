<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionGrupo;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AsignacionGrupoController extends Controller
{
    
    public function store($validateData)
    {
        $grupo = Grupo::find($validateData['grupo_id']);
        $usuario = User::find($validateData['usuario_id']);
        if (isset($grupo) && isset($usuario)) {
            if ($grupo->estado == 1 && $usuario->estado_usuario == 1) {
                try {
                    $asignacion = AsignacionGrupo::create([
                        "usuario_id" => $validateData['usuario_id'],
                        "grupo_id" => $validateData['grupo_id']
                    ]);
                    return response()->json([
                        'status' => true,
                        'message' => 'Usuario asignado a Grupo correctamente'
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
                "grupo_id"=>'required|int',
                "usuarios"=>"required|array",
                "usuarios.*"=>'string'
            ]);
            $array = $validateData['usuarios'];
            $idGrupo = $validateData['grupo_id'];
            foreach ($array as $username) {
                try {
                    $user = User::where('username', $username)->first();
                    if(isset($user)){
                        $asignacion = AsignacionGrupo::create([
                            "usuario_id" => $user->id,
                            "grupo_id" => $idGrupo
                        ]);
                    }else{
                        array_push($errores, "El usuario $username no existe");
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
    public function asignarUsuarioAGrupo(Request $request)
    {
        $validateData = $request->validate([
            'username' => 'required|string',
            'grupo_id' => 'required|int'
        ]);
        $grupo = Grupo::find($validateData['grupo_id']);
        $user = User::where('username',$validateData['username'])->first();
        if (isset($grupo)) {
            if(isset($user)){
                $matchThese = ["usuario_id"=>$user->id,"grupo_id"=>$grupo->id];
                $asignacion = AsignacionGrupo::where($matchThese)->first();
                if(isset($asignacion)){
                    return response()->json([
                        'status' => true,
                        'message' => 'El usuario ya existe en este grupo'
                    ], 404);
                }else{
                    AsignacionGrupo::create([
                        "usuario_id" => $user->id,
                        "grupo_id" => $validateData['grupo_id']
                    ]);
                    return response()->json([
                        'status' => true,
                        'message' => 'Usuario agregado correctamente'
                    ], 200);
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


    public function eliminarUsuario(Request $request){
        try{
            $validateData = $request->validate([
                "grupo_id"=>'required|int',
                "username"=>'required|string'
            ]);
            $user = User::where('username',$validateData['username'])->first();
            if(isset($user) ){
                $matchThese = ["grupo_id"=>$validateData['grupo_id'],"usuario_id"=>$user->id];
                AsignacionGrupo::where($matchThese)->delete();
                return response()->json([
                    'status' => true,
                    'message' =>"Usuario eliminado del grupo"
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
    public function obtenerGrupoUsuarios($id)
    {
        try {
            $asginaciones = AsignacionGrupo::select('usuario.id','usuario.username')
                ->join('usuario', 'asignacion_grupo.usuario_id', 'usuario.id')
                ->join('grupo', 'asignacion_grupo.grupo_id', 'grupo.id')
                ->where('usuario.estado_usuario', 1)
                ->where('grupo.id',$id)
                ->where('usuario.estado_usuario',1)
                ->where('grupo.estado', 1)
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