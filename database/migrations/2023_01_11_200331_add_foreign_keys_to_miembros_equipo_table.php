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
        Schema::table('miembros_equipo', function (Blueprint $table) {
            $table->foreign(['cartografo'], 'fk_Miembros Equipo_Usuario1')->references(['id'])->on('usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['supervisor'], 'fk_miembros_equipo_equipo_campo1')->references(['supervisor'])->on('equipo_campo')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('miembros_equipo', function (Blueprint $table) {
            $table->dropForeign('fk_Miembros Equipo_Usuario1');
            $table->dropForeign('fk_miembros_equipo_equipo_campo1');
        });
    }
};
