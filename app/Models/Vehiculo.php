<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;
     /** 
     * En la variable $table indicamos a que tabla de la DB pertenece la clase Vehiculo
     * El arreglo fillable es para indiciar que si se desea crear un nuevo Vehiculo debe cumplir con los campos indicados en el arreglo
     */
    protected $table="vehiculo";
    public $timestamps = false;
    protected $fillable = [
        'placa',
        'modelo',
        'year',
        'estado'
    ];
}
