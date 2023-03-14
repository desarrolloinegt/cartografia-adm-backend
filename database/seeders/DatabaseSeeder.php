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

    private $permisos = [["crear-usuario",1],["editar-usuario",1],["desactivar-usuario",1],["ver-usuario",1],["editar-encuesta",1],["desactivar-encuesta",1],["crear-encuesta",1],["ver-encuesta",1],["editar-vehiculo",1],["desactivar-vehiculo",1]
    ,["crear-vehiculo",1],["ver-vehiculo",1],["editar-rol",1],["desactivar-rol",1],["crear-rol",1],["ver-rol",1],["ver-rol-proyecto",0],
    ["editar-rol-proyecto",0],["desactivar-rol-proyecto",0],["crear-rol_proyecto",0],["asignar-rol-politica",1],["asignar-usuario-rol",1],
    ["ver-usuario-rol",1],["eliminar-usuario-rol",1],["editar-politica",1],["desactivar-politica",1],["crear-politica",1],["asignar-permiso-politica",1],
    ["ver-politica",1],["editar-proyecto",1],["desactivar-proyecto",1],["crear-proyecto",1],["ver-proyecto",1],
    ["finalizar-proyecto",1],["asignar-upm-proyecto",0],["ver-upms",0],["reemplazar-upm",0],["descargar-plantilla",0],["asignar-personal",0],
    ["asignar-upms-personal",0],["ver-upms-cartografo",0],["inicializar-actualizacion",0],["finalizar-actualizacion",0],
    ["ver-equipo-campo",0],["ver-usuarios-equipo-campo",0],["editar-equipo-campo",0],["agregar-vehiculo-equipo-campo",0],["crear-equipo-campo",0],
    ["editar-cartografo-upm",0],["ver-mapa",0],["supervisar",0],["asignar-usuario-politica",1],["asignar-rol-politica-proyecto",0],["asignar-usuario-rol-proyecto",0],
    ["ver-usuario-rol-proyecto",0],["eliminar-usuario-rol-proyecto",0]];

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
                    "alias"=>$permiso[0],
                    "estado"=>1,
                    "permiso_sistema"=>$permiso[1]
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
