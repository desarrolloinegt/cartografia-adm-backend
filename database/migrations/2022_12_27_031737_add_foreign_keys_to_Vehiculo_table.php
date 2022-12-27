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
        Schema::table('Vehiculo', function (Blueprint $table) {
            $table->foreign(['Equipo Campo_Id'], 'fk_Vehiculo_Equipo Campo1')->references(['Id'])->on('Equipo Campo')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Vehiculo', function (Blueprint $table) {
            $table->dropForeign('fk_Vehiculo_Equipo Campo1');
        });
    }
};
