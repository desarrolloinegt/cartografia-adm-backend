<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReemplazoUpm extends Model
{
    use HasFactory;
    protected $table="reemplazo_upm";
    public $timestamps = false;
    protected $fillable = [
        'usuario_id',
        'upm_anterior',
        'upm_nuevo',
        'fecha',
        'descripcion'
    ];
}
