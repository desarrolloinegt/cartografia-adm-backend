<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Asignacion Permisos', function (Blueprint $table) {
            $table->foreign(['Permiso_Id'], 'fk_Asignacion Permisos_Permiso1')->references(['Id'])->on('Permiso')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['Rol_Id'], 'fk_Asignacion Permisos_Rol1')->references(['Id'])->on('Rol')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Asignacion Permisos', function (Blueprint $table) {
            $table->dropForeign('fk_Asignacion Permisos_Permiso1');
            $table->dropForeign('fk_Asignacion Permisos_Rol1');
        });
    }
};
