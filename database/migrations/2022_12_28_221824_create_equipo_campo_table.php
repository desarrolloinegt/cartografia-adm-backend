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
            $table->integer('id', true);
            $table->integer('proyecto_id')->index('fk_Equipo Campo_Proyecto1_idx');
            $table->integer('usuario_id')->index('fk_Equipo Campo_Usuario1_idx');
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
