<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permiso;

class PermisoController extends Controller
{
    public function obtenerPermisos(){
        $permisos=Permiso::select('id','alias')
            ->where('estado',1)
            ->get();
        return response()->json($permisos);    
    }
}
