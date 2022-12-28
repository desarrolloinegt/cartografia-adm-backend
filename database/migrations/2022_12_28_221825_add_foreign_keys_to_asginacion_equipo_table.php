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
        Schema::table('asginacion_equipo', function (Blueprint $table) {
            $table->foreign(['equipo_campo_id'], 'fk_Asignacion Equipo_Equipo Campo1')->references(['id'])->on('equipo_campo')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['miembros_equipo_id'], 'fk_Asignacion Equipo_Miembros Equipo1')->references(['id'])->on('miembros_equipo')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asginacion_equipo', function (Blueprint $table) {
            $table->dropForeign('fk_Asignacion Equipo_Equipo Campo1');
            $table->dropForeign('fk_Asignacion Equipo_Miembros Equipo1');
        });
    }
};
