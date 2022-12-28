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
            $table->foreign(['usuario_id'], 'fk_Miembros Equipo_Usuario1')->references(['id'])->on('usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
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
        });
    }
};
