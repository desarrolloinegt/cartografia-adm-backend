<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $email;
    public $token;
    public function __construct($email,$token)
    {
        $this->email=$email;
        $this->token=$token;
    }

    public function build()
    {
        return $this->subject('Recuperacion de contraseña')
            //->html("<span>Codigo: </span> <p>$this->token</p> <span>Si usted no ha solicitado cambiar su contraseña
            //, comuniquese a ine.gob.gt</span>")
            ->html('<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Cambio de  contraseña</title>
            </head>
            <body style="background-color: #f6f6f6;">
                <div style="background-color: #f6f6f6; padding: 40px;">
                    <div style="background-color: #ffffff; max-width: 600px; margin: 0 auto; padding: 40px;">
                        <h1 style="text-align: center;">Cambiar contraseña</h1>
                        <p>Recibiste este correo electrónico porque solicitaste cambiar tu contraseña. Si no solicitaste este cambio, comunicate con ine.gob.gt</p>
                        <p>Codigo para cambiar contraseña:</p><p style="color: #828282; font-size: 22px; font-weight: bold; text-align: center; ">'.$this->token.'</p>
                        <div style="text-align: center;"> 
                        <img  width="300" height="300" src="https://www.ine.gob.gt/ine/wp-content/uploads/2017/09/cropped-INE.png" alt="Logo INE">
                        </div>
                        
                    </div>
                </div>  
            </body>
            </html>
            ')
            ->text('');
    }
    

   
}
