<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departamento;
class DepartamentoController extends Controller
{
    public function obtenerDepartamentos(){
        $departamentos=Departamento::all();
        return response()->json($departamentos);
    }
}
