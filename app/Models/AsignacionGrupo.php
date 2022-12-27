<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionGrupo extends Model
{
    use HasFactory;

    protected $table="Asignacion Grupo";
    public $timestamps = false;
    protected $fillable = [
        'Usuario_Id',
        'Grupo_Id'
    ];
}
