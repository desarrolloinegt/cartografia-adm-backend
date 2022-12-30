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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//roles

Route::post('/rol',[RoleController::class,'createRole']);
Route::get('/roles',[RoleController::class,'obtenerRoles']);
Route::get('/rol/{id}',[RoleController::class,'desactivarRol']);
Route::post('/rol/edit',[RoleController::class,'modificarRol']);


//usuario
Route::post('/login',[UsuarioController::class,'login']);
Route::post('/registro',[UsuarioController::class,'register']);
Route::get('/usuario/{id}',[UsuarioController::class,'desactivarUsuario']);
Route::get('/usuarios',[UsuarioController::class,'obtenerUsuarios']);
Route::post('/logout',[UsuarioController::class,'logout'])->middleware('auth:sanctum');


//asginaciones
Route::post('/asignarPermiso',[AsignacionPermisoController::class,'asignarPermisoRol']);
Route::post('/asignarRol',[AsignacionRolController::class,'asignarRolGrupo']);
Route::post('/asignarGrupo',[AsignacionGrupoController::class,'asignarGrupoUsuario']);

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


Route::post('/permiso',[PermisoController::class,'createPermiso']);



Route::group(['middleware' => ['auth:sanctum','ability:admin']], function() {
    
});



