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
        Schema::create('Asignacion Equipo', function (Blueprint $table) {
            $table->comment('');
            $table->integer('Equipo Campo_Id')->index('fk_Asignacion Equipo_Equipo Campo1_idx');
            $table->integer('Miembros Equipo_Id')->index('fk_Asignacion Equipo_Miembros Equipo1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Asignacion Equipo');
    }
};
