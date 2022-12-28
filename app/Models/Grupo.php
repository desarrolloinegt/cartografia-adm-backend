<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;
    protected $table="Grupo";
    public $timestamps = false;
    protected $fillable = [
        'Nombre',
        'Descripcion',
        'estado',
        'Proyecto_Id'
    ];
}
