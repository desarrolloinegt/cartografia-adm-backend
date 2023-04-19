<?php

namespace Database\Seeders;

use App\Models\AsignacionPoliticaPermiso;
use App\Models\AsignacionPoliticaUsuario;
use App\Models\Politica;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Permiso;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{

    private $permissions = [
        ["crear-usuario", 1],
        ["editar-usuario", 1],
        ["desactivar-usuario", 1],
        ["ver-usuario", 1],
        ["editar-encuesta", 1],
        ["desactivar-encuesta", 1],
        ["crear-encuesta", 1],
        ["ver-encuesta", 1],
        ["editar-vehiculo", 1],
        ["desactivar-vehiculo", 1],
        ["crear-vehiculo", 1],
        ["ver-vehiculo", 1],
        ["editar-rol", 1],
        ["desactivar-rol", 1],
        ["crear-rol", 1],
        ["ver-rol", 1],
        ["ver-rol-proyecto", 0],
        ["editar-rol-proyecto", 0],
        ["desactivar-rol-proyecto", 0],
        ["crear-rol_proyecto", 0],
        ["asignar-rol-politica", 1],
        ["asignar-usuario-rol", 1],
        ["ver-usuario-rol", 1],
        ["eliminar-usuario-rol", 1],
        ["editar-politica", 1],
        ["desactivar-politica", 1],
        ["crear-politica", 1],
        ["asignar-permiso-politica", 1],
        ["ver-politica", 1],
        ["editar-proyecto", 1],
        ["desactivar-proyecto", 1],
        ["crear-proyecto", 1],
        ["ver-proyecto", 1],
        ["finalizar-proyecto", 1],
        ["asignar-upm-proyecto", 0],
        ["ver-upms", 0],
        ["reemplazar-upm", 0],
        ["descargar-plantilla", 0],
        ["asignar-personal", 0],
        ["asignar-upms-personal", 0],
        ["ver-upms-cartografo", 0],
        ["inicializar-actualizacion", 0],
        ["finalizar-actualizacion", 0],
        ["ver-equipo-campo", 0],
        ["ver-usuarios-equipo-campo", 0],
        ["editar-equipo-campo", 0],
        ["agregar-vehiculo-equipo-campo", 0],
        ["crear-equipo-campo", 0],
        ["editar-cartografo-upm", 0],
        ["ver-mapa", 0],
        ["supervisar", 0],
        ["asignar-usuario-politica", 1],
        ["asignar-rol-politica-proyecto", 0],
        ["asignar-usuario-rol-proyecto", 0],
        ["ver-usuario-rol-proyecto", 0],
        ["eliminar-usuario-rol-proyecto", 0],
        ["configuracion-proyecto", 0]
    ];
    private $permissionChiefActualization = [
        "ver-rol-proyecto",
        "editar-rol-proyecto",
        "desactivar-rol-proyecto",
        "crear-rol_proyecto",
        "asignar-upm-proyecto",
        "ver-upms",
        "reemplazar-upm",
        "descargar-plantilla",
        "asignar-personal",
        "asignar-upms-personal",
        "asignar-rol-politica-proyecto",
        "asignar-usuario-rol-proyecto",
        "ver-usuario-rol-proyecto",
        "eliminar-usuario-rol-proyecto",
        "configuracion-proyecto"
    ];
    private $permissionMonitor = [
        "descargar-plantilla",
        "asignar-personal",
        "asignar-upms-personal",
        "ver-equipo-campo",
        "ver-usuarios-equipo-campo",
        "editar-equipo-campo",
        "agregar-vehiculo-equipo-campo",
        "crear-equipo-campo"
    ];
    private $permissionSupervisor = ["descargar-plantilla", "asignar-upms-personal", "editar-cartografo-upm", "ver-mapa", "supervisar"];
    private $permissionCartographer = ["ver-upms-cartografo", "inicializar-actualizacion", "finalizar-actualizacion"];
    private $policyAdmin = "Administrador";
    private $policyMonitor = "Monitor";
    private $policyCartographer = "Cartografo";
    private $policySupervisor = "Supervisor";
    private $policyChiefActualization = "Jefe-Actualizacion";
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /**Crear primer usuario */
        $userAdmin = User::where('id',1)->first();
        if (!isset($userAdmin)) {
            $userAdmin = User::create([
                'DPI' => '1234567891234',
                'nombres' => 'Admin',
                'apellidos' => 'Admin',
                'email' => 'admin@example.com',
                'codigo_usuario' => '1234',
                'estado_usuario' => 1,
                'password' => Hash::make('12345789z'),
                'telefono' => '12345678',
                'descripcion' => ''
            ]);
        } 

        /**Funcion para crear los permisos */
        $this->addPermission($this->permissions);

        /**Crear politica de administrador y gregar todos los permisos al administrador */
        $this->assignPolicyAdmin($userAdmin);

        /**Funcion para crear politicas basicas y agregar sus permisos */
        $this->createBasicPolicys();


    }

    private function addPermission($permissions)
    {
        foreach ($permissions as $permission) {
            $exist = Permiso::where('alias', $permission[0])->first();
            if (!isset($exist)) {
                Permiso::create([
                    "alias" => $permission[0],
                    "estado" => 1,
                    "permiso_sistema" => $permission[1]
                ]);
            }
        }
    }

    private function createBasicPolicys()
    {
        try {

            $supervisor = Politica::where('nombre', $this->policySupervisor)->first();
            $cartographer = Politica::where('nombre', $this->policyCartographer)->first();
            $chiefActualization = Politica::where('nombre', $this->policyChiefActualization)->first();
            $monitor = Politica::where('nombre', $this->policyMonitor)->first();
            if (!isset($supervisor)) {
                $supervisor = Politica::create([
                    "nombre" => $this->policySupervisor,
                    "estado" => 1,
                    "politica_sistema" => 0
                ]);
            }
            if (!isset($cartographer)) {
                $cartographer = Politica::create([
                    "nombre" => $this->policyCartographer,
                    "estado" => 1,
                    "politica_sistema" => 0
                ]);
            }
            if (!isset($chiefActualization)) {
                $chiefActualization = Politica::create([
                    "nombre" => $this->policyChiefActualization,
                    "estado" => 1,
                    "politica_sistema" => 0
                ]);
            }
            if (!isset($monitor)) {
                $monitor = Politica::create([
                    "nombre" => $this->policyMonitor,
                    "estado" => 1,
                    "politica_sistema" => 0
                ]);
            }
            $this->assignPermissionToBasicPolicy($chiefActualization, $monitor, $supervisor, $cartographer);
        } catch (\Throwable $th) {}
    }

    private function assignPermissionToBasicPolicy($chiefActualization, $monitor, $supervisor, $cartographer)
    {
        $permissionList = Permiso::select('id')
            ->where('estado', 1)
            ->where('politica_sistema', 0)
            ->get();
        
        //Asignar permisos a politica jefe de actualizacion    
        foreach ($this->permissionChiefActualization as $permission) {
            try {
                $perm = Permiso::where('alias', $permission)->first();
                if (isset($perm)) {
                    AsignacionPoliticaPermiso::create([
                        'permiso_id' => $perm->id,
                        'politica_id' => $chiefActualization->id
                    ]);
                }
            } catch (\Throwable $th) {}

        }

        //Asignar permisos a politica de monitor
        foreach ($this->permissionMonitor as $permission) {
            try {
                $perm = Permiso::where('alias', $permission)->first();
                if (isset($perm)) {
                    AsignacionPoliticaPermiso::create([
                        'permiso_id' => $perm->id,
                        'politica_id' => $monitor->id
                    ]);
                }
            } catch (\Throwable $th) {}
        }

        //Asignar permisos a politica de supervisor
        foreach ($this->permissionSupervisor as $permission) {
            try {
                $perm = Permiso::where('alias', $permission)->first();
                if (isset($perm)) {
                    AsignacionPoliticaPermiso::create([
                        'permiso_id' => $perm->id,
                        'politica_id' => $supervisor->id
                    ]);
                }
            } catch (\Throwable $th) {}
        }

        //Asignar permisos a politica de cartografo
        foreach ($this->permissionCartographer as $permission) {
            try {
                $perm = Permiso::where('alias', $permission)->first();
                if (isset($perm)) {
                    AsignacionPoliticaPermiso::create([
                        'permiso_id' => $perm->id,
                        'politica_id' => $cartographer->id
                    ]);
                }
            } catch (\Throwable $th) {}
        }
    }

    /**
     * @param $user, parametro con el usuario administrador
     * Funcion para crear la politica de administrador y asignarme todos los permisos
     */
    private function assignPolicyAdmin(User $user)
    {
        $permissionList = Permiso::select('id')
            ->where('estado', 1)
            ->get();
        $policy = '';
        try {
            $policy = Politica::where('nombre', $this->policyAdmin)->first();
            if (!isset($policy)) {
                $policy = Politica::create([
                    "nombre" => $this->policyAdmin,
                    "estado" => 1,
                    "politica_sistema" => 1
                ]);
            }
            AsignacionPoliticaUsuario::create([
                'usuario_id' => $user->id,
                'politica_id' => $policy->id
            ]);
            if (isset($policy)) {
                foreach ($permissionList as $permission) {
                    try {
                        AsignacionPoliticaPermiso::create([
                            "permiso_id" => $permission->id,
                            "politica_id" => $policy->id
                        ]);
                    } catch (\Throwable $th) {
                    }
                }
            }
        } catch (\Throwable $th) {
        }
    }
}