<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipoCampo extends Model
{
    use HasFactory;
    
    protected $table="equipo_campo";
    public $timestamps = false;
    protected $fillable = [
        'proyecto_id',
        'supervisor',
        'usuario_asignador'
    ];
}
