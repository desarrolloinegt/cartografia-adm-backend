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
        Schema::table('Miembros Equipo', function (Blueprint $table) {
            $table->foreign(['Usuario_Id'], 'fk_Miembros Equipo_Usuario1')->references(['Id'])->on('Usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Miembros Equipo', function (Blueprint $table) {
            $table->dropForeign('fk_Miembros Equipo_Usuario1');
        });
    }
};
