<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionRol extends Model
{
    use HasFactory;

    protected $table="Asignacion Rol";
    public $timestamps = false;
    protected $fillable = [
        'Rol_Id',
        'Grupo_Id'
    ];
}
