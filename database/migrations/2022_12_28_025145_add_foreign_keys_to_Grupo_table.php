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
        Schema::table('Grupo', function (Blueprint $table) {
            $table->foreign(['Proyecto_Id'], 'fk_Grupo_Proyecto1')->references(['Id'])->on('Proyecto')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Grupo', function (Blueprint $table) {
            $table->dropForeign('fk_Grupo_Proyecto1');
        });
    }
};
