<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionUpm extends Model
{
    use HasFactory;
     /**
     * Variable $table indica a que tabla de la DB hace referencia la clase AignacionUpm
     * El arreglo fillable indica los campos que require una creacion de un nueva asignacion de upms 
     */
    protected $table="asignacion_upm";
    public $timestamps = false;
    protected $fillable = [
        'upm_id',
        'proyecto_id',
        'estado_upm'
    ];
}
