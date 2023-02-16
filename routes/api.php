<?php

use App\Http\Controllers\AsignacionAdministradorController;
use App\Http\Controllers\AsignacionRolUsuarioController;
use App\Http\Controllers\CargaTrabajoController;
use App\Http\Controllers\OrganizacionController;
use App\Http\Controllers\ReemplazoUpmController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\AsignacionPermisoController;
use App\Http\Controllers\AsignacionRolController;
use App\Http\Controllers\AsignacionGrupoController;
use App\Http\Controllers\EncuestaController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\UPMController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\AsignacionUpmController;
use App\Http\Controllers\VehiculoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/login', [UsuarioController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => ['auth:sanctum']], function () {
    //roles

    Route::post('/rol', [RoleController::class, 'createRole']);
    Route::get('/roles', [RoleController::class, 'obtenerRoles']);
    Route::get('/rol/{id}', [RoleController::class, 'desactivarRol']);
    Route::patch('/rol/edit', [RoleController::class, 'modificarRol']);

    //permiso
    Route::get('/permisos', [PermisoController::class, 'obtenerPermisos']);

    //usuario

    Route::post('/registro', [UsuarioController::class, 'register']);
    Route::patch('/usuario/edit', [UsuarioController::class, 'modificarUsuario']);
    Route::get('/usuario/{id}', [UsuarioController::class, 'desactivarUsuario']);
    Route::get('/usuarios', [UsuarioController::class, 'obtenerUsuarios']);
    Route::get('/usuariosList', [UsuarioController::class, 'obtenerUsuariosList']);
    Route::get('/projectsAssing/{id}', [UsuarioController::class, 'obtenerProyecto']);
    Route::post('/obtenerPermisos', [UsuarioController::class, 'obtenerPermisos']);
    Route::get('/obtenerPermisosDirectos/{id}', [UsuarioController::class, 'obtenerPermisosDirectos']);
    Route::post('/logout', [UsuarioController::class, 'logout']);
 

    //asignaciones usuario rol
    Route::get('/obtenerRolesUser/{id}',[AsignacionRolUsuarioController::class,'obtenerRolesUsuario']);
    Route::patch('/asignarRoleUser',[AsignacionRolUsuarioController::class,'asignarUsuarioRol']);


    //asginaciones grupo usuario
    Route::post('/asignarGruposUsuarios', [AsignacionGrupoController::class, 'asignacionMasiva']);
    Route::get('/obtenerGrupoUsuarios/{id}', [AsignacionGrupoController::class, 'obtenerGrupoUsuarios']);
    Route::post('/asignacionGrupoUsuario', [AsignacionGrupoController::class, 'asignarUsuarioAGrupo']);
    Route::patch('/eliminarUsuarioGrupo', [AsignacionGrupoController::class, 'eliminarUsuario']);


    //asgingaciones rol Permiso
    Route::post('/asignarPermiso', [AsignacionPermisoController::class, 'asignacionMasiva']);
    Route::get('/asignacionesRolPermiso/{id}', [AsignacionPermisoController::class, 'obtenerRolPermiso']);
    Route::post('/asignacionRolPermiso/eliminar', [AsignacionPermisoController::class, 'eliminarAsignacion']);


    //asgingaciones rol Grupo
    Route::get('/obtenerGruposRoles/{id}', [AsignacionRolController::class, 'obtenerGruposRoles']);
    Route::post('/asignarGrupoRol', [AsignacionRolController::class, 'asignarRolGrupo']);
    Route::patch('/asignacionGrupoRol/edit', [AsignacionRolController::class, 'modificarGruposRoles']);

    //Asginacions upms
    Route::post('/asginarUpmsProyecto', [AsignacionUpmController::class, 'asignacionMasiva']);
    Route::patch('/sustituirUpm', [AsignacionUpmController::class, 'sustituirUpm']);


    //Encuesta
    Route::post('/encuesta', [EncuestaController::class, 'crearEncuesta']);
    Route::patch('/encuesta/edit', [EncuestaController::class, 'modificarEncuesta']);
    Route::get('/encuestas', [EncuestaController::class, 'obtenerEncuestas']);
    Route::get('/encuesta/{id}', [EncuestaController::class, 'desactivarEncuesta']);

    //Proyecto
    Route::post('/proyecto', [ProyectoController::class, 'crearProyecto']);
    Route::patch('/proyecto/edit', [ProyectoController::class, 'modificarProyecto']);
    Route::get('/proyectos', [ProyectoController::class, 'obtenerProyectos']);
    Route::get('/proyectoId/{projecto}', [ProyectoController::class, 'obtenerProyectoId']);
    Route::get('/proyecto/{id}', [ProyectoController::class, 'desactivarProyecto']);
    Route::get('/obtenerGruposProyecto/{proyecto}', [ProyectoController::class, 'obtenerGruposPorProyecto']);
    Route::get('/finalizarProyecto/{id}', [ProyectoController::class, 'finalizarProyecto']);

    //Grupo
    Route::post('/grupo', [GrupoController::class, 'createGroup']);
    Route::get('/grupos', [GrupoController::class, 'obtenerGrupos']);
    Route::patch('/jerarquias', [GrupoController::class, 'modificarJerarquias']);
    Route::patch('/grupo/edit', [GrupoController::class, 'modificarGrupo']);
    Route::get('/grupo/{id}', [GrupoController::class, 'desactivarGrupo']);
    Route::post('/seleccionarGruposMenores', [GrupoController::class, 'seleccionarGruposMenores']);



    //Vehiculo
    Route::post('/vehiculo', [VehiculoController::class, 'crearVehiculo']);
    Route::patch('/vehiculo/edit', [VehiculoController::class, 'modificarVehiculo']);
    Route::get('/vehiculos', [VehiculoController::class, 'obtenerVehiculos']);
    Route::get('/vehiculo/{id}', [VehiculoController::class, 'desactivarVehiculo']);


    //Municipio y Departamento
    Route::get('/municipios', [MunicipioController::class, 'obtenerMunicipios']);
    Route::get('/departamentos', [DepartamentoController::class, 'obtenerDepartamentos']);

    //Asignacion de administradores

    Route::post('/asignarAdmin', [AsignacionAdministradorController::class, 'asignarAdmin']);

    //UPM
    Route::post('/upm', [UPMController::class, 'crearUpm']);

    //Asignacion de carga de trabajo
    Route::post('/asignarPersonal/{id}', [CargaTrabajoController::class, 'asignarPersonal']);
    Route::get('/obtenerUpmsProyecto/{id}', [AsignacionUpmController::class, 'obtenerUpmsProyecto']);
    Route::post('/asignarUpmPersonal',[CargaTrabajoController::class,'asignarUpmsAPersonal']);
    Route::post('/obtenerUpmPersonal',[CargaTrabajoController::class,'obtenerUpmsPersonal']);

    //Reemplazo de upm
    Route::get('/detalleSustitucion/{id}',[ReemplazoUpmController::class,'verDetalle']);
});