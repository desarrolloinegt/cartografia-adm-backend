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
        Schema::create('equipo_campo', function (Blueprint $table) {
            $table->comment('');
            $table->integer('proyecto_id')->index('fk_Equipo Campo_Proyecto1_idx');
            $table->integer('supervisor')->index('fk_Equipo Campo_Usuario1_idx');
            $table->integer('vehiculo_id')->nullable()->index('fk_equipo_campo_vehiculo1_idx');

            $table->primary(['supervisor']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipo_campo');
    }
};
