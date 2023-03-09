<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControlProgreso extends Model
{
    use HasFactory;
    protected $table="control_de_progreso";
    public $timestamps = false;
    protected $fillable = [
        'fecha_inicio',
        'upm_id',
        'usuario_id',
        'proyecto_id'
    ];
}
