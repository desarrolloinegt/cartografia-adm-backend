<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AsignacionGrupo;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AsignacionGrupoController extends Controller
{
    /**
     * @param $request recibe la peticion del frontend
     * $validateData valida los campos, es decir require que la peticion contenga dos campos y ambos sean enteros
     * $asignacion hace uso de ELOQUENT de laravel con el metodo create y solo es necesario pasarle los campos validados
     * ELOQUENT se hara cargo de insertar en la DB
     */
    public function asignarGrupoUsuario(Request $request)
    {
        $validateData = $request->validate([
            'usuarios' => 'required|array',
            'usuarios.*' => 'int',
            'grupo_id' => 'required|int'
        ]);
        $grupo = Grupo::find($validateData['grupo_id']);
        $arrayUsuarios = $validateData['usuarios'];
        if (isset($grupo)) {
            if ($grupo->estado == 1) {
                foreach($arrayUsuarios as $usuario){
                    $asignacion=AsignacionGrupo::create([
                        "grupo_id"=>$grupo->id,
                        "usuario_id"=>$usuario
                    ]);             
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Usuario asignado a Grupo correctamente'
                ], 200);

            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Datos no disponibles'
                ], 401);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Datos no encontrados'
            ], 404);
        }
    }

    public function store($validateData)
    {
        $grupo = Grupo::find($validateData['grupo_id']);
        $usuario = User::find($validateData['usuario_id']);
        if (isset($grupo) && isset($usuario)) {
            if ($grupo->estado == 1 && $usuario->estado_usuario == 1) {
                try {
                    $asignacion = AsignacionGrupo::create([
                        "usuario_id" => $validateData['usuario_id'],
                        "grupo_id" => $validateData['grupo_id']
                    ]);
                    return response()->json([
                        'status' => true,
                        'message' => 'Usuario asignado a Grupo correctamente'
                    ], 200);
                } catch (\Throwable $th) {
                    return response()->json([
                        'status' => false,
                        'message' => $th->getMessage()
                    ], 500);
                }

            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Datos no disponibles'
                ], 401);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Datos no encontrados'
            ], 404);
        }
    }
    public function asignacionMasiva(Request $request)
    {
        $array = $request->all();
        $responses = [];
        foreach ($array as $asignacion => $item) {
            $validateData = Validator::make($item, [
                'usuario_id' => 'required|int',
                'grupo_id' => 'required|int'
            ]);
            if ($validateData->fails()) {
            } else {
                //var_dump($item['grupo_id']);
                array_push($responses, $this->store($item));

            }

        }
        return response()->json($responses);
    }

    public function eliminarAsignacion(Request $request)
    {
        $validateData = $request->validate([
            'usuario_id' => 'required|int',
            'grupo_id' => 'required|int'
        ]);
        $matchThese = ['usuario_id' => $validateData['usuario_id'], 'grupo_id' => $validateData['grupo_id']];
        $asignacion = AsignacionGrupo::where($matchThese)
            ->first();

        if (isset($asignacion)) {
            AsignacionGrupo::where($matchThese)
                ->delete();
            return response()->json([
                'status' => true,
                'message' => 'Asignacion de grupo y usuario eliminada'
            ], 200);
        } else {
            return response()->json([
                'status' => true,
                'message' => 'Datos no encontrados'
            ], 404);
        }
    }

    public function modificarGrupoUsuarios(Request $request)
    {
        $validateData = $request->validate([
            'usuarios' => 'required|array',
            'usuarios.*' => 'int',
            'grupo_id' => 'required|int'
        ]);
        $grupo = Grupo::find($validateData['grupo_id']);
        if (isset($grupo)) {
            AsignacionGrupo::where('grupo_id', $validateData['grupo_id'])->delete();
            $this->asignarGrupoUsuario($request);
            return response()->json([
                'status' => true,
                'message' => 'Grupo modificado correctamente'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Dato no encontrado'
            ], 404);
        }
    }
    public function obtenerGrupoUsuarios()
    {
        try {
            $asginaciones = AsignacionGrupo::selectRaw('grupo.id,grupo.nombre, GROUP_CONCAT(usuario.username) AS usuarios')
                ->join('usuario', 'asignacion_grupo.usuario_id', 'usuario.id')
                ->join('grupo', 'asignacion_grupo.grupo_id', 'grupo.id')
                ->where('usuario.estado_usuario', 1)
                ->where('grupo.estado', 1)
                ->groupBy('asignacion_grupo.grupo_id')
                ->get();
            foreach ($asginaciones as $asginacion) {
                $asginacion->usuarios = explode(",", $asginacion->usuarios);
            }
            return response()->json($asginaciones);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}