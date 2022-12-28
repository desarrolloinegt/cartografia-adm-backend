<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionGrupo extends Model
{
    use HasFactory;

    protected $table="asignacion_grupo";
    public $timestamps = false;
    protected $fillable = [
        'usuario_id',
        'grupo_id'
    ];
}
