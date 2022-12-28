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
        Schema::table('Proyecto', function (Blueprint $table) {
            $table->foreign(['Encuesta_Id'], 'fk_Proyecto_Encuesta1')->references(['Id'])->on('Encuesta')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Proyecto', function (Blueprint $table) {
            $table->dropForeign('fk_Proyecto_Encuesta1');
        });
    }
};
