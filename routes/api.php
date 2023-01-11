<?php

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
Route::post('/rol/edit',[RoleController::class,'modificarRol']);

//permiso
Route::get('/permisos',[PermisoController::class,'obtenerPermisos']);

//usuario

Route::post('/registro',[UsuarioController::class,'register']);
Route::post('/usuario/edit',[UsuarioController::class,'modificarUsuario']);
Route::get('/usuario/{id}',[UsuarioController::class,'desactivarUsuario']);
Route::get('/usuarios',[UsuarioController::class,'obtenerUsuarios']);
Route::post('/logout',[UsuarioController::class,'logout'])->middleware('auth:sanctum');


//asginaciones grupo

Route::post('/asignarGrupoUsuario',[AsignacionGrupoController::class,'asignarGrupoUsuario']);
Route::post('/asignacionGrupoUsuario/eliminar',[AsignacionGrupoController::class,'eliminarAsignacion']);
Route::post('/asignarGruposUsuarios',[AsignacionGrupoController::class,'asignacionMasiva']);


//asgingaciones rol
Route::post('/asignarPermiso',[AsignacionPermisoController::class,'asignarPermisoRol']);
Route::post('/asignacionRolPermiso/eliminar',[AsignacionPermisoController::class,'eliminarAsignacion']);
Route::post('/asignarGrupoRol',[AsignacionRolController::class,'asignarRolGrupo']);
Route::post('/asignacionGrupoRol/eliminar',[AsignacionRolController::class,'eliminarAsignacion']);


//Encuesta
Route::post('/encuesta',[EncuestaController::class,'crearEncuesta']);
Route::post('/encuesta/edit',[EncuestaController::class,'modificarEncuesta']);
Route::get('/encuestas',[EncuestaController::class,'obtenerEncuestas']);
Route::delete('/encuesta/{id}',[EncuestaController::class,'eliminarEncuesta']);

//Proyecto
Route::post('/proyecto',[ProyectoController::class,'crearProyecto']);
Route::post('/proyecto/edit',[ProyectoController::class,'modificarProyecto']);
Route::get('/proyectos',[ProyectoController::class,'obtenerProyectos']);
Route::get('/proyecto/{id}',[ProyectoController::class,'desactivarProyecto']);


//Grupo
Route::post('/grupo',[GrupoController::class,'createGroup']);
Route::get('/grupos',[GrupoController::class,'obtenerGrupos']);
Route::post('/grupo/edit',[GrupoController::class,'modificarGrupo']);
Route::get('/grupo/{id}',[GrupoController::class,'desactivarGrupo']);


//UPM
Route::post('/upm',[UPMController::class,'crearUpm']);
Route::get('/upms',[UPMController::class,'obtenerUpms']);
Route::post('/upm/edit',[UPMController::class,'modificarUpm']);
Route::get('/upm/{id}',[UPMController::class,'desactivarUpm']);

//Municipio y Departamento
Route::get('/municipios',[MunicipioController::class,'obtenerMunicipios']);
Route::get('/departamentos',[DepartamentoController::class,'obtenerDepartamentos']);

Route::group(['middleware' => ['auth:sanctum']], function() {
    
});



