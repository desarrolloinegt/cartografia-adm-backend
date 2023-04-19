<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionPoliticaPermiso extends Model
{
    use HasFactory;
    /**
     * Variable $table indica a que tabla de la DB hace referencia la clase AsignacionPermiso
     * El arreglo fillable indica los campos que require una creacion de un nueva asignacion de permisos 
     */
    protected $table="asignacion_permiso_politica";
    public $timestamps = false;
    protected $fillable = [
        'politica_id',
        'permiso_id'
    ];
}
