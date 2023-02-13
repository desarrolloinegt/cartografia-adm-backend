<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionRolUsuario extends Model
{
    use HasFactory;
    protected $table="asignacion_rol_usuario";
    public $timestamps = false;
    protected $fillable = [
        'usuario_id',
        'rol_id'
    ];
}
