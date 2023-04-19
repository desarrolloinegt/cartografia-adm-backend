<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    use HasFactory;
     /** 
     * En la variable $table indicamos a que tabla de la DB pertenece la clase Municipio
     * El arreglo fillable es para indiciar que si se desea crear un nuevo Vehiculo debe cumplir con los campos indicados en el arreglo
     */
    protected $table="departamento";
    public $timestamps = false;
    protected $fillable = [
        'nombre'
    ];

}
