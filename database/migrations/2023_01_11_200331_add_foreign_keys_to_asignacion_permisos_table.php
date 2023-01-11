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
        Schema::table('asignacion_permisos', function (Blueprint $table) {
            $table->foreign(['rol_id'], 'fk_Asignacion Permisos_Rol1')->references(['id'])->on('rol')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['permiso_Id'], 'fk_Asignacion Permisos_Permiso1')->references(['id'])->on('permiso')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asignacion_permisos', function (Blueprint $table) {
            $table->dropForeign('fk_Asignacion Permisos_Rol1');
            $table->dropForeign('fk_Asignacion Permisos_Permiso1');
        });
    }
};
