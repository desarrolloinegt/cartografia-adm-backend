<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionRolUsuario extends Model
{
    use HasFactory;
    /**
     * Variable $table indica a que tabla de la DB hace referencia la clase AsignacionGrupo
     * El arreglo fillable indica los campos que require una creacion de un nueva asignacion de grupo 
     */
    protected $table="asignacion_rol_usuario";
    public $timestamps = false;
    protected $fillable = [
        'usuario_id',
        'rol_id',
        'proyecto_id'
    ];
}
