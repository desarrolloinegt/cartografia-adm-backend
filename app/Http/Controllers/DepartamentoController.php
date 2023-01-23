<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departamento;

class DepartamentoController extends Controller
{
    /**
     * Summary of obtenerDepartamentos
     * funcion que devuelve todos los departamentos guardados en la DB
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerDepartamentos()
    {
        $departamentos = Departamento::all();
        return response()->json($departamentos);
    }
}