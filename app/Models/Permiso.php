<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    use HasFactory;

    protected $table="Permiso";
    public $timestamps = false;
    protected $fillable = [
        'Nombre'
    ];
}
