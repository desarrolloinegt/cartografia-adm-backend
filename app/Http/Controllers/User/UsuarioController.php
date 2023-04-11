<?php

namespace App\Http\Controllers\User;

use App\Models\AsignacionPoliticaUsuario;
use App\Models\Politica;
use App\Models\Rol;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permiso;
use App\Models\AsignacionRolUsuario;
use App\Models\Proyecto;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga siete campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validateData = $request->validate([
                'DPI' => 'required|max:13|min:13|unique:usuario',
                'nombres' => 'required|string|max:25',
                'apellidos' => 'required|string|max:25',
                'email' => 'required|email|min:13|unique:usuario',
                'codigo_usuario' => 'required|max:5',
                'password' => 'required|min:8',
                'telefono' => 'required|min:8',
                'descripcion' => ''
            ]);
            $user = User::create([
                //Metodo equivalente a INSERT de sql
                'DPI' => $validateData['DPI'],
                'nombres' => $validateData['nombres'],
                'apellidos' => $validateData['apellidos'],
                'email' => $validateData['email'],
                'codigo_usuario' => $validateData['codigo_usuario'],
                'estado_usuario' => 1,
                'password' => Hash::make($validateData['password']),
                'telefono' => $validateData['telefono'],
                'descripcion' => $validateData['descripcion']
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Usuario creado correctamente',
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
     * Function para el login del usuario
     * $validateData valida los campos, es decir require que la peticion contenga dos campos
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $validateData = $request->validate([
                'codigo_usuario' => 'required|int',
                'password' => 'required|string'
            ]);
            $user = User::where("codigo_usuario", $validateData['codigo_usuario'])->first(); //Busca el usuario por el codigo de usuario
            if (isset($user)) { //verificar que el usuario exista
                if ($user->estado_usuario == 1) { //Verificar que el usuario exista
                    if (Hash::check($validateData['password'], $user->password)) { //comparacion de contraseñas
                        Auth::login($user); //Login del usuario
                        /*if($user->tokens()->where('tokenable_id',$user->id)->exists()){
                        $user->tokens()->delete();
                        }*/
                        $token = $user->createToken('auth_token', ['*'])->plainTextToken; //Creacion del token Bearer
                        return response()->json([
                            "status" => true,
                            "token" => $token,
                            "id" => $user->id,
                            "usuario" => $user->nombres . " " . $user->apellidos,
                        ], 200);
                    } else {
                        return response()->json([
                            "status" => false,
                            "message" => "Contraseña incorrecta",
                        ], 401);
                    }
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "Usuario no disponible",
                    ], 401);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Usuario no encontrado",
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
     * @param $id recibe el id del usuario
     * Function para obtener los permisos del sistema que tenga el usuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissionSystem($id)
    {
        try {
            $permisosList = [];
            $permisosDirectos = AsignacionPoliticaUsuario::select('permiso.alias')
                ->join('usuario', 'usuario.id', 'asignacion_politica_usuario.usuario_id')
                ->join('politica', 'politica.id', 'asignacion_politica_usuario.politica_id')
                ->join('asignacion_permiso_politica', 'asignacion_permiso_politica.politica_id', 'politica.id')
                ->join('permiso', 'permiso.id', 'asignacion_permiso_politica.permiso_id')
                ->where('asignacion_politica_usuario.usuario_id', $id)
                ->where('permiso.estado', 1)
                ->where('permiso.permiso_sistema', 1)
                ->where('politica.estado', 1)
                ->get();
            foreach ($permisosDirectos as $permiso) {
                array_push($permisosList, $permiso->alias);
            }
            return response()->json($permisosList, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @param $request recibe la peticion del frontend
     * Atraves de $request podemos acceder al usuario, ver el token actual y eliminarlo
     * esto terminara la sesion
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                "message" => "Sesion terminada"
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }

    /**
     * @param $id recibe el id del usuario para evaluar
     * Function para obtener proyectos en los que este el usuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectsUser($id)
    {
        try {
            $projects = AsignacionRolUsuario::selectRaw('proyecto.nombre')
                ->join('usuario', "asignacion_rol_usuario.usuario_id", "usuario.id")
                ->join('rol', 'asignacion_rol_usuario.rol_id', 'rol.id')
                ->join('proyecto', 'proyecto.id', 'rol.proyecto_id')
                ->where('usuario.id', $id)
                ->where('proyecto.estado_proyecto',1)
                ->groupBy('proyecto.nombre')
                ->get();
            return response()->json($projects, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @param $request recibe los datos enviados por el frontend en formato JSON
     * Function para obtener los permisos en un proyecto
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPermissionProjectUser(Request $request)
    {
        try {
            $permisosList = array();
            $userId = $request->user()->id;
            $validateData = $request->validate([
                'proyecto' => 'required|string'
            ]);
            $project = Proyecto::where('nombre', $validateData['proyecto'])->first();
            if(isset($project)){
                $permission = AsignacionRolUsuario::select('permiso.alias')
                ->join('rol', 'rol.id', 'asignacion_rol_usuario.rol_id')
                ->join('asignacion_rol_politica', 'asignacion_rol_politica.rol_id', 'rol.id')
                ->join('politica', 'politica.id', 'asignacion_rol_politica.politica_id')
                ->join('asignacion_permiso_politica', 'asignacion_permiso_politica.politica_id', 'politica.id')
                ->join('permiso', 'permiso.id', 'asignacion_permiso_politica.permiso_id')
                ->where('rol.estado', 1)
                ->where('politica.estado', 1)
                ->where('permiso.estado', 1)
                ->where('permiso.permiso_sistema', 0)
                ->where('rol.proyecto_id', $project->id)
                ->where('asignacion_rol_usuario.usuario_id', $userId)
                ->where('asignacion_rol_usuario.proyecto_id', $project->id)
                ->groupBy('permiso.id')
                ->get();
                foreach ($permission as $permiso) {
                    array_push($permisosList, $permiso->alias);
                }
                return response()->json($permisosList, 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Proyecto no encontrado"
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
     * Function para obtener la lista de usuarios    
     * @return \Illuminate\Http\JsonResponse  
     */
    public function getUsers()
    {
        try {
            $users = User::select("id", "DPI", "nombres", "apellidos", "email", "codigo_usuario", "telefono", "descripcion")
                ->where("estado_usuario", 1)
                ->get();
            return response()->json($users, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Function para obtener una lista simple de los usuarios
     * @return \Illuminate\Http\JsonResponse  
     */
    public function getUsersList()
    {
        try {
            $users = User::select("id", "nombres", "apellidos")
                ->where("estado_usuario", 1)
                ->get();
            return response()->json($users, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    /**
     * @param $request recibe los parametros enviador por el frontend
     * Function para editar el usuario
     * $validateData valida los campos, es decir requiee que la peticion contenga los datos que necesitamos para editar el usuario 
     * @return \Illuminate\Http\JsonResponse     
     */
    public function editUser(Request $request)
    {
        try {
            $validateData = $request->validate([
                'id' => 'required|int',
                'DPI' => 'required|max:13|min:13',
                'nombres' => 'required|string|max:25',
                'apellidos' => 'required|string|max:25',
                'email' => 'required|email|min:13',
                'codigo_usuario' => 'required|max:5',
                'password' => 'nullable|min:8',
                'telefono' => 'required|min:8',
                'descripcion' => ''
            ]);
            $user = User::find($validateData['id']); //Busca el uaurio por su id
            if (isset($user)) { //Verifica que el usuario exista
                $user->nombres = $validateData['nombres'];
                $user->apellidos = $validateData['apellidos'];
                $user->email = $validateData['email'];
                $user->codigo_usuario = $validateData['codigo_usuario'];
                $user->DPI = $validateData['DPI'];
                $user->telefono = $validateData['telefono'];
                $user->descripcion = $validateData['descripcion'];
                if ($validateData['password']) { //En caso se haya modificado la contraseña 
                    $user->password = Hash::make($validateData['password']); //Hashea la nueva contraseña
                }
                $user->save(); //Metodo save equivalente a UPDATE de sql
                return response()->json([
                    'status' => true,
                    'message' => 'Usuario modificado correctamente'
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
     * Function para desactivar el usuario
     * @return \Illuminate\Http\JsonResponse     
     */

    public function desactiveUser(int $id)
    {
        try {
            $user = User::find($id); //busca el usuario por su id
            if (isset($user)) { //Verifica que el usuario exista
                $user->estado_usuario = 0; //Cambia a 0 el estado
                $user->save(); //Metodo save equivalente a UPDATE de sql
                return response()->json([
                    'status' => true,
                    'message' => 'Usuario desactivado correctamente'
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