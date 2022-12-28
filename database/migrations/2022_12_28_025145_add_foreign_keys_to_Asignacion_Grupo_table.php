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
        Schema::table('Asignacion Grupo', function (Blueprint $table) {
            $table->foreign(['Grupo_Id'], 'fk_Asignacion Grupo_Grupo1')->references(['Id'])->on('Grupo')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['Usuario_Id'], 'fk_Asignacion Grupo_Usuario1')->references(['Id'])->on('Usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Asignacion Grupo', function (Blueprint $table) {
            $table->dropForeign('fk_Asignacion Grupo_Grupo1');
            $table->dropForeign('fk_Asignacion Grupo_Usuario1');
        });
    }
};
