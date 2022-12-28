<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionGrupo extends Model
{
    use HasFactory;
    /**
     * Variable $table indica a que tabla de la DB hace referencia la clase AsignacionGrupo
     * El arreglo fillable indica los campos que require una creacion de un nueva asignacion de grupo 
     */
    protected $table="asignacion_grupo";
    public $timestamps = false;
    protected $fillable = [
        'usuario_id',
        'grupo_id'
    ];
}
