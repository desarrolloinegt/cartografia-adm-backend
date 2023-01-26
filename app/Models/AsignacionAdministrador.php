<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionAdministrador extends Model
{
    protected $table="asignacion_administrador";
    public $timestamps = false;
    protected $fillable = [
        'usuario_id',
        'rol_id'
    ];
}
