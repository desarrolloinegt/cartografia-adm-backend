<?php

use App\Http\Controllers\Permiso\AsignacionPermisoPoliticaController;
use App\Http\Controllers\Politica\AsignacionPoliticaUsuarioController;
use App\Http\Controllers\ResetPassowordController;
use App\Http\Controllers\Rol\AsignacionRolPoliticaController;
use App\Http\Controllers\Rol\AsignacionRolUsuarioController;
use App\Http\Controllers\UPM\AsignacionUpmProyectoController;
use App\Http\Controllers\Work\CargaTrabajoController;
use App\Http\Controllers\Work\ControlProgresoController;
use App\Http\Controllers\Work\EquipoCampoController;
use App\Http\Controllers\Work\OrganizacionController;
use App\Http\Controllers\Politica\PoliticaController;
use App\Http\Controllers\UPM\ReemplazoUpmController;
use App\Http\Controllers\Rol\RolController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UsuarioController;
use App\Http\Controllers\Permiso\PermisoController;
use App\Http\Controllers\Survey\EncuestaController;
use App\Http\Controllers\Project\ProyectoController;
use App\Http\Controllers\UPM\UPMController;
use App\Http\Controllers\DepartmentMunicipality\DepartamentoController;
use App\Http\Controllers\DepartmentMunicipality\MunicipioController;
use App\Http\Controllers\Vehicle\VehiculoController;

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
Route::post('/generateTokenReset', [ResetPassowordController::class, 'generateTokenReset']);
Route::post('/verifyToken', [ResetPassowordController::class, 'validateToken']);
Route::post('/resetPassword',[ResetPassowordController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->id;
});


Route::group(['middleware' => ['auth:sanctum']], function () {

    //politica
    Route::post('/politica', [PoliticaController::class, 'createPolicy']);
    Route::get('/politicas', [PoliticaController::class, 'getPolicys']);
    Route::get('/politicasSistema', [PoliticaController::class, 'getSystemPolicys']);
    Route::get('/politicasProyecto', [PoliticaController::class, 'getProjectPolicys']);
    Route::get('/politica/{id}', [PoliticaController::class, 'desactivePolicy']);
    Route::patch('/politica/edit', [PoliticaController::class, 'editPolicy']);

    //permiso
    Route::get('/permisos', [PermisoController::class, 'getPermissions']);
    Route::get('/permisosSistema', [PermisoController::class, 'getSytemPermissions']);
    Route::get('/permisosProyecto', [PermisoController::class, 'getProjectPermissions']);
    //usuario

    Route::post('/registro', [UsuarioController::class, 'register']);
    Route::patch('/usuario/edit', [UsuarioController::class, 'editUser']);
    Route::get('/usuario/{id}', [UsuarioController::class, 'desactiveUser']);
    Route::get('/usuarios', [UsuarioController::class, 'getUsers']);
    Route::get('/usuariosList', [UsuarioController::class, 'getUsersList']);
    Route::get('/projectsAssing/{id}', [UsuarioController::class, 'getProjectsUser']);
    Route::post('/obtenerPermisos', [UsuarioController::class, 'getPermissionProjectUser']);
    Route::get('/obtenerPermisosDirectos/{id}', [UsuarioController::class, 'getPermissionSystem']);
    Route::post('/logout', [UsuarioController::class, 'logout']);


    //asignaciones usuario politica
    Route::get('/obtenerPoliticaUser/{id}', [AsignacionPoliticaUsuarioController::class, 'getUserPolicy']);
    Route::patch('/asignarPoliticaUser', [AsignacionPoliticaUsuarioController::class, 'asignnUserPolicy']);


    //asginaciones rol usuario
    Route::post('/asignarUsuariosRol', [AsignacionRolUsuarioController::class, 'asignnRolUsers']);
    Route::get('/obtenerUsuariosRol/{id}', [AsignacionRolUsuarioController::class, 'getUsersRol']);
    Route::post('/asignacionUsuarioRol', [AsignacionRolUsuarioController::class, 'assignUserRol']);
    Route::patch('/eliminarUsuarioRol', [AsignacionRolUsuarioController::class, 'deleteUserRol']);


    //asgingaciones politica Permiso
    Route::post('/asignarPermiso', [AsignacionPermisoPoliticaController::class, 'asignnment']);
    Route::get('/obtenerPoliticaPermisos/{id}', [AsignacionPermisoPoliticaController::class, 'getPolicyPermission']);


    //asgingaciones rol Politicas
    Route::get('/obtenerRolPoliticas/{id}', [AsignacionRolPoliticaController::class, 'getRolesPolicy']);
    Route::patch('/asignarRolPoliticas', [AsignacionRolPoliticaController::class, 'modifyRolesPolicys']);

    //Asginacions upms proyecto
    Route::post('/asginarUpmsProyecto', [AsignacionUpmProyectoController::class, 'assign']);
    Route::patch('/sustituirUpm', [AsignacionUpmProyectoController::class, 'replaceUpm']);
    Route::get('/obtenerUpmsProyecto/{id}', [AsignacionUpmProyectoController::class, 'getUpmsProject']);

    //Encuesta
    Route::post('/encuesta', [EncuestaController::class, 'createSurvey']);
    Route::patch('/encuesta/edit', [EncuestaController::class, 'editSurvey']);
    Route::get('/encuestas', [EncuestaController::class, 'getSurveys']);
    Route::get('/encuesta/{id}', [EncuestaController::class, 'desactiveSurvey']);

    //Proyecto
    Route::post('/proyecto', [ProyectoController::class, 'createProject']);
    Route::patch('/proyecto/edit', [ProyectoController::class, 'editProject']);
    Route::get('/proyectos', [ProyectoController::class, 'getProjects']);
    Route::get('/proyectoId/{projecto}', [ProyectoController::class, 'getProjectId']);
    Route::get('/proyecto/{id}', [ProyectoController::class, 'desactiveProject']);
    Route::get('/obtenerRolesProyecto/{proyecto}', [ProyectoController::class, 'getRolesProject']);
    Route::get('/finalizarProyecto/{id}', [ProyectoController::class, 'finishProject']);

    //Rol
    Route::post('/rol', [RolController::class, 'createRol']);
    Route::get('/roles', [RolController::class, 'getRoles']);
    Route::patch('/jerarquias', [RolController::class, 'editHierarchy']);
    Route::patch('/rol/edit', [RolController::class, 'editRol']);
    Route::get('/rol/{id}', [RolController::class, 'desactiveRol']);
    Route::post('/seleccionarRolesMenores', [RolController::class, 'getMinorRoles']);



    //Vehiculo
    Route::post('/vehiculo', [VehiculoController::class, 'createVehicle']);
    Route::patch('/vehiculo/edit', [VehiculoController::class, 'editVehicle']);
    Route::get('/vehiculos', [VehiculoController::class, 'getVehicles']);
    Route::get('/vehiculo/{id}', [VehiculoController::class, 'desactiveVehicle']);


    //Municipio y Departamento
    Route::get('/municipios', [MunicipioController::class, 'getMunicipality']);
    Route::get('/departamentos', [DepartamentoController::class, 'getDepartaments']);
    Route::post('/cargarMunicipios', [MunicipioController::class, 'cargarMunicipios']);
    Route::post('/cargarDepartamentos', [DepartamentoController::class, 'chargeDepartments']);

    //Asignacion de carga de trabajo
    Route::patch('/iniciarActualizacion', [CargaTrabajoController::class, 'initActualization']);
    Route::patch('/finalizarActualizacion', [CargaTrabajoController::class, 'finishActualization']);
    Route::post('/asignarUpmPersonal', [CargaTrabajoController::class, 'assignn']);
    Route::post('/obtenerUpmPersonal', [CargaTrabajoController::class, 'getUpmsPersonal']);
    Route::post('/obtenerUpmsAsignados', [CargaTrabajoController::class, 'getUpmsAssigned']);

    //asignacion de personal
    Route::post('/asignarPersonal', [OrganizacionController::class, 'assignn']);
    Route::post('/obtenerEncargadoEmpleado', [OrganizacionController::class, 'getAsignnments']);
    Route::post('/obtenerPersonalAsignado', [OrganizacionController::class, 'obtenerPersonalAsignado']);
    Route::patch('/eliminarAsignacionPersonal', [OrganizacionController::class, 'deleteAssignmentOrganization']);
    //Reemplazo de upm
    Route::get('/detalleSustitucion/{id}', [ReemplazoUpmController::class, 'seeDetails']);

    //Cargar Upms
    Route::post('/cargarUpms', [UPMController::class, 'chargueUpms']);

    //Obtener upms del cartografo
    Route::post('/obtenerUpmCartografo', [CargaTrabajoController::class, 'getUpmCartographer']);
    Route::patch('/upmCartografoSupervisor/edit', [CargaTrabajoController::class, 'modifyUpmCartographer']);
    //Obtener upm del supervisor
    Route::post('/obtenerUpmSupervisor', [CargaTrabajoController::class, 'getUpmSupervisor']);
    //Obtener cartografos del supervisor
    Route::post('/obtenerCartografosSupervisor', [CargaTrabajoController::class, 'getCartographerSupervisor']);


    //Control de progreso
    Route::post('/bitacoraUpm', [ControlProgresoController::class, 'getLogUpm']);
    Route::post('/progresoUpms', [ControlProgresoController::class, 'getProgressDashboard']);
    Route::post('/departamentosProyecto', [ControlProgresoController::class, 'getDepartmentsProject']);
    Route::post('/dataDepartamentosProyecto', [ControlProgresoController::class, 'getDataDeparments']);

    //Equipos
    Route::post('/equipo', [EquipoCampoController::class, 'createTeams']);
    Route::patch('/equipoVehicle/edit', [EquipoCampoController::class, 'modifyVehicle']);
    Route::patch('/equipo/edit', [EquipoCampoController::class, 'editTeam']);
    Route::patch('/addVehiculo', [EquipoCampoController::class, 'assignVehicle']);
    Route::post('/equipos', [EquipoCampoController::class, 'getTeams']);
    Route::post('/addEquipo', [EquipoCampoController::class, 'addTeam']);
    Route::post('/miembrosEquipo', [EquipoCampoController::class, 'getUsersTeam']);
});