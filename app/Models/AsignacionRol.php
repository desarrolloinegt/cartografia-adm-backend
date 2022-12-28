<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionRol extends Model
{
    use HasFactory;

    protected $table="asignacion_rol";
    public $timestamps = false;
    protected $fillable = [
        'rol_id',
        'grupo_id'
    ];
}
