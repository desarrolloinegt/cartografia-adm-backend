<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionPermiso extends Model
{
    use HasFactory;
    protected $table="asignacion_permisos";
    public $timestamps = false;
    protected $fillable = [
        'rol_id',
        'permiso_id'
    ];
}
