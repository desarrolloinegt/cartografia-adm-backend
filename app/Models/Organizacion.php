<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organizacion extends Model
{
    use HasFactory;
    protected $table="organizacion";
    public $timestamps = false;
    protected $fillable = [
        'usuario_superior',
        'usuario_inferior',
        'proyecto_id',
        'usuario_asignador',
        'fecha_asignacion'
    ];
}
