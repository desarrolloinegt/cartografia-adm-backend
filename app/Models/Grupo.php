<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    /**
     * Variable $table indica a que tabla de la DB hace referencia la clase Grupo
     * El arreglo fillable indica los campos que require una creacion de un nuevo grupo 
     */
    use HasFactory;
    protected $table="grupo";
    public $timestamps = false;
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'proyecto_id',
        'jerarquia'
    ];
}
