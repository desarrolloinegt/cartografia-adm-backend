<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionUpmUsuario extends Model
{
    use HasFactory;
    protected $table="asignacion_upm_usuario";
    public $timestamps = false;
    protected $fillable = [
        'upm_id',
        'usuario_id',
        'proyecto_id'
    ];
}
