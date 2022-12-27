<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends  Authenticatable
{
    protected $table="Usuario";
    public $timestamps = false;
    protected $fillable = [
        'DPI',
        'Nombres',
        'Apellidos',
        'Email',
        'Codigo_Usuario',
        'Estado_Usuario',
        'password'
    ];
} 
