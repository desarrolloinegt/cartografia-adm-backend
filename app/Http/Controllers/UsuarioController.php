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
                'DPI'=>'required|max:13|min:13|unique:Usuario',
                'Nombres'=>'required|string|max:25',
                'Apellidos'=>'required|string|max:25',
                'Email'=>'required|email|min:13|unique:Usuario',
                'Codigo_Usuario'=>'required|max:5',
                'password'=>'required|min:8',
                'username'=>'required|unique:Usuario'
            ]);
    
            $user=User::create([
                'DPI'=>$validateData['DPI'],
                'Nombres'=>$validateData['Nombres'],
                'Apellidos'=>$validateData['Apellidos'],
                'Email'=>$validateData['Email'],
                'Codigo_Usuario'=>$validateData['Codigo_Usuario'],
                'Estado_Usuario'=>1,
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
                if ($user->Estado_Usuario==1) {
                    if(Hash::check($validateData['password'], $user->Password)){
                        Auth::login($user);
                        $proyectos=$this->obtenerProyecto($user->Id);
                        $token = $user->createToken('auth_token')->plainTextToken;
                        return response()->json([
                            'token' => $token,
                            "id"=>$user->Id,
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
        $grupos=AsignacionGrupo::select('Grupo_Id')
                        ->join('Usuario',"Asignacion Grupo.Usuario_Id","Usuario.Id")
                        ->where('Usuario.Id',$id)
                        ->get();
        $json=[];
        foreach($grupos as $grupo){
            $proyecto=Proyecto::select('Proyecto.Id AS ID_PROYECTO','Proyecto.nombre AS NOMBRE_PROYECTO')
                ->join('Grupo',"Proyecto.Id","Grupo.Proyecto_Id")
                ->where('Grupo.Id',$grupo->Grupo_Id)
                ->get();
            $role=Role::select('Rol.Id','Nombre AS Role')
                ->join('Asignacion Rol',"Rol.Id","Asignacion Rol.Rol_Id")
                ->where('Asignacion Rol.Grupo_Id',$grupo->Grupo_Id)
                ->get();
            $permisos=[];
            foreach($role as $rol){
                $permisos=Permiso::select('Id','alias')
                    ->join('Asignacion Permisos','Asignacion Permisos.Permiso_Id','Permiso.Id')
                    ->where('Asignacion Permisos.Rol_Id',$rol->Id)
                    ->get();
            }
           
            $data =[
                'proyecto'=>strtolower($proyecto),
                "roles"=>strtolower($role),
                "permisos"=>($permisos)
            ];    
            array_push($json,$data); 
        }  
        return $json;
    }
}
