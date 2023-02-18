<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UPM extends Model
{
    use HasFactory;
     /**
     * En la variable $table indicamos a que tabla de la DB pertenece la clase UPM
     * El arreglo fillable es para indiciar que si se desea crear un nuevo UPM debe cumplir con los campos indicados en el arreglo
     */
    protected $table="upm";
    public $timestamps = false;
    protected $fillable = [
        'municipio_id',
        'estado',
        'nombre'
    ];
}
