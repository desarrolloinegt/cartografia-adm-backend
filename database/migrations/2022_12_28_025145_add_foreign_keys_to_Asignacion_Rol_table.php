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
        Schema::table('Asignacion Rol', function (Blueprint $table) {
            $table->foreign(['Grupo_Id'], 'fk_Asignacion Rol_Grupo1')->references(['Id'])->on('Grupo')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['Rol_Id'], 'fk_Asignacion Rol_Rol1')->references(['Id'])->on('Rol')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Asignacion Rol', function (Blueprint $table) {
            $table->dropForeign('fk_Asignacion Rol_Grupo1');
            $table->dropForeign('fk_Asignacion Rol_Rol1');
        });
    }
};
