<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /**
     * En esta parte la variable $table es para relacionar la clase User a la tabla usuario.
     * $primaryKey sirve para indicar cual es la llave primara de la tabla Usuario, esto por que es utilizada para el token. NO QUITAR ESA PARTE
     * El arrelgo fillable es para indicar que para crear un nuevo usuario, los datos que manda el usuario debe estar completos de lo contrario dara error.
     */
    use HasApiTokens, HasFactory, Notifiable;
    protected $table="usuario";
    public $timestamps = false;
    protected $primaryKey ="id";
    protected $fillable = [
        'DPI',
        'nombres',
        'apellidos',
        'email',
        'codigo_usuario',
        'estado_usuario',
        'password',
        'username'
    ]; 
}