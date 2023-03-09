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
        Schema::table('equipo_campo', function (Blueprint $table) {
            $table->foreign(['proyecto_id'], 'fk_Equipo Campo_Proyecto1')->references(['id'])->on('proyecto')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['supervisor'], 'fk_Equipo Campo_Usuario1')->references(['id'])->on('usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['usuario_asignador'], 'fk_equipo_campo_usuario_asignador')->references(['id'])->on('usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['vehiculo_id'], 'fk_equipo_campo_vehiculo1')->references(['id'])->on('vehiculo')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipo_campo', function (Blueprint $table) {
            $table->dropForeign('fk_Equipo Campo_Proyecto1');
            $table->dropForeign('fk_Equipo Campo_Usuario1');
            $table->dropForeign('fk_equipo_campo_usuario_asignador');
            $table->dropForeign('fk_equipo_campo_vehiculo1');
        });
    }
};
