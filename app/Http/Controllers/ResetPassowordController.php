<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use Mail;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\TextPart;

class ResetPassowordController extends Controller
{
    public function forgetPassword(Request $request)
    {
        try {
            $validateData = $request->validate([
                "email" => "required|email"
            ]);

            $user = User::where("email", $validateData['email'])->first();
            if (isset($user) && $user->estado_usuario == 1) {
                $email = $validateData['email'];
                $token = Str::random(50);
                $date = new \DateTime("now", new \DateTimeZone('America/Guatemala'));
               
                /*ForgotPassword::create([
                'email'=>$user->email,
                'token'=>$token,
                'created_at'=>$date
                ]);
                $body = 'Correo para recuperar tu contraseña.<h1>';
                $textPart = new TextPart($body);
                Mail::send([],[],function ($message) use($request,$textPart){
                $message->to($request->email);
                $message->subject('Recuperacion de contraseña');
                $message->html("Instituto Nacional de Estadistica");
                $message->htmlVersion(5);
                $message->setBody($textPart);
                });*/
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
}