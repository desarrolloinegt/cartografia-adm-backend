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
        Schema::table('asignacion_rol_politica', function (Blueprint $table) {
            $table->foreign(['rol_id'], 'fk_Asignacion Rol_Grupo1')->references(['id'])->on('rol')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['politica_id'], 'fk_Asignacion Rol_Rol1')->references(['id'])->on('politica')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asignacion_rol_politica', function (Blueprint $table) {
            $table->dropForeign('fk_Asignacion Rol_Grupo1');
            $table->dropForeign('fk_Asignacion Rol_Rol1');
        });
    }
};
