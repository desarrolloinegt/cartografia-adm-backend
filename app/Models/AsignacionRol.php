<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionRol extends Model
{
    use HasFactory;
    /**
     * Variable $table indica a que tabla de la DB hace referencia la clase Asignacion rol
     * El arreglo fillable indica los campos que require una creacion de un nueva asignacion de rol 
     */
    protected $table="asignacion_rol";
    public $timestamps = false;
    protected $fillable = [
        'rol_id',
        'grupo_id'
    ];
}
