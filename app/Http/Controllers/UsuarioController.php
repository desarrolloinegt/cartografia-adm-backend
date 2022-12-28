<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\Proyecto;
use App\Models\AsignacionGrupo;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function register(Request $request) {

        try{

            $validateData=$request->validate([
                'DPI'=>'required|max:13|min:13|unique:usuario',
                'nombres'=>'required|string|max:25',
                'apellidos'=>'required|string|max:25',
                'email'=>'required|email|min:13|unique:usuario',
                'codigo_usuario'=>'required|max:5',
                'password'=>'required|min:8',
                'username'=>'required|unique:usuario'
            ]);
    
            $user=User::create([
                'DPI'=>$validateData['DPI'],
                'nombres'=>$validateData['nombres'],
                'apellidos'=>$validateData['apellidos'],
                'email'=>$validateData['email'],
                'codigo_usuario'=>$validateData['codigo_usuario'],
                'estado_usuario'=>1,
                'password'=>Hash::make($validateData['password']),
                'username'=>($validateData['username'])
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

    public function login(Request $request){
        try{
            $validateData=$request->validate([
                'username'=>'required|string',
                'password'=>'required|string'
            ]);
            $user=User::where("username",$validateData['username'])->first();
            if(isset($user)){
                if ($user->estado_usuario==1) {
                    if(Hash::check($validateData['password'], $user->password)){
                        Auth::login($user);
                        $proyectos=$this->obtenerProyecto($user->id);
                        $token = $user->createToken('auth_token')->plainTextToken;
                        return response()->json([
                            'token' => $token,
                            "id"=>$user->id,
                            "usuario"=>$user->username,
                            "proyectos"=>$proyectos,
                        ],200); 
                    } else{
                        return response()->json([
                            "status"=>false,
                            "message"=>"Contraseña incorrecta",
                        ],401); 
                    }
                    
                } else {
                    return response()->json([
                        "status"=>false,
                        "message"=>"Usuario no disponible",
                    ],401); 
                }
            }
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "message"=>"Sesion terminada"
        ],200);
    }

    private function obtenerProyecto($id){
        $grupos=AsignacionGrupo::select('grupo_id')
                        ->join('usuario',"asignacion_grupo.usuario_id","usuario.id")
                        ->where('usuario.id',$id)
                        ->get();
        $json=[];
        foreach($grupos as $grupo){
            $proyecto=Proyecto::select('proyecto.id AS id_proyecto','proyecto.nombre AS nombre_proyecto')
                ->join('grupo',"proyecto.id","grupo.proyecto_id")
                ->where('grupo.id',$grupo->grupo_id)
                ->get();
            $role=Role::select('rol.id','nombre AS rol')
                ->join('asignacion_rol',"rol.id","asignacion_rol.rol_id")
                ->where('asignacion_rol.grupo_id',$grupo->grupo_id)
                ->get();
            $permisos=[];
            foreach($role as $rol){
                $permisos=Permiso::select('id','alias')
                    ->join('asignacion_permisos','asignacion_permisos.permiso_id','permiso.id')
                    ->where('asignacion_permisos.rol_id',$rol->id)
                    ->get();
            }
           
            $data =[
                'proyecto'=>$proyecto,
                "roles"=>$role,
                "permisos"=>($permisos)
            ];    
            array_push($json,$data); 
        }  
        return $json;
    }
}
