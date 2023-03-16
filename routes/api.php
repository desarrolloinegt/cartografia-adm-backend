<?php

use App\Http\Controllers\AsignacionAdministradorController;
use App\Http\Controllers\AsignacionPermisoPoliticaController;
use App\Http\Controllers\AsignacionPoliticaUsuarioController;
use App\Http\Controllers\AsignacionRolPoliticaController;
use App\Http\Controllers\AsignacionRolUsuarioController;
use App\Http\Controllers\AsignacionUpmProyectoController;
use App\Http\Controllers\CargaTrabajoController;
use App\Http\Controllers\ControlProgresoController;
use App\Http\Controllers\EquipoCampoController;
use App\Http\Controllers\OrganizacionController;
use App\Http\Controllers\PoliticaController;
use App\Http\Controllers\ReemplazoUpmController;
use App\Http\Controllers\RolController;
use App\Models\AsignacionPoliticaUsuario;
use App\Models\AsignacionUpmProyecto;
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
    return $request->user()->id;
});


Route::group(['middleware' => ['auth:sanctum']], function () {
    
    //politica
    Route::post('/politica', [PoliticaController::class, 'createPolicy']);
    Route::get('/politicas', [PoliticaController::class, 'obtenerPoliticas']);
    Route::get('/politicasSistema', [PoliticaController::class, 'obtenerPoliticasSistema']);
    Route::get('/politicasProyecto', [PoliticaController::class, 'obtenerPoliticasProyecto']);
    Route::get('/politica/{id}', [PoliticaController::class, 'desactivarPolitica']);
    Route::patch('/politica/edit', [PoliticaController::class, 'modificarPolitica']);

    //permiso
    Route::get('/permisos', [PermisoController::class, 'obtenerPermisos']);
    Route::get('/permisosSistema', [PermisoController::class, 'obtenerPermisosSistema']);
    Route::get('/permisosProyecto', [PermisoController::class, 'obtenerPermisosProyecto']);
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
 

    //asignaciones usuario politica
    Route::get('/obtenerPoliticaUser/{id}',[AsignacionPoliticaUsuarioController::class,'obtenerUsuarioPoliticas']);
    Route::patch('/asignarPoliticaUser',[AsignacionPoliticaUsuarioController::class,'asignarUsuarioPolitica']);


    //asginaciones grupo usuario
    Route::post('/asignarUsuariosRol', [AsignacionRolUsuarioController::class, 'asignacionMasiva']);
    Route::get('/obtenerUsuariosRol/{id}', [AsignacionRolUsuarioController::class, 'obtenerUsuariosRol']);
    Route::post('/asignacionUsuarioRol', [AsignacionRolUsuarioController::class, 'asignarUsuariosRol']);
    Route::patch('/eliminarUsuarioRol', [AsignacionRolUsuarioController::class, 'eliminarUsuarioRol']);


    //asgingaciones politica Permiso
    Route::post('/asignarPermiso', [AsignacionPermisoPoliticaController::class, 'asignacionMasiva']);
    Route::get('/obtenerPoliticaPermisos/{id}', [AsignacionPermisoPoliticaController::class, 'obtenerPoliticaPermisos']);


    //asgingaciones rol Politicas
    Route::get('/obtenerRolPoliticas/{id}', [AsignacionRolPoliticaController::class, 'obtenerRolesPoliticas']);
    Route::patch('/asignarRolPoliticas', [AsignacionRolPoliticaController::class, 'modificarRolesPoliticas']);

    //Asginacions upms
    Route::post('/asginarUpmsProyecto', [AsignacionUpmProyectoController::class, 'asignacionMasiva']);
    Route::patch('/sustituirUpm', [AsignacionUpmProyectoController::class, 'sustituirUpm']);
    Route::get('/obtenerUpmsProyecto/{id}', [AsignacionUpmProyectoController::class, 'obtenerUpmsProyecto']);

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
    Route::get('/obtenerRolesProyecto/{proyecto}', [ProyectoController::class, 'obtenerGruposPorProyecto']);
    Route::get('/finalizarProyecto/{id}', [ProyectoController::class, 'finalizarProyecto']);

    //Rol
    Route::post('/rol', [RolController::class, 'createRol']);
    Route::get('/roles', [RolController::class, 'obtenerRoles']);
    Route::patch('/jerarquias', [RolController::class, 'modificarJerarquias']);
    Route::patch('/rol/edit', [RolController::class, 'modificarRol']);
    Route::get('/rol/{id}', [RolController::class, 'desactivarRol']);
    Route::post('/seleccionarRolesMenores', [RolController::class, 'seleccionarRolesMenores']);



    //Vehiculo
    Route::post('/vehiculo', [VehiculoController::class, 'crearVehiculo']);
    Route::patch('/vehiculo/edit', [VehiculoController::class, 'modificarVehiculo']);
    Route::get('/vehiculos', [VehiculoController::class, 'obtenerVehiculos']);
    Route::get('/vehiculo/{id}', [VehiculoController::class, 'desactivarVehiculo']);


    //Municipio y Departamento
    Route::get('/municipios', [MunicipioController::class, 'obtenerMunicipios']);
    Route::get('/departamentos', [DepartamentoController::class, 'obtenerDepartamentos']);
    Route::post('/cargarMunicipios', [MunicipioController::class, 'cargarMunicipios']);
    Route::post('/cargarDepartamentos', [DepartamentoController::class, 'cargarDepartamentos']);

    //Asignacion de carga de trabajo
    Route::post('/asignarPersonal/{id}', [CargaTrabajoController::class, 'asignarPersonal']);
    Route::patch('/iniciarActualizacion', [CargaTrabajoController::class, 'initActualization']);
    Route::patch('/finalizarActualizacion', [CargaTrabajoController::class, 'finishActualization']);
    
    Route::post('/asignarUpmPersonal',[CargaTrabajoController::class,'asignarUpmsAPersonal']);
    Route::post('/obtenerUpmPersonal',[CargaTrabajoController::class,'obtenerUpmsPersonal']);
    Route::post('/obtenerUpmsAsignados',[CargaTrabajoController::class,'obtenerUpmsAsignados']);

    //asignacion de personal
    Route::post('/asignarPersonal',[OrganizacionController::class,'asignarPersonal']);
    Route::post('/obtenerEncargadoEmpleado',[OrganizacionController::class,'obtenerAsignacionesPersonal']);
    Route::post('/obtenerPersonalAsignado',[OrganizacionController::class,'obtenerPersonalAsignado']);
    Route::patch('/eliminarAsignacionPersonal',[OrganizacionController::class,'deleteAssignmentOrganization']);
    //Reemplazo de upm
    Route::get('/detalleSustitucion/{id}',[ReemplazoUpmController::class,'verDetalle']);

    //Cargar Upms
    Route::post('/cargarUpms', [UPMController::class, 'cargarUpms']);

    //Obtener upms del cartografo
    Route::post('/obtenerUpmCartografo',[CargaTrabajoController::class,'obtenerUpmCartografos']);
    Route::patch('/upmCartografoSupervisor/edit',[CargaTrabajoController::class,'modifyUpmCartographer']);
    //Obtener upm del supervisor
    Route::post('/obtenerUpmSupervisor',[CargaTrabajoController::class,'getUpmSupervisor']);
    //Obtener cartografos del supervisor
    Route::post('/obtenerCartografosSupervisor',[CargaTrabajoController::class,'getCartographerSupervisor']);
    

    //Control de progreso
    Route::post('/bitacoraUpm',[ControlProgresoController::class,'getLogUpm']);
    
    //Equipos
    Route::post('/equipo',[EquipoCampoController::class,'createTeams']);
    Route::patch('/equipoVehicle/edit',[EquipoCampoController::class,'modifyVehicle']);
    Route::patch('/equipo/edit',[EquipoCampoController::class,'editTeam']);
    Route::patch('/addVehiculo',[EquipoCampoController::class,'assignVehicle']);
    Route::post('/equipos',[EquipoCampoController::class,'getTeams']);
    Route::post('/addEquipo',[EquipoCampoController::class,'addTeam']);
    Route::post('/miembrosEquipo',[EquipoCampoController::class,'getUsersTeam']);
});