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
        Schema::table('Asignacion Equipo', function (Blueprint $table) {
            $table->foreign(['Equipo Campo_Id'], 'fk_Asignacion Equipo_Equipo Campo1')->references(['Id'])->on('Equipo Campo')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['Miembros Equipo_Id'], 'fk_Asignacion Equipo_Miembros Equipo1')->references(['Id'])->on('Miembros Equipo')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Asignacion Equipo', function (Blueprint $table) {
            $table->dropForeign('fk_Asignacion Equipo_Equipo Campo1');
            $table->dropForeign('fk_Asignacion Equipo_Miembros Equipo1');
        });
    }
};
