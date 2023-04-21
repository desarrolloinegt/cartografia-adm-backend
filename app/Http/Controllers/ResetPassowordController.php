<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use Mail;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\TextPart;

class ResetPassowordController extends Controller
{
    public function generateTokenReset(Request $request)
    {
        try {
            $validateData = $request->validate([
                "email" => "required|email"
            ]);

            $user = User::where("email", $validateData['email'])->first();
            if (isset($user) && $user->estado_usuario == 1) {
                $token = Str::random(50);
                $date = new \DateTime("now", new \DateTimeZone('America/Guatemala'));
               
                ResetPassword::create([
                    'email'=>$user->email,
                    'token'=>$token,
                    'fecha'=>$date
                ]);
               
                Mail::to($request->email)->send(new ResetPassword($user->email, $token));
                return response()->json([
                    'status' => true,
                    'message' => "Revise el codigo enviado a su correo",
                    'token' => $token
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Email no encontrado",
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function validateToken(Request $request){
        try{
            $validateData=$request->validate([
                "email"=>'required|email',
                "token"=>"required|string"
            ]);
            $matchThese=["email"=>$validateData['email'],"token"=>$validateData['token']];
            $data=ResetPassword::where($matchThese)->first();
            if(isset($data)){
                $date = new \DateTime("now", new \DateTimeZone('America/Guatemala'));
                $diff=$data->fecha->diffInMinutes($date);
                if($diff<=10){
                    return response()->json([
                        'status' => true,
                        'message' => "Token valido",
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => "Token expirado",
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Token incorrecto",
                ], 404);
            }
        } catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request){
        try{
            $validateData=$request->validate([
                "email"=>'required|email',
                "token"=>'required|string',
                "password"=>'required|min:8'
            ]);
            $matchThese=["email"=>$validateData['email'],"token"=>$validateData['token']];
            $data=ResetPassword::where($matchThese)->first();
            if(isset($data)){
                $user=User::where('email',$validateData['email'])->first();
                $user->password = Hash::make($validateData['password']); //Hashea la nueva contraseña
                $user->save();//Metodo update de SQL
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Datos incorrectos",
                ], 404);
            }
        }catch(\Throwable $th){
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}