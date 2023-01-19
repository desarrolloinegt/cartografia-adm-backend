<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    /**
     * Variable $table para indicar a que tabla se relaciona la clase Proyecto
     */
    use HasFactory;
    protected $table="proyecto";
    public $timestamps = false;
    protected $fillable = [
        'nombre',
        'year',
        'progreso',
        'estado_proyecto',
        'encuesta_id'
    ];
}
