<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\AsignacionAdministrador;
use App\Models\Proyecto;
use App\Models\AsignacionGrupo;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{

    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga siete campos, la inidicacion unique
     * hace una consulta a la db y se asegura de que no exista de lo contrario hara uso de  excepciones.
     * $user hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
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
                'username' => 'required|unique:usuario',
                'telefono'=>'required',
                'descripcion'=>''
            ]);

            $user = User::create([
                'DPI' => $validateData['DPI'],
                'nombres' => $validateData['nombres'],
                'apellidos' => $validateData['apellidos'],
                'email' => $validateData['email'],
                'codigo_usuario' => $validateData['codigo_usuario'],
                'estado_usuario' => 1,
                'password' => Hash::make($validateData['password']),
                'username' => ($validateData['username']),
                'telefono'=>$validateData['telefono'],
                'descripcion'=>$validateData['descripcion']
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
     * $validateData valida los campos, es decir require que la peticion contenga dos campos, 
     * $user hace uso de ELOQUENT de laravel con where lo cual obtiene el usuario de la DB en caso existiera comparando el username
     * 
     * la condicion $user->estado_usuario==1 valida que el usuario este disponible de lo contrario no autenticara y dara el mensaje indicado
     * 
     * Hash::check($validateData['password'], $user->password) metodo de laravel que valida la contraseña guardada en la base de datos
     * con la recibida en $request, el metodo Hash::check desencripta la contraseña y la compara este metodo no tiene forma de saber cual
     * es la contraseña ya que laravel incluye este tipo de seguridad
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $validateData = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string'
            ]);
            $user = User::where("username", $validateData['username'])->first();
            if (isset($user)) {
                if ($user->estado_usuario == 1) {
                    if (Hash::check($validateData['password'], $user->password)) { //comparacion de contraseñas
                        Auth::login($user);
                        $token = $user->createToken('auth_token')->plainTextToken; //Creacion del token Bearer
                        return response()->json([
                            "status" => true,
                            "token" => $token,
                            "id" => $user->id,
                            "usuario" => $user->username,
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
    public function isAdmin($id){
        try{
            $status = AsignacionAdministrador::where('usuario_id',$id)
            ->first();
            if(isset($status)){
                return response()->json([
                    "status" => true
                ],200);
            }else{
                return response()->json([
                    "status" => false
                ],200); 
            }
        }catch(\Throwable $th){

        }
        
    }
    public function obtenerPermisosAdmin($id){
        $permisosList = [];
        $permisosAdmin = AsignacionAdministrador::select('permiso.alias')
            ->join('usuario','usuario.id','asignacion_administrador.usuario_id')
            ->join('rol','rol.id','asignacion_administrador.rol_id')
            ->join('asignacion_permisos','asignacion_permisos.rol_id','rol.id')
            ->join('permiso','permiso.id','asignacion_permisos.permiso_id')
            ->where('asignacion_administrador.usuario_id',$id)
            ->where('permiso.estado',1)
            ->where('rol.estado',1)
            ->get();
        foreach ($permisosAdmin as $permiso) {
            array_push($permisosList,$permiso->alias);
        }    
        return response()->json($permisosList,200) ;
    }
    /**
     * @param $request recibe la peticion del frontend
     * Atraves de $request podemos acceder al usuario, ver el token actual y eliminarlo
     * esto terminara la sesion
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            "message" => "Sesion terminada"
        ], 200);
    }

    /**
     * @param $id recibe el id del usuario para evaluar
     * 
     * 
     * Primera consulta: Uso de ELOQUENT query podemos crear consultas personalizada
     * en este caso el inner join para obtener lo siguiente
     * 1. $grupos realiza el inner join entre las tablas Asignacion grupo y usuario para obtener a los grupos que esta incluido el usuario
     * 
     * 2.$proyecto: se evalua el segundo inner join entre Proyecto y Grupo para obtener los proyectos que tiene dicho grupo
     * 
     * 3.$role: Se evalua el tercer inner join entre rol y asignacion rol para obtener los roles que estan asignados al grupo 
     * anteriormente encontrado
     * 
     * 4. $permisos: una vez obtenido el rol se hace el ultimo inner join para obtener los permisos que estan relacionados a este rol
     * mediante las tablas asignacion_permiso y Permiso usando el rol obtenido para la comparacion
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerProyecto($id)
    {
        try {
            $proyectos = AsignacionGrupo::selectRaw('proyecto.nombre')
                ->join('usuario', "asignacion_grupo.usuario_id", "usuario.id")
                ->join('grupo', 'asignacion_grupo.grupo_id', 'grupo.id')
                ->join('proyecto', 'proyecto.id', 'grupo.proyecto_id')
                ->where('usuario.id', $id)
                ->groupBy('proyecto.nombre')
                ->get();
            return response()->json($proyectos, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function obtenerPermisos(Request $request)
    {
        try {
            $permisosList = array();
            $validateData = $request->validate([
                'proyecto' => 'required|string',
                'usuario_id' => 'required|int'
            ]);
            $grupos = Grupo::select('grupo.id')
                ->join('asignacion_grupo', 'asignacion_grupo.grupo_id', 'grupo.id')
                ->join('usuario', 'usuario.id', 'asignacion_grupo.usuario_id')
                ->join('proyecto','proyecto.id','grupo.proyecto_id')
                ->where('usuario.id', $validateData['usuario_id'])
                ->where('proyecto.nombre',$validateData['proyecto'])
                ->get();
            foreach ($grupos as $grupo) {
                $role = Role::select('rol.id')
                    ->join('asignacion_rol', "rol.id", "asignacion_rol.rol_id")
                    ->where('asignacion_rol.grupo_id', $grupo->id)
                    ->where('rol.estado', 1)
                    ->get();
                foreach ($role as $rol) {
                    $permisos = Permiso::select('alias')
                        ->join('asignacion_permisos', 'asignacion_permisos.permiso_id', 'permiso.id')
                        ->where('asignacion_permisos.rol_id', $rol->id)
                        ->get();
                    foreach($permisos as $permiso){
                        array_push($permisosList,$permiso->alias);
                    }    
                }
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
     * A traves de ELOQUENT podemos usar el metodo select y seleccionar los campos necesarios con la condicion de que el estado
     * sea 1, es decir este activo    
     * @return \Illuminate\Http\JsonResponse  
     */
    public function obtenerUsuarios()
    {
        $users = User::select("id", "DPI", "nombres", "apellidos", "username", "email", "codigo_usuario","telefono","descripcion")
            ->where("estado_usuario", 1)
            ->get();
        return response()->json($users, 200);
    }

    public function obtenerUsuariosList()
    {
        $users = User::select("id", "username")
            ->where("estado_usuario", 1)
            ->get();
        return response()->json($users, 200);
    }
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir requiee que la peticion contenga los datos que necesitamos para editar el usuario
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar el rol que corresponde el id
     * 
     * Al obtener el rol podemos hacer uso de sus variables y asignarle el valor obtenido en el validateData
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL 
     * @return \Illuminate\Http\JsonResponse     
     */

    public function modificarUsuario(Request $request)
    {
        try {
            $validateData = $request->validate([
                'id' => 'required|int',
                'DPI' => 'required|max:13|min:13',
                'nombres' => 'required|string|max:25',
                'apellidos' => 'required|string|max:25',
                'email' => 'required|email|min:13',
                'codigo_usuario' => 'required|max:5',
                'username' => 'required',
                'password' => 'nullable|min:8',
                'telefono'=>'required|string',
                'descripcion'=>''
            ]);
            $user = User::find($validateData['id']);
            if (isset($user)) {
                $user->nombres = $validateData['nombres'];
                $user->apellidos = $validateData['apellidos'];
                $user->email = $validateData['email'];
                $user->codigo_usuario = $validateData['codigo_usuario'];
                $user->username = $validateData['username'];
                $user->DPI = $validateData['DPI'];
                $user->telefono = $validateData['telefono'];
                $user->descripcion = $validateData['descripcion'];
                if ($validateData['password']) {
                    $user->password = Hash::make($validateData['password']);
                }
                $user->save();
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
     * A traves de ELOQUENT podemos usar el metodo find y seleccionar el Usuario que corresponde el id
     * 
     * Al obtener el usuario podemos hacer uso de sus variables y asignarle el valor 0 al estado del usuario
     * Con el metodo save() de ELOQUENT se hace referencia al UPDATE de SQL 
     * @return \Illuminate\Http\JsonResponse     
     */

    public function desactivarUsuario(int $id)
    {
        try {
            $user = User::find($id);
            if (isset($user)) {
                $user->estado_usuario = 0;
                $user->save();
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