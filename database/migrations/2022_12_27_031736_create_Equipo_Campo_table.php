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
        Schema::create('Equipo Campo', function (Blueprint $table) {
            $table->comment('');
            $table->integer('Id', true);
            $table->integer('Proyecto_Id')->index('fk_Equipo Campo_Proyecto1_idx');
            $table->integer('Usuario_Id')->index('fk_Equipo Campo_Usuario1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Equipo Campo');
    }
};
