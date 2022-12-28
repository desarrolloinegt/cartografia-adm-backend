<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**d
     * En la variable $table indicamos a que tabla de la DB pertenece la clase Role
     * El arreglo fillable es para indiciar que si se desea crear un nuevo rol debe cumplir con los campos indicados en el arreglo
     */
    protected $table="rol";
    public $timestamps = false;
    protected $fillable = [
        'nombre',
        'estado'
    ];
}
