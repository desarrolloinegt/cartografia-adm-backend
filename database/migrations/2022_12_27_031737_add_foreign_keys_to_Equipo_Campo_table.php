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
        Schema::table('Equipo Campo', function (Blueprint $table) {
            $table->foreign(['Proyecto_Id'], 'fk_Equipo Campo_Proyecto1')->references(['Id'])->on('Proyecto')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['Usuario_Id'], 'fk_Equipo Campo_Usuario1')->references(['Id'])->on('Usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Equipo Campo', function (Blueprint $table) {
            $table->dropForeign('fk_Equipo Campo_Proyecto1');
            $table->dropForeign('fk_Equipo Campo_Usuario1');
        });
    }
};
