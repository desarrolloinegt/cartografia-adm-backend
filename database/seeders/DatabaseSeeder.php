<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\AsignacionPermiso;
class DatabaseSeeder extends Seeder
{

    private $permisos = ["crear-usuario","editar-usuario","desactivar-usuario","ver-usuario","asignar-roles-usuario"
    ,"editar-encuesta","desactivar-encuesta","crear-encuesta","ver-encuesta","editar-vehiculo","desactivar-vehiculo"
    ,"crear-vehiculo","ver-vehiculo","editar-grupo","desactivar-grupo","crear-grupo","ver-grupo",
    "asignar-rol-grupo","asignar-usuario-grupo","ver-usuario-grupo","eliminar-usuario-grupo","editar-rol","desactivar-rol","crear-rol","asignar-permiso-rol","ver-rol","editar-proyecto","desactivar-proyecto",
    "crear-proyecto","ver-proyecto","finalizar-proyecto","asignar-upm-proyecto","ver-upms","reemplazar-upms","asignar-personal","asignar-upms-personal"];

    private $roleAdmin = "Administrador";
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        
        foreach ($this->permisos as $permiso) {
            try{
                Permiso::create([
                    "alias"=>$permiso,
                    "estado"=>1
                ]);
            }catch(\Throwable $th){

            }
        }
        $permisosCreados = Permiso::select('id')
            ->where('estado',1)
            ->get();
        $role='';
        try{
            $role = Role::create([
                "nombre"=>$this->roleAdmin,
                "estado"=>1
            ]);
        }catch(\Throwable $th){
            $role = Role::select('id')
                ->where("nombre",$this->roleAdmin)
                ->first();
        }
        
        if(isset($role)){
            foreach ($permisosCreados as $permiso) {
                try{
                    AsignacionPermiso::create([
                        "permiso_id"=>$permiso->id,
                        "rol_id"=>$role->id
                    ]);
                }catch(\Throwable $th){
    
                }
            }
        }
    }
}
