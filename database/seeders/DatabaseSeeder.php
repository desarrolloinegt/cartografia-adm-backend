<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\AsignacionPoliticaPermiso;
use App\Models\AsignacionPoliticaUsuario;
use App\Models\Politica;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Permiso;
use App\Models\Role;
use App\Models\AsignacionPermiso;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{

    private $permisos = ["crear-usuario","editar-usuario","desactivar-usuario","ver-usuario","asignar-roles-usuario"
    ,"editar-encuesta","desactivar-encuesta","crear-encuesta","ver-encuesta","editar-vehiculo","desactivar-vehiculo"
    ,"crear-vehiculo","ver-vehiculo","editar-grupo","desactivar-grupo","crear-grupo","ver-grupo",
    "asignar-rol-grupo","asignar-usuario-grupo","ver-usuario-grupo","eliminar-usuario-grupo","editar-rol",
    "desactivar-rol","crear-rol","asignar-permiso-rol","ver-rol","administrar-proyecto","editar-proyecto","desactivar-proyecto",
    "crear-proyecto","ver-proyecto","finalizar-proyecto","asignar-upm-proyecto","ver-upms","reemplazar-upm",
    "descargar-plantilla","asignar-personal","asignar-upms-personal","ver-upms-cartografo","inicializar-actualizacion","finalizar-actualizacion",
    "ver-equipo-campo","ver-usuarios-equipo-campo"];

    private $policyAdmin = "Administrador";
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        try{
            $user = User::create([
                'DPI' => '1234567891234',
                'nombres' => 'Admin',
                'apellidos' => 'Admin',
                'email' => 'admin@example.com',
                'codigo_usuario' => '1234',
                'estado_usuario' => 1,
                'password' => Hash::make('12345789z'),
                'telefono'=>'12345678',
                'descripcion'=>''
            ]);
        } catch(\Throwable $th){

        }
       
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
        $politica='';
        try{
            $politica = Politica::create([
                "nombre"=>$this->policyAdmin,
                "estado"=>1
            ]);
            $asignacionPoliticaUsuario=AsignacionPoliticaUsuario::create([
                'usuario_id'=>$user->id,
                'politica_id'=>$politica->id
            ]);
        }catch(\Throwable $th){
            $politica = Politica::select('id')
                ->where("nombre",$this->policyAdmin)
                ->first();
        }
        
        if(isset($politica)){
            foreach ($permisosCreados as $permiso) {
                try{
                    AsignacionPoliticaPermiso::create([
                        "permiso_id"=>$permiso->id,
                        "politica_id"=>$politica->id
                    ]);
                }catch(\Throwable $th){
                }
            }
        }
    }
}
