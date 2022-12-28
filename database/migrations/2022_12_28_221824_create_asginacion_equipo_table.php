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
        Schema::create('asginacion_equipo', function (Blueprint $table) {
            $table->comment('');
            $table->integer('equipo_campo_id')->index('fk_Asignacion Equipo_Equipo Campo1_idx');
            $table->integer('miembros_equipo_id')->index('fk_Asignacion Equipo_Miembros Equipo1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asginacion_equipo');
    }
};
