<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionPoliticaUsuario extends Model
{
    use HasFactory;
    protected $table="asignacion_politica_usuario";
    public $timestamps = false;
    protected $fillable = [
        'usuario_id',
        'politica_id'
    ];
}
