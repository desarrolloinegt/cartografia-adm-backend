<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    /**
     * Variable $table se indica a que tabla de la DB esta relacionada la clase Permiso
     */
    use HasFactory;
    protected $table="permiso";
    public $timestamps = false;
    protected $fillable = [
        'alias',
        'estado'
    ];
}
