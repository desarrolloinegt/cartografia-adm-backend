<?php

use App\Http\Controllers\AsignacionAdministradorController;
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
Route::post('/login',[UsuarioController::class,'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//roles

Route::post('/rol',[RoleController::class,'createRole']);
Route::get('/roles',[RoleController::class,'obtenerRoles']);
Route::get('/rol/{id}',[RoleController::class,'desactivarRol']);
Route::patch('/rol/edit',[RoleController::class,'modificarRol']);

//permiso
Route::get('/permisos',[PermisoController::class,'obtenerPermisos']);

//usuario

Route::post('/registro',[UsuarioController::class,'register']);
Route::patch('/usuario/edit',[UsuarioController::class,'modificarUsuario']);
Route::get('/usuario/{id}',[UsuarioController::class,'desactivarUsuario']);
Route::get('/usuarios',[UsuarioController::class,'obtenerUsuarios']);
Route::get('/usuariosList',[UsuarioController::class,'obtenerUsuariosList']);
Route::get('/projectsAssing/{id}',[UsuarioController::class,'obtenerProyecto']);
Route::post('/obtenerPermisos',[UsuarioController::class,'obtenerPermisos']);
Route::get('/obtenerPermisosAdmin/{id}',[UsuarioController::class,'obtenerPermisosAdmin']);
Route::get('/isAdmin/{id}',[UsuarioController::class,'isAdmin']);
Route::post('/logout',[UsuarioController::class,'logout'])->middleware('auth:sanctum');


//asginaciones grupo usuario
Route::post('/asignarGruposUsuarios',[AsignacionGrupoController::class,'asignacionMasiva']);
Route::get('/obtenerGrupoUsuarios/{id}',[AsignacionGrupoController::class,'obtenerGrupoUsuarios']);
Route::patch('/asignacionGrupoUsuario/edit',[AsignacionGrupoController::class,'modificarGrupoUsuarios']);

//asgingaciones rol Permiso
Route::post('/asignarPermiso',[AsignacionPermisoController::class,'asignacionMasiva']);
Route::get('/asignacionesRolPermiso/{id}',[AsignacionPermisoController::class,'obtenerRolPermiso']);
Route::post('/asignacionRolPermiso/eliminar',[AsignacionPermisoController::class,'eliminarAsignacion']);


//asgingaciones rol Grupo
Route::get('/obtenerGruposRoles/{id}',[AsignacionRolController::class,'obtenerGruposRoles']);
Route::post('/asignarGrupoRol',[AsignacionRolController::class,'asignarRolGrupo']);
Route::patch('/asignacionGrupoRol/edit',[AsignacionRolController::class,'modificarGruposRoles']);

//Asginacions upms
Route::post('/asginarUpmsProyecto',[AsignacionUpmController::class,'asignacionMasiva']);
Route::get('/obtenerUpmsProyecto',[AsignacionUpmController::class,'obtenerUpmsProyecto']);


//Encuesta
Route::post('/encuesta',[EncuestaController::class,'crearEncuesta']);
Route::patch('/encuesta/edit',[EncuestaController::class,'modificarEncuesta']);
Route::get('/encuestas',[EncuestaController::class,'obtenerEncuestas']);
Route::get('/encuesta/{id}',[EncuestaController::class,'desactivarEncuesta']);

//Proyecto
Route::post('/proyecto',[ProyectoController::class,'crearProyecto']);
Route::patch('/proyecto/edit',[ProyectoController::class,'modificarProyecto']);
Route::get('/proyectos',[ProyectoController::class,'obtenerProyectos']);
Route::get('/proyecto/{id}',[ProyectoController::class,'desactivarProyecto']);
Route::get('/finalizarProyecto/{id}',[ProyectoController::class,'finalizarProyecto']);

//Grupo
Route::post('/grupo',[GrupoController::class,'createGroup']);
Route::get('/grupos',[GrupoController::class,'obtenerGrupos']);
Route::patch('/grupo/edit',[GrupoController::class,'modificarGrupo']);
Route::get('/grupo/{id}',[GrupoController::class,'desactivarGrupo']);


//UPM
Route::post('/upm',[UPMController::class,'crearUpm']);
Route::get('/upms',[UPMController::class,'obtenerUpms']);
Route::post('/upm/edit',[UPMController::class,'modificarUpm']);
Route::get('/upm/{id}',[UPMController::class,'desactivarUpm']);

//Vehiculo
Route::post('/vehiculo',[VehiculoController::class,'crearVehiculo']);
Route::patch('/vehiculo/edit',[VehiculoController::class,'modificarVehiculo']);
Route::get('/vehiculos',[VehiculoController::class,'obtenerVehiculos']);
Route::get('/vehiculo/{id}',[VehiculoController::class,'desactivarVehiculo']);


//Municipio y Departamento
Route::get('/municipios',[MunicipioController::class,'obtenerMunicipios']);
Route::get('/departamentos',[DepartamentoController::class,'obtenerDepartamentos']);

//Asignacion de administradores

Route::post('/asignarAdmin',[AsignacionAdministradorController::class,'asignarAdmin']);

Route::group(['middleware' => ['auth:sanctum']], function() {
    
});



