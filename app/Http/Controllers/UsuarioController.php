<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
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
                'password'=>'required|min:8'
            ]);
    
            $user=Usuario::create([
                'DPI'=>$validateData['DPI'],
                'Nombres'=>$validateData['Nombres'],
                'Apellidos'=>$validateData['Apellidos'],
                'Email'=>$validateData['Email'],
                'Codigo_Usuario'=>$validateData['Codigo_Usuario'],
                'Estado_Usuario'=>1,
                'password'=>Hash::make($validateData['password'])
            ]);
    
            //$user->assignRole($validateData['role']);
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

    /*public function login(Request $request){
        try{
            $validateData=$request->validate([
                'email'=>'required|email',
                'password'=>'required|string'
            ]);
            
            $user=User::where("CORREO",$request->email)->first();
            if(isset($user)){
                if(Hash::check($request->password,$user->PASSWORD)){
                    Auth::login($user);
                    $request->session()->regenerate();
                    $user2 = User::where("ID_USUARIO",'1')->first();;
                    //$roles = $user2->roles();
                    //$permissions =$user2->getPermissionsViaRoles();
                    //dd($roles, true);
                    return response()->json([
                        "message"=>"Inicio de sesion correcto",
                        "id"=>$user->ID_USUARIO,
                        "email"=>$user->CORREO,
                        "nombre"=>$user->NOMBRE,
                        "apellido"=>$user->APELLIDO,
                    ],200); 
                }
            } else {
                return response()->json([
                    "status"=>false,
                    "message"=>"Datos incorrectos"
                ], 401);
            }
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }

    public function logout(Request $request){
        Auth::logout();
 
        $request->session()->invalidate();
 
        $request->session()->regenerateToken();
        return response()->json([
            "message"=>"Sesion terminada"
        ],200);
    }*/
}
