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
        Schema::table('asignacion_rol', function (Blueprint $table) {
            $table->foreign(['rol_id'], 'fk_Asignacion Rol_Rol1')->references(['id'])->on('rol')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['grupo_id'], 'fk_Asignacion Rol_Grupo1')->references(['id'])->on('grupo')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asignacion_rol', function (Blueprint $table) {
            $table->dropForeign('fk_Asignacion Rol_Rol1');
            $table->dropForeign('fk_Asignacion Rol_Grupo1');
        });
    }
};
